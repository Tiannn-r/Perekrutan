<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\ResponseToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Wawancara');
        $response->assertSee('RecruitFlow');
    }

    public function test_candidate_portal_page_loads_with_valid_token(): void
    {
        $candidate = Candidate::create([
            'nama' => 'Test Kandidat',
            'email' => 'kandidat@test.com',
            'telepon' => '081234567890',
            'posisi' => 'Software Engineer',
            'status' => 'Invited',
        ]);

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(2),
            'durasi_menit' => 45,
            'interviewer' => 'Tech Lead',
            'link_meeting' => 'https://meet.google.com/test',
            'status' => 'Invited',
            'jumlah_reschedule' => 0,
            'batas_respon' => now()->addDay(),
        ]);

        $token = ResponseToken::create([
            'interview_id' => $interview->id,
            'token' => 'tok_test_frontend_123',
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => now()->addDay(),
        ]);

        $response = $this->get('/portal/' . $token->token);
        $response->assertStatus(200);
        $response->assertSee('Test Kandidat');
        $response->assertSee('Software Engineer');
        $response->assertSee('Konfirmasi Hadir');
        $response->assertSee('Ajukan Reschedule');
        $response->assertSee('Tolak Undangan');
        $response->assertSee('Apakah Anda yakin menolak undangan ini?');
    }

    public function test_email_preview_page_loads_successfully(): void
    {
        $response = $this->get('/email-preview');
        $response->assertStatus(200);
        $response->assertSee('Preview Email Undangan');
        $response->assertSee('TechNusantara Inc.');
    }
}
