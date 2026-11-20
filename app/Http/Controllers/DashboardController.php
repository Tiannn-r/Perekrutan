<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ResponseToken;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Render the unified HR Dashboard & Prototype Page.
     */
    public function index(): View
    {
        return view('dashboard');
    }

    /**
     * Render the Candidate Response Landing Page directly via token URL.
     */
    public function portal(string $token): View
    {
        $responseToken = ResponseToken::where('token', $token)
            ->with(['interview.candidate'])
            ->first();

        return view('portal', [
            'token' => $token,
            'responseToken' => $responseToken,
            'interview' => $responseToken?->interview,
            'candidate' => $responseToken?->interview?->candidate,
        ]);
    }

    /**
     * Render the Official Email Invitation Preview.
     */
    public function emailPreview(?string $token = null): View
    {
        $responseToken = null;
        if ($token) {
            $responseToken = ResponseToken::where('token', $token)->with(['interview.candidate'])->first();
        }
        if (!$responseToken) {
            $responseToken = ResponseToken::where('sudah_dipakai', false)
                ->with(['interview.candidate'])
                ->latest()
                ->first() 
                ?? ResponseToken::with(['interview.candidate'])->latest()->first();
        }

        return view('email-preview', [
            'token' => $responseToken?->token,
            'responseToken' => $responseToken,
            'interview' => $responseToken?->interview,
            'candidate' => $responseToken?->interview?->candidate,
        ]);
    }
}
