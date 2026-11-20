<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * List all candidates with optional status filter and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Candidate::with(['latestInterview.token', 'latestInterview.latestRescheduleRequest']);

        // Filter by Status Tab
        $status = $request->query('status');
        if ($status && $status !== 'all') {
            if ($status === 'new') {
                $query->where('status', 'New Application');
            } elseif ($status === 'reschedule') {
                $query->where('status', 'Reschedule Requested');
            } elseif ($status === 'invited') {
                $query->where('status', 'Invited');
            } elseif ($status === 'confirmed') {
                $query->where('status', 'Confirmed');
            } else {
                $query->where('status', $status);
            }
        }

        // Search Keyword
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('posisi', 'like', "%{$search}%");
            });
        }

        $candidates = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $candidates,
        ]);
    }

    /**
     * Aggregate metric counters for HR Dashboard cards.
     */
    public function metrics(): JsonResponse
    {
        $total = Candidate::count();
        $perluJadwal = Candidate::where('status', 'New Application')->count();
        $menungguRespon = Candidate::where('status', 'Invited')->count();
        $reschedule = Candidate::where('status', 'Reschedule Requested')->count();
        $terkonfirmasi = Candidate::where('status', 'Confirmed')->count();

        return response()->json([
            'success' => true,
            'metrics' => [
                'total' => $total,
                'perlu_jadwal' => $perluJadwal,
                'menunggu_respon' => $menungguRespon,
                'reschedule' => $reschedule,
                'terkonfirmasi' => $terkonfirmasi,
            ],
        ]);
    }
}
