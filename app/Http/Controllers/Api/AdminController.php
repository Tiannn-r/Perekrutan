<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Setting;
use App\Models\StatusLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get all default settings configured by Super Admin.
     */
    public function getSettings(): JsonResponse
    {
        $settings = Setting::all()->keyBy('key');

        // Ensure defaults if not present
        if ($settings->isEmpty()) {
            foreach (Setting::defaultSettings() as $k => $item) {
                Setting::create(array_merge(['key' => $k], $item));
            }
            $settings = Setting::all()->keyBy('key');
        }

        $formatted = [];
        foreach ($settings as $k => $s) {
            $formatted[$k] = $s->value;
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'details' => $settings,
        ]);
    }

    /**
     * Update Super Admin default settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $inputs = $request->except(['_token']);

        foreach ($inputs as $key => $val) {
            if ($val !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string)$val]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan default sistem berhasil disimpan oleh Super Admin',
            'data' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    /**
     * Reset default settings to official defaults.
     */
    public function resetSettings(): JsonResponse
    {
        foreach (Setting::defaultSettings() as $k => $item) {
            Setting::updateOrCreate(
                ['key' => $k],
                $item
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan default sistem berhasil dikembalikan ke standar awal',
            'data' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    /**
     * Get system activity & notification logs for Super Admin inspection.
     */
    public function getLogs(): JsonResponse
    {
        $statusLogs = StatusLog::with(['candidate', 'interview'])
            ->latest('id')
            ->limit(30)
            ->get();

        $notificationLogs = NotificationLog::with('interview.candidate')
            ->latest('id')
            ->limit(30)
            ->get();

        return response()->json([
            'success' => true,
            'status_logs' => $statusLogs,
            'notification_logs' => $notificationLogs,
        ]);
    }
}
