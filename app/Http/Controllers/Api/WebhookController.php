<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\StatusLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    /**
     * Ingestion webhook endpoint for Google Form / Apps Script.
     */
    public function handleGoogleForm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telepon' => 'required|string|max:50',
            'posisi' => 'required|string|max:255',
            'berkas' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi data gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $candidate = Candidate::create([
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
            'telepon' => $request->input('telepon'),
            'posisi' => $request->input('posisi'),
            'berkas' => $request->input('berkas'),
            'status' => 'New Application',
        ]);

        StatusLog::create([
            'candidate_id' => $candidate->id,
            'interview_id' => null,
            'dari_status' => '-',
            'ke_status' => 'New Application',
            'aktor' => 'Sistem',
            'keterangan' => 'Lamaran masuk via Google Form',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data lamaran berhasil diterima',
            'candidate_id' => $candidate->id,
        ], 201);
    }
}
