<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Models\MessageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * List all message templates.
     */
    public function index(): JsonResponse
    {
        $templates = MessageTemplate::orderBy('id', 'asc')->get();

        // If empty, seed defaults on the fly
        if ($templates->isEmpty()) {
            foreach (MessageTemplate::defaultTemplates() as $k => $data) {
                MessageTemplate::create(array_merge(['key' => $k], $data));
            }
            $templates = MessageTemplate::orderBy('id', 'asc')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get a specific template by key.
     */
    public function show(string $key): JsonResponse
    {
        $template = MessageTemplate::getByKey($key);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    /**
     * Update a message template.
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $template = MessageTemplate::getByKey($key);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'subjek' => 'required|string|max:255',
            'isi_pesan' => 'required|string',
            'petunjuk_persiapan' => 'nullable|string',
            'ketentuan_pakaian' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $template->update([
            'subjek' => $request->input('subjek'),
            'isi_pesan' => $request->input('isi_pesan'),
            'petunjuk_persiapan' => $request->input('petunjuk_persiapan'),
            'ketentuan_pakaian' => $request->input('ketentuan_pakaian'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Template '{$template->nama}' berhasil diperbarui",
            'data' => $template,
        ]);
    }

    /**
     * Reset a template to official default.
     */
    public function reset(string $key): JsonResponse
    {
        $defaults = MessageTemplate::defaultTemplates();

        if (!isset($defaults[$key])) {
            return response()->json([
                'success' => false,
                'message' => 'Template default tidak ditemukan',
            ], 404);
        }

        $template = MessageTemplate::updateOrCreate(
            ['key' => $key],
            $defaults[$key]
        );

        return response()->json([
            'success' => true,
            'message' => "Template '{$template->nama}' berhasil dikembalikan ke default",
            'data' => $template,
        ]);
    }

    /**
     * Generate preview of template with given candidate/interview or dummy data.
     */
    public function preview(Request $request, string $key): JsonResponse
    {
        $template = MessageTemplate::getByKey($key);
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template tidak ditemukan'], 404);
        }

        $interviewId = $request->input('interview_id');
        $vars = [
            'nama' => 'Muhamad Ibnhu',
            'posisi' => 'Frontend Engineer',
            'jadwal' => now()->addDays(2)->translatedFormat('l, d F Y, H:i') . ' WIB',
            'pewawancara' => 'Rian Pratama (Tech Lead)',
            'kategori' => $key === 'reminder_offline' ? 'Offline (Tatap Muka)' : 'Online (Google Meet)',
            'link_meeting' => 'https://meet.google.com/xyz-interview-ibnhu',
            'lokasi' => 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Ruang Meeting Alpha Lt. 3, Jakarta',
            'persiapan' => $request->input('petunjuk_persiapan', $template->petunjuk_persiapan),
            'pakaian' => $request->input('ketentuan_pakaian', $template->ketentuan_pakaian),
            'catatan' => $request->input('catatan', ''),
            'batas_konfirmasi' => now()->addDay()->translatedFormat('l, d M Y, H:i') . ' WIB',
            'portal_url' => url('/portal/tok_demo_preview'),
        ];

        if ($interviewId) {
            $interview = Interview::with('candidate')->find($interviewId);
            if ($interview && $interview->candidate) {
                $vars['nama'] = $interview->candidate->nama;
                $vars['posisi'] = $interview->candidate->posisi;
                $vars['jadwal'] = $interview->tanggal_waktu ? $interview->tanggal_waktu->translatedFormat('l, d F Y, H:i') . ' WIB' : $vars['jadwal'];
                $vars['pewawancara'] = $interview->interviewer ?? $vars['pewawancara'];
                $vars['kategori'] = $interview->kategori === 'offline' ? 'Offline (Tatap Muka)' : 'Online (Google Meet)';
                $vars['link_meeting'] = $interview->link_meeting ?? $vars['link_meeting'];
                $vars['lokasi'] = $interview->lokasi ?? $vars['lokasi'];
                if ($interview->batas_respon) {
                    $vars['batas_konfirmasi'] = $interview->batas_respon->translatedFormat('l, d M Y, H:i') . ' WIB';
                }
            }
        }

        // Custom template body if passed
        $customSubject = $request->input('subjek', $template->subjek);
        $customBody = $request->input('isi_pesan', $template->isi_pesan);

        $tempTemplate = new MessageTemplate([
            'subjek' => $customSubject,
            'isi_pesan' => $customBody,
        ]);

        $rendered = $tempTemplate->render($vars);

        return response()->json([
            'success' => true,
            'preview' => $rendered,
            'vars_used' => $vars,
        ]);
    }
}
