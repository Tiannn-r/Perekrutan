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

    public function test_hr_can_send_interview_reminder()
    {
        $candidate = Candidate::create([
            'nama' => 'User E',
            'email' => 'e@example.com',
            'telepon' => '081512345678',
            'posisi' => 'Frontend Engineer',
            'status' => 'Confirmed',
        ]);

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(2),
            'interviewer' => 'Rian Pratama',
            'link_meeting' => 'https://meet.google.com/test-room',
            'status' => 'Confirmed',
        ]);

        $reminderPayload = [
            'channel' => 'email',
            'catatan_persiapan' => 'Pastikan koneksi internet stabil dan siapkan portofolio digital.',
            'ketentuan_pakaian' => 'Kemeja formal rapi.',
            'pesan_tambahan' => 'Hadir 5-10 menit sebelum sesi dimulai.',
        ];

        $response = $this->postJson("/api/interviews/{$interview->id}/reminder", $reminderPayload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'candidate' => 'User E',
                         'email' => 'e@example.com',
                         'kategori' => 'online',
                     ],
                 ]);

        $this->assertDatabaseHas('notification_logs', [
            'interview_id' => $interview->id,
            'penerima' => 'e@example.com',
        ]);

        $this->assertDatabaseHas('status_logs', [
            'candidate_id' => $candidate->id,
            'aktor' => 'HR',
        ]);

        $this->assertDatabaseHas('reminders', [
            'interview_id' => $interview->id,
            'jenis' => 'persiapan',
            'status_kirim' => 'terkirim',
        ]);
    }

    public function test_two_stage_reminder_and_prd_business_rules()
    {
        $candidate = Candidate::create([
            'nama' => 'User PRD',
            'email' => 'prd@example.com',
            'telepon' => '0815998877',
            'posisi' => 'Backend Developer',
            'status' => 'Confirmed',
        ]);

        $interview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(1),
            'interviewer' => 'Dimas Ardiansyah',
            'link_meeting' => 'https://meet.google.com/prd-room',
            'status' => 'Confirmed',
        ]);

        // 1. Send Persiapan Reminder (FR-20)
        $resPrep = $this->postJson("/api/interviews/{$interview->id}/reminder", [
            'jenis' => 'persiapan',
            'channel' => 'email',
            'catatan_persiapan' => 'Pastikan koneksi internet stabil & ruangan tenang.',
        ]);
        $resPrep->assertStatus(200);

        $this->assertDatabaseHas('reminders', [
            'interview_id' => $interview->id,
            'jenis' => 'persiapan',
            'status_kirim' => 'terkirim',
        ]);

        // 2. Send Hari H Reminder (FR-21)
        $resDay = $this->postJson("/api/interviews/{$interview->id}/reminder", [
            'jenis' => 'hari_h',
            'channel' => 'email',
        ]);
        $resDay->assertStatus(200);

        $this->assertDatabaseHas('reminders', [
            'interview_id' => $interview->id,
            'jenis' => 'hari_h',
            'status_kirim' => 'terkirim',
        ]);

        // 3. Query reminder statuses (FR-24)
        $resList = $this->getJson("/api/interviews/{$interview->id}/reminders");
        $resList->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'reminders' => [
                        'persiapan' => ['status_kirim' => 'terkirim'],
                        'hari_h' => ['status_kirim' => 'terkirim'],
                    ],
                ]);

        // 4. Test BR-07: Cannot send reminder if interview status is not Confirmed
        $unconfirmedInterview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(3),
            'interviewer' => 'Rian Pratama',
            'status' => 'Invited',
        ]);
        $resBr07 = $this->postJson("/api/interviews/{$unconfirmedInterview->id}/reminder", [
            'jenis' => 'persiapan',
        ]);
        $resBr07->assertStatus(422)
                ->assertJson(['success' => false]);

        // 5. Test BR-08: Online Hari H requires meeting link
        $noLinkInterview = Interview::create([
            'candidate_id' => $candidate->id,
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(1),
            'interviewer' => 'Rian Pratama',
            'link_meeting' => null,
            'status' => 'Confirmed',
        ]);
        $resBr08 = $this->postJson("/api/interviews/{$noLinkInterview->id}/reminder", [
            'jenis' => 'hari_h',
        ]);
        $resBr08->assertStatus(422)
                ->assertJson(['success' => false]);
    }

    public function test_hr_can_manage_message_templates_in_admin_panel()
    {
        // 1. List templates
        $listResponse = $this->getJson('/api/templates');
        $listResponse->assertStatus(200)
                     ->assertJson(['success' => true])
                     ->assertJsonStructure(['data']);

        // 2. Update online reminder template
        $updatePayload = [
            'subjek' => 'Pemberitahuan Wawancara Online: {posisi} - {nama}',
            'isi_pesan' => 'Halo {nama}, ini adalah pesan kustom dari HR.',
            'petunjuk_persiapan' => 'Koneksi internet minimal 10 Mbps.',
            'ketentuan_pakaian' => 'Kemeja berkerah rapi.',
        ];

        $updateResponse = $this->putJson('/api/templates/reminder_online', $updatePayload);
        $updateResponse->assertStatus(200)
                       ->assertJson([
                           'success' => true,
                           'data' => [
                               'key' => 'reminder_online',
                               'subjek' => 'Pemberitahuan Wawancara Online: {posisi} - {nama}',
                           ],
                       ]);

        // 3. Preview template
        $previewResponse = $this->postJson('/api/templates/reminder_online/preview', [
            'catatan' => 'Segera hadir tepat waktu',
        ]);
        $previewResponse->assertStatus(200)
                        ->assertJson(['success' => true])
                        ->assertJsonStructure(['preview' => ['subjek', 'isi_pesan']]);

        // 4. Reset template to default
        $resetResponse = $this->postJson('/api/templates/reminder_online/reset');
        $resetResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
    }

    public function test_super_admin_can_manage_default_settings(): void
    {
        // 1. Get settings
        $res = $this->getJson('/api/admin/settings');
        $res->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data', 'details']);

        // 2. Update settings
        $update = $this->putJson('/api/admin/settings', [
            'default_interviewer' => 'Head of HR NKRI',
            'default_duration' => '60',
            'default_max_reschedule' => '1',
        ]);
        $update->assertStatus(200)
               ->assertJson(['success' => true]);

        $this->assertDatabaseHas('settings', [
            'key' => 'default_interviewer',
            'value' => 'Head of HR NKRI',
        ]);

        // 3. Reset settings
        $reset = $this->postJson('/api/admin/settings/reset');
        $reset->assertStatus(200)
              ->assertJson(['success' => true]);

        // 4. Get audit logs
        $logs = $this->getJson('/api/admin/logs');
        $logs->assertStatus(200)
             ->assertJson(['success' => true])
             ->assertJsonStructure(['status_logs', 'notification_logs']);
    }
}
