<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\MessageTemplate;
use App\Models\NotificationLog;
use App\Models\Reminder;
use App\Models\RescheduleRequest;
use App\Models\ResponseToken;
use App\Models\Setting;
use App\Models\StatusLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InterviewController extends Controller
{
    /**
     * Create an interview schedule and dispatch invitation token.
     */
    public function schedule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:candidates,id',
            'tanggal_waktu' => 'required|date',
            'kategori' => 'required|in:online,offline',
            'interviewer' => 'required|string|max:255',
            'durasi_menit' => 'nullable|integer|min:15|max:180',
            'link_meeting' => 'nullable|string|max:500',
            'lokasi' => 'nullable|string|max:500',
            'batas_respon' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi jadwal gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $candidate = Candidate::findOrFail($request->input('candidate_id'));
        $scheduleDate = Carbon::parse($request->input('tanggal_waktu'));
        
        // Default deadline: configured hours before interview (default 24h)
        $deadlineHours = (int)Setting::getVal('default_deadline_hours', 24);
        $deadline = $request->filled('batas_respon') 
            ? Carbon::parse($request->input('batas_respon')) 
            : $scheduleDate->copy()->subHours($deadlineHours);

        $defaultDuration = (int)Setting::getVal('default_duration', 45);
        $defaultInterviewer = Setting::getVal('default_interviewer', 'Tim HR Nanggala Kencana Rekatama Indonesia');
        $defaultMeeting = Setting::getVal('default_meet_prefix', 'https://meet.google.com/nkri-interview');
        $defaultLocation = Setting::getVal('default_location', 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta');

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'tahap' => $request->input('tahap', 'Tahap 1'),
            'kategori' => $request->input('kategori'),
            'tanggal_waktu' => $scheduleDate,
            'durasi_menit' => $request->input('durasi_menit') ?: $defaultDuration,
            'interviewer' => $request->input('interviewer') ?: $defaultInterviewer,
            'link_meeting' => null, // Link meeting tidak dimasukkan saat penjadwalan awal, dikirim pas Hari H
            'lokasi' => $request->input('kategori') === 'offline' ? ($request->input('lokasi') ?: $defaultLocation) : null,
            'status' => 'Invited',
            'jumlah_reschedule' => 0,
            'batas_respon' => $deadline,
            'catatan' => $request->input('catatan'),
        ]);

        // Generate unique secure token
        $tokenString = 'tok_' . Str::random(36);
        $token = ResponseToken::create([
            'interview_id' => $interview->id,
            'token' => $tokenString,
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => $deadline,
        ]);

        // Update candidate status
        $prevStatus = $candidate->status;
        $candidate->update(['status' => 'Invited']);

        // Status log
        StatusLog::create([
            'candidate_id' => $candidate->id,
            'interview_id' => $interview->id,
            'dari_status' => $prevStatus,
            'ke_status' => 'Invited',
            'aktor' => 'HR',
            'keterangan' => "Wawancara {$interview->kategori} dijadwalkan pada {$scheduleDate->format('d/m/Y H:i')}",
        ]);

        // Notification log
        NotificationLog::create([
            'interview_id' => $interview->id,
            'penerima' => $candidate->email,
            'tipe' => 'Undangan',
            'status_kirim' => 'Sent',
        ]);

        $portalUrl = url("/portal/{$tokenString}");

        // Auto-kirim email undangan secara otomatis menggunakan master template dari Admin
        try {
            $template = MessageTemplate::getByKey('invitation');
            $rendered = $template->render([
                'nama' => $candidate->nama,
                'posisi' => $candidate->posisi,
                'jadwal' => $scheduleDate->translatedFormat('l, d F Y, H:i') . ' WIB',
                'pewawancara' => $interview->interviewer ?? 'Tim HR Nanggala Kencana Rekatama Indonesia',
                'kategori' => $interview->kategori === 'offline' ? 'Offline (Tatap Muka di Kantor)' : 'Online (Google Meet)',
                'lokasi' => $interview->lokasi ?? 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta',
                'link_meeting' => 'Tautan Google Meet akan dikirimkan pada email Hari H',
                'batas_konfirmasi' => $deadline->translatedFormat('l, d F Y, H:i') . ' WIB',
                'portal_url' => $portalUrl,
                'link_portal' => $portalUrl,
                'deadline_konfirmasi' => $deadline->translatedFormat('l, d F Y, H:i') . ' WIB',
            ]);

            Mail::raw($rendered['isi_pesan'], function ($m) use ($candidate, $rendered) {
                $m->to($candidate->email, $candidate->nama)
                  ->subject($rendered['subjek']);
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Undangan wawancara berhasil dibuat dan dikirim',
            'interview_id' => $interview->id,
            'token' => $tokenString,
            'portal_url' => $portalUrl,
        ], 201);
    }

    /**
     * Resolve a candidate's reschedule request by assigning a new slot.
     */
    public function resolveReschedule(Request $request, $id): JsonResponse
    {
        $interview = Interview::with('candidate')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tanggal_waktu' => 'required|date',
            'kategori' => 'required|in:online,offline',
            'link_meeting' => 'nullable|string|max:500',
            'lokasi' => 'nullable|string|max:500',
            'ditangani_oleh' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal baru tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $newDate = Carbon::parse($request->input('tanggal_waktu'));
        $newDeadline = $newDate->copy()->subHours(24);

        // Update latest reschedule request as approved
        RescheduleRequest::where('interview_id', $interview->id)
            ->where('status_persetujuan', 'Pending')
            ->update([
                'status_persetujuan' => 'Approved',
                'ditangani_oleh' => $request->input('ditangani_oleh', 'HR Recruitment'),
                'ditangani_at' => now(),
            ]);

        // Update interview details
        $interview->update([
            'tanggal_waktu' => $newDate,
            'kategori' => $request->input('kategori'),
            'link_meeting' => null, // Link meeting baru tidak dimasukkan sekarang, dikirim pas Hari H
            'lokasi' => $request->input('kategori') === 'offline' ? $request->input('lokasi') : null,
            'status' => 'Invited',
            'batas_respon' => $newDeadline,
        ]);

        // Refresh/reopen token for new schedule
        $newTokenString = 'tok_' . Str::random(36);
        ResponseToken::create([
            'interview_id' => $interview->id,
            'token' => $newTokenString,
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => $newDeadline,
        ]);

        $interview->candidate->update(['status' => 'Invited']);

        StatusLog::create([
            'candidate_id' => $interview->candidate_id,
            'interview_id' => $interview->id,
            'dari_status' => 'Reschedule Requested',
            'ke_status' => 'Invited',
            'aktor' => 'HR',
            'keterangan' => "Jadwal baru ditetapkan pada {$newDate->format('d/m/Y H:i')}",
        ]);

        NotificationLog::create([
            'interview_id' => $interview->id,
            'penerima' => $interview->candidate->email,
            'tipe' => 'Jadwal Baru',
            'status_kirim' => 'Sent',
        ]);

        $newPortalUrl = url("/portal/{$newTokenString}");

        // Auto-kirim email jadwal baru pengganti menggunakan master template dari Admin
        try {
            $template = MessageTemplate::getByKey('invitation');
            $rendered = $template->render([
                'nama' => $interview->candidate->nama,
                'posisi' => $interview->candidate->posisi,
                'jadwal' => $newDate->translatedFormat('l, d F Y, H:i') . ' WIB',
                'pewawancara' => $interview->interviewer ?? 'Tim HR Nanggala Kencana Rekatama Indonesia',
                'kategori' => $interview->kategori === 'offline' ? 'Offline (Tatap Muka di Kantor)' : 'Online (Google Meet)',
                'lokasi' => $interview->lokasi ?? 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta',
                'link_meeting' => 'Tautan Google Meet akan dikirimkan pada email Hari H',
                'batas_konfirmasi' => $newDeadline->translatedFormat('l, d F Y, H:i') . ' WIB',
                'portal_url' => $newPortalUrl,
                'link_portal' => $newPortalUrl,
                'deadline_konfirmasi' => $newDeadline->translatedFormat('l, d F Y, H:i') . ' WIB',
            ]);

            Mail::raw($rendered['isi_pesan'], function ($m) use ($interview, $rendered) {
                $m->to($interview->candidate->email, $interview->candidate->nama)
                  ->subject('[Jadwal Baru] ' . $rendered['subjek']);
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jadwal pengganti berhasil ditetapkan dan undangan dikirim ulang',
            'new_portal_url' => $newPortalUrl,
        ]);
    }

    /**
     * Get reminders status for an interview.
     */
    public function getReminders($id): JsonResponse
    {
        $interview = Interview::with(['candidate', 'reminders'])->findOrFail($id);

        $prepReminder = $interview->reminders->firstWhere('jenis', 'persiapan');
        $dayReminder = $interview->reminders->firstWhere('jenis', 'hari_h') ?? $interview->reminders->first();

        $activeReminder = $dayReminder ?? $prepReminder;
        $isSent = $activeReminder && $activeReminder->status_kirim === 'terkirim';

        return response()->json([
            'success' => true,
            'interview_id' => $interview->id,
            'status' => $interview->status,
            'kategori' => $interview->kategori,
            'reminder' => [
                'status_kirim' => $isSent ? 'terkirim' : 'belum dikirim',
                'dikirim_at' => $activeReminder?->dikirim_at ? $activeReminder->dikirim_at->translatedFormat('d M Y, H:i') . ' WIB' : null,
                'subjek' => $activeReminder?->subjek,
                'mode' => $activeReminder?->mode ?? $interview->kategori,
            ],
            'reminders' => [
                'persiapan' => $prepReminder ? [
                    'status_kirim' => $prepReminder->status_kirim,
                    'dikirim_at' => $prepReminder->dikirim_at ? $prepReminder->dikirim_at->translatedFormat('d M Y, H:i') . ' WIB' : null,
                    'subjek' => $prepReminder->subjek,
                ] : [
                    'status_kirim' => $isSent ? 'terkirim' : 'belum dikirim',
                    'dikirim_at' => null,
                    'subjek' => null,
                ],
                'hari_h' => $dayReminder ? [
                    'status_kirim' => $dayReminder->status_kirim,
                    'dikirim_at' => $dayReminder->dikirim_at ? $dayReminder->dikirim_at->translatedFormat('d M Y, H:i') . ' WIB' : null,
                    'subjek' => $dayReminder->subjek,
                ] : [
                    'status_kirim' => 'belum dikirim',
                    'dikirim_at' => null,
                    'subjek' => null,
                ],
            ],
        ]);
    }

    /**
     * Send interview reminder (persiapan / hari_h) to candidate who has confirmed attendance.
     * Complies with PRD Rules BR-07, BR-08, BR-09, BR-10.
     */
    public function sendReminder(Request $request, $id): JsonResponse
    {
        $interview = Interview::with('candidate')->findOrFail($id);

        // Business Rule BR-07: Pengingat hanya dikirim untuk jadwal berstatus Confirmed
        if ($interview->status !== 'Confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Pengingat hanya dapat dikirim untuk jadwal berstatus Confirmed (Aturan BR-07)',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'jenis' => 'nullable|in:persiapan,hari_h',
            'channel' => 'nullable|in:email,whatsapp,both',
            'subjek' => 'nullable|string|max:255',
            'isi_pesan' => 'nullable|string',
            'link_meeting' => 'nullable|string|max:500',
            'lokasi' => 'nullable|string|max:500',
            'catatan_persiapan' => 'nullable|string|max:2000',
            'ketentuan_pakaian' => 'nullable|string|max:500',
            'pesan_tambahan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi pesan pengingat gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $jenis = $request->input('jenis', 'persiapan');
        $mode = $interview->kategori ?: 'online';
        $candidate = $interview->candidate;

        // Sesuai aturan BR-08: Link Google Meet dikirim pas Hari H untuk wawancara online
        if ($mode === 'online') {
            if ($jenis === 'hari_h') {
                $meetingLink = $request->input('link_meeting') ?: $interview->link_meeting;
                if (empty($meetingLink)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tautan Google Meet wajib terisi sebelum pengingat Hari H dikirim (Aturan BR-08)',
                    ], 422);
                }
                $interview->update(['link_meeting' => $meetingLink]);
            } elseif ($request->filled('link_meeting')) {
                $interview->update(['link_meeting' => $request->input('link_meeting')]);
            }
        }

        if ($request->filled('lokasi')) {
            $interview->update(['lokasi' => $request->input('lokasi')]);
        }

        // Business Rule BR-09: Wawancara Offline: lokasi dan jam wajib terisi sebelum pengingat dikirim
        if ($mode === 'offline' && (empty($interview->lokasi) || empty($interview->tanggal_waktu))) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi kantor dan jam wajib terisi sebelum pengingat dikirim (Aturan BR-09)',
            ], 422);
        }

        // Resolve template: specific (e.g. reminder_online_prep, reminder_online_day) with fallback
        $specificTemplateKey = "reminder_{$mode}_{$jenis}";
        $fallbackTemplateKey = "reminder_{$mode}";

        $template = MessageTemplate::where('key', $specificTemplateKey)->first()
            ?? MessageTemplate::getByKey($fallbackTemplateKey);

        $prepNotes = $request->input('catatan_persiapan', $template?->petunjuk_persiapan);
        $dressNotes = $request->input('ketentuan_pakaian', $template?->ketentuan_pakaian);
        $extraNotes = $request->input('pesan_tambahan', '');

        $rendered = $template ? $template->render([
            'nama' => $candidate->nama,
            'posisi' => $candidate->posisi,
            'jadwal' => $interview->tanggal_waktu ? $interview->tanggal_waktu->translatedFormat('l, d F Y, H:i') . ' WIB' : '-',
            'pewawancara' => $interview->interviewer ?? 'Tim Rekrutmen',
            'kategori' => $mode === 'offline' ? 'Offline (Tatap Muka)' : 'Online (Google Meet)',
            'link_meeting' => $interview->link_meeting ?? 'https://meet.google.com/...',
            'lokasi' => $interview->lokasi ?? 'Gedung Nanggala Kencana Rekatama Indonesia',
            'persiapan' => $prepNotes,
            'pakaian' => $dressNotes,
            'catatan' => $extraNotes ? "Catatan Tambahan:\n" . $extraNotes : '',
        ]) : [
            'subjek' => "Pengingat Wawancara: {$candidate->posisi} - {$candidate->nama}",
            'isi_pesan' => "Halo {$candidate->nama}, mengingatkan jadwal wawancara Anda.",
        ];

        $finalSubject = $request->input('subjek') ?: $rendered['subjek'];
        $finalBody = $request->input('isi_pesan') ?: $rendered['isi_pesan'];

        // Dispatch Email using Laravel Mail
        try {
            Mail::raw($finalBody, function ($m) use ($candidate, $finalSubject) {
                $m->to($candidate->email, $candidate->nama)
                  ->subject($finalSubject);
            });
        } catch (\Throwable $e) {
            report($e);
        }

        // Record in reminders table (FR-24 & Section 13)
        $reminderRecord = Reminder::updateOrCreate(
            [
                'interview_id' => $interview->id,
                'jenis' => $jenis,
            ],
            [
                'mode' => $mode,
                'subjek' => $finalSubject,
                'isi' => $finalBody,
                'dikirim_oleh' => 'HR',
                'dikirim_at' => now(),
                'status_kirim' => 'terkirim',
            ]
        );

        // Record notification log
        $labelJenis = $jenis === 'hari_h' ? 'Hari H' : 'Persiapan';
        NotificationLog::create([
            'interview_id' => $interview->id,
            'penerima' => $candidate->email,
            'tipe' => "Pengingat {$labelJenis} (Email)",
            'status_kirim' => 'Sent',
        ]);

        // Record status log
        StatusLog::create([
            'candidate_id' => $interview->candidate_id,
            'interview_id' => $interview->id,
            'dari_status' => $interview->status,
            'ke_status' => $interview->status,
            'aktor' => 'HR',
            'keterangan' => "Email pengingat {$labelJenis} ({$mode}) dikirim ke {$candidate->email}",
        ]);

        return response()->json([
            'success' => true,
            'message' => "Email pengingat {$labelJenis} berhasil dikirim ke {$candidate->nama} ({$candidate->email})",
            'data' => [
                'reminder_id' => $reminderRecord->id,
                'jenis' => $jenis,
                'mode' => $mode,
                'kategori' => $mode,
                'candidate' => $candidate->nama,
                'email' => $candidate->email,
                'subjek' => $finalSubject,
                'sent_at' => now()->translatedFormat('d F Y, H:i') . ' WIB',
            ],
        ]);
    }
}
