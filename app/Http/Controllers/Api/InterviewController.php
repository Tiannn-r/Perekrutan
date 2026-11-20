<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\NotificationLog;
use App\Models\RescheduleRequest;
use App\Models\ResponseToken;
use App\Models\StatusLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        
        // Default deadline: 24 hours before interview
        $deadline = $request->filled('batas_respon') 
            ? Carbon::parse($request->input('batas_respon')) 
            : $scheduleDate->copy()->subHours(24);

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'tahap' => $request->input('tahap', 'Tahap 1'),
            'kategori' => $request->input('kategori'),
            'tanggal_waktu' => $scheduleDate,
            'durasi_menit' => $request->input('durasi_menit', 45),
            'interviewer' => $request->input('interviewer'),
            'link_meeting' => $request->input('kategori') === 'online' ? $request->input('link_meeting') : null,
            'lokasi' => $request->input('kategori') === 'offline' ? $request->input('lokasi') : null,
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
            'link_meeting' => $request->input('kategori') === 'online' ? $request->input('link_meeting') : null,
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

        return response()->json([
            'success' => true,
            'message' => 'Jadwal pengganti berhasil ditetapkan dan undangan dikirim ulang',
            'new_portal_url' => url("/portal/{$newTokenString}"),
        ]);
    }

    /**
     * Send interview reminder to candidate who has confirmed attendance.
     */
    public function sendReminder(Request $request, $id): JsonResponse
    {
        $interview = Interview::with('candidate')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'channel' => 'nullable|in:email,whatsapp,both',
            'catatan_persiapan' => 'nullable|string|max:1000',
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

        $channel = $request->input('channel', 'email');

        // Record notification log
        NotificationLog::create([
            'interview_id' => $interview->id,
            'penerima' => $interview->candidate->email,
            'tipe' => 'Pengingat Wawancara (' . ucfirst($channel) . ')',
            'status_kirim' => 'Sent',
        ]);

        // Record status log
        StatusLog::create([
            'candidate_id' => $interview->candidate_id,
            'interview_id' => $interview->id,
            'dari_status' => $interview->status,
            'ke_status' => $interview->status,
            'aktor' => 'HR',
            'keterangan' => "Pengingat wawancara {$interview->kategori} dikirim via " . ucfirst($channel),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengingat wawancara berhasil dikirim ke ' . $interview->candidate->nama,
            'data' => [
                'candidate' => $interview->candidate->nama,
                'email' => $interview->candidate->email,
                'telepon' => $interview->candidate->telepon,
                'kategori' => $interview->kategori,
                'sent_at' => now()->translatedFormat('d F Y, H:i') . ' WIB',
            ],
        ]);
    }
}
