<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Models\NotificationLog;
use App\Models\RescheduleRequest;
use App\Models\ResponseToken;
use App\Models\StatusLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PortalController extends Controller
{
    /**
     * Fetch interview details for the candidate response page via token.
     */
    public function show($token): JsonResponse
    {
        $responseToken = ResponseToken::where('token', $token)
            ->with(['interview.candidate'])
            ->first();

        if (!$responseToken) {
            return response()->json([
                'success' => false,
                'status' => 'NOT_FOUND',
                'message' => 'Tautan undangan tidak valid atau tidak ditemukan.',
            ], 404);
        }

        $interview = $responseToken->interview;
        $candidate = $interview->candidate;

        $isConsumed = $responseToken->sudah_dipakai;
        $isExpired = $responseToken->kedaluwarsa_at && now()->isAfter($responseToken->kedaluwarsa_at);

        $tokenStatus = 'VALID';
        if ($isConsumed) {
            $tokenStatus = 'CONSUMED';
        } elseif ($isExpired) {
            $tokenStatus = 'EXPIRED';
        }

        return response()->json([
            'success' => true,
            'token_status' => $tokenStatus,
            'candidate' => [
                'nama' => $candidate->nama,
                'email' => $candidate->email,
                'posisi' => $candidate->posisi,
            ],
            'interview' => [
                'id' => $interview->id,
                'tahap' => $interview->tahap,
                'kategori' => $interview->kategori,
                'tanggal_waktu' => $interview->tanggal_waktu->format('Y-m-d H:i:s'),
                'tanggal_waktu_formatted' => $interview->tanggal_waktu->translatedFormat('l, d F Y, H:i') . ' WIB',
                'durasi_menit' => $interview->durasi_menit,
                'interviewer' => $interview->interviewer,
                'link_meeting' => $interview->link_meeting,
                'lokasi' => $interview->lokasi,
                'status' => $interview->status,
                'jumlah_reschedule' => $interview->jumlah_reschedule,
                'can_reschedule' => $interview->jumlah_reschedule < 1,
                'batas_respon_formatted' => $interview->batas_respon 
                    ? $interview->batas_respon->translatedFormat('d M Y, H:i') . ' WIB' 
                    : null,
            ],
        ]);
    }

    /**
     * Submit candidate response (Confirm, Reschedule, or Decline).
     */
    public function respond(Request $request, $token): JsonResponse
    {
        $responseToken = ResponseToken::where('token', $token)
            ->with('interview.candidate')
            ->first();

        if (!$responseToken) {
            return response()->json([
                'success' => false,
                'message' => 'Tautan undangan tidak valid.',
            ], 404);
        }

        if (!$responseToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Tautan undangan ini sudah digunakan atau telah kedaluwarsa.',
            ], 410);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:confirm,reschedule,decline',
            'alasan' => 'nullable|string|max:1000',
            'saran_waktu' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format respon tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $action = $request->input('action');
        $interview = $responseToken->interview;
        $candidate = $interview->candidate;

        if ($action === 'confirm') {
            $interview->update(['status' => 'Confirmed']);
            $candidate->update(['status' => 'Confirmed']);
            $responseToken->update(['sudah_dipakai' => true]);

            StatusLog::create([
                'candidate_id' => $candidate->id,
                'interview_id' => $interview->id,
                'dari_status' => 'Invited',
                'ke_status' => 'Confirmed',
                'aktor' => 'Kandidat',
                'keterangan' => 'Kandidat mengonfirmasi kehadiran',
            ]);

            NotificationLog::create([
                'interview_id' => $interview->id,
                'penerima' => $interview->interviewer,
                'tipe' => 'Konfirmasi',
                'status_kirim' => 'Sent',
            ]);

            return response()->json([
                'success' => true,
                'action' => 'confirm',
                'message' => 'Kehadiran Anda berhasil dikonfirmasi. Undangan kalender telah dikirim ke email.',
                'status' => 'Confirmed',
            ]);
        }

        if ($action === 'reschedule') {
            // Business Rule BR-06: Max 1 reschedule per stage
            if ($interview->jumlah_reschedule >= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas maksimal pengajuan reschedule (1 kali) telah tercapai.',
                ], 422);
            }

            $alasan = $request->input('alasan');
            if (empty($alasan) || strlen(trim($alasan)) < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan penundaan wajib diisi minimal 5 karakter.',
                ], 422);
            }

            RescheduleRequest::create([
                'interview_id' => $interview->id,
                'alasan' => trim($alasan),
                'saran_waktu' => $request->input('saran_waktu'),
                'status_persetujuan' => 'Pending',
                'diajukan_at' => now(),
            ]);

            $interview->increment('jumlah_reschedule');
            $interview->update(['status' => 'Reschedule Requested']);
            $candidate->update(['status' => 'Reschedule Requested']);

            StatusLog::create([
                'candidate_id' => $candidate->id,
                'interview_id' => $interview->id,
                'dari_status' => 'Invited',
                'ke_status' => 'Reschedule Requested',
                'aktor' => 'Kandidat',
                'keterangan' => 'Alasan: ' . trim($alasan),
            ]);

            NotificationLog::create([
                'interview_id' => $interview->id,
                'penerima' => 'hr@technusantara.id',
                'tipe' => 'Reschedule',
                'status_kirim' => 'Sent',
            ]);

            return response()->json([
                'success' => true,
                'action' => 'reschedule',
                'message' => 'Permohonan jadwal ulang telah dikirimkan ke tim HR.',
                'status' => 'Reschedule Requested',
            ]);
        }

        if ($action === 'decline') {
            $alasan = $request->input('alasan');
            if (empty($alasan) || strlen(trim($alasan)) < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan penolakan wajib diisi.',
                ], 422);
            }

            $interview->update([
                'status' => 'Declined',
                'catatan' => 'Ditolak pelamar. Alasan: ' . trim($alasan),
            ]);
            $candidate->update(['status' => 'Declined']);
            $responseToken->update(['sudah_dipakai' => true]);

            StatusLog::create([
                'candidate_id' => $candidate->id,
                'interview_id' => $interview->id,
                'dari_status' => 'Invited',
                'ke_status' => 'Declined',
                'aktor' => 'Kandidat',
                'keterangan' => 'Ditolak kandidat. Alasan: ' . trim($alasan),
            ]);

            NotificationLog::create([
                'interview_id' => $interview->id,
                'penerima' => 'hr@technusantara.id',
                'tipe' => 'Penolakan',
                'status_kirim' => 'Sent',
            ]);

            return response()->json([
                'success' => true,
                'action' => 'decline',
                'message' => 'Undangan wawancara telah ditolak. Terima kasih atas konfirmasi Anda.',
                'status' => 'Declined',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
    }
}
