<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\ResponseToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_form_webhook_creates_new_candidate()
    {
        $payload = [
            'nama' => 'Rina Gunawan',
            'email' => 'rina.gunawan@example.com',
            'telepon' => '081299887766',
            'posisi' => 'Data Analyst',
            'berkas' => 'https://drive.google.com/file/d/rina_cv',
        ];

        $response = $this->postJson('/api/webhooks/google-form', $payload);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('candidates', [
            'nama' => 'Rina Gunawan',
            'email' => 'rina.gunawan@example.com',
            'status' => 'New Application',
        ]);
    }

    public function test_hr_can_fetch_metrics_and_candidates()
    {
        Candidate::create([
            'nama' => 'User A',
            'email' => 'a@example.com',
            'telepon' => '0811',
            'posisi' => 'QA',
            'status' => 'New Application',
        ]);

        $metricsResponse = $this->getJson('/api/dashboard/metrics');
        $metricsResponse->assertStatus(200)
                        ->assertJson([
                            'success' => true,
                            'metrics' => [
                                'total' => 1,
                                'perlu_jadwal' => 1,
                            ],
                        ]);

        $listResponse = $this->getJson('/api/candidates');
        $listResponse->assertStatus(200)
                     ->assertJsonStructure(['success', 'data']);
    }

    public function test_hr_can_schedule_online_interview()
    {
        $candidate = Candidate::create([
            'nama' => 'User B',
            'email' => 'b@example.com',
            'telepon' => '0812',
            'posisi' => 'Backend Dev',
            'status' => 'New Application',
        ]);

        $scheduleData = [
            'candidate_id' => $candidate->id,
            'tanggal_waktu' => now()->addDays(3)->format('Y-m-d 10:00:00'),
            'kategori' => 'online',
            'interviewer' => 'Dimas Ardiansyah',
            'durasi_menit' => 45,
            'link_meeting' => 'https://meet.google.com/test-room',
        ];

        $response = $this->postJson('/api/interviews/schedule', $scheduleData);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('interviews', [
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'status' => 'Invited',
        ]);

        $this->assertEquals('Invited', $candidate->fresh()->status);
        $this->assertDatabaseHas('response_tokens', [
            'interview_id' => $response->json('interview_id'),
            'sudah_dipakai' => false,
        ]);
    }

    public function test_candidate_can_confirm_via_token()
    {
        $candidate = Candidate::create([
            'nama' => 'User C',
            'email' => 'c@example.com',
            'telepon' => '0813',
            'posisi' => 'UI Designer',
            'status' => 'Invited',
        ]);

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(3),
            'interviewer' => 'Rian Pratama',
            'status' => 'Invited',
            'batas_respon' => now()->addDays(2),
        ]);

        $token = ResponseToken::create([
            'interview_id' => $interview->id,
            'token' => 'tok_unit_test_confirm',
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => now()->addDays(2),
        ]);

        // 1. Fetch portal info
        $portalResponse = $this->getJson("/api/portal/{$token->token}");
        $portalResponse->assertStatus(200)
                       ->assertJson([
                           'success' => true,
                           'token_status' => 'VALID',
                       ]);

        // 2. Respond confirm
        $respondResponse = $this->postJson("/api/portal/{$token->token}/respond", [
            'action' => 'confirm',
        ]);

        $respondResponse->assertStatus(200)
                        ->assertJson(['success' => true, 'status' => 'Confirmed']);

        $this->assertEquals('Confirmed', $interview->fresh()->status);
        $this->assertEquals('Confirmed', $candidate->fresh()->status);
        $this->assertTrue((bool)$token->fresh()->sudah_dipakai);
    }

    public function test_candidate_can_request_reschedule_within_quota()
    {
        $candidate = Candidate::create([
            'nama' => 'User D',
            'email' => 'd@example.com',
            'telepon' => '0814',
            'posisi' => 'DevOps',
            'status' => 'Invited',
        ]);

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(3),
            'interviewer' => 'Rian Pratama',
            'status' => 'Invited',
            'jumlah_reschedule' => 0,
            'batas_respon' => now()->addDays(2),
        ]);

        $token = ResponseToken::create([
            'interview_id' => $interview->id,
            'token' => 'tok_unit_test_reschedule',
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => now()->addDays(2),
        ]);

        // Submit reschedule 1st time (allowed)
        $rescheduleResponse = $this->postJson("/api/portal/{$token->token}/respond", [
            'action' => 'reschedule',
            'alasan' => 'Ada jadwal ujian kampus di waktu tersebut.',
            'saran_waktu' => 'Kamis jam 14:00',
        ]);

        $rescheduleResponse->assertStatus(200)
                           ->assertJson(['success' => true, 'status' => 'Reschedule Requested']);

        $this->assertEquals(1, $interview->fresh()->jumlah_reschedule);
        $this->assertEquals('Reschedule Requested', $candidate->fresh()->status);

        // Attempt submit reschedule 2nd time (must be rejected - rule BR-06)
        $secondAttempt = $this->postJson("/api/portal/{$token->token}/respond", [
            'action' => 'reschedule',
            'alasan' => 'Minta reschedule lagi',
        ]);

        $secondAttempt->assertStatus(422);
    }
}
