<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\NotificationLog;
use App\Models\RescheduleRequest;
use App\Models\ResponseToken;
use App\Models\StatusLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Muhamad Ibnhu - Confirmed (Online)
        $cand1 = Candidate::create([
            'nama' => 'Muhamad Ibnhu',
            'email' => 'ibnhu@example.com',
            'telepon' => '082199887711',
            'posisi' => 'Frontend Engineer',
            'berkas' => 'https://example.com/cv/ibnhu.pdf',
            'status' => 'Confirmed',
        ]);

        $int1 = Interview::create([
            'candidate_id' => $cand1->id,
            'tahap' => 'Tahap 1',
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(5)->setHour(10)->setMinute(0)->setSecond(0),
            'durasi_menit' => 45,
            'interviewer' => 'Rian Pratama',
            'link_meeting' => 'https://meet.google.com/xyz-interview-ibnhu',
            'status' => 'Confirmed',
            'jumlah_reschedule' => 0,
            'batas_respon' => now()->addDays(4)->setHour(10)->setMinute(0),
        ]);

        ResponseToken::create([
            'interview_id' => $int1->id,
            'token' => 'tok_' . Str::random(32),
            'sudah_dipakai' => true,
            'kedaluwarsa_at' => $int1->batas_respon,
        ]);

        StatusLog::create([
            'candidate_id' => $cand1->id,
            'interview_id' => $int1->id,
            'dari_status' => 'Invited',
            'ke_status' => 'Confirmed',
            'aktor' => 'Kandidat',
            'keterangan' => 'Kandidat menyetujui jadwal wawancara',
        ]);

        // 2. Siti Rahma - Reschedule Requested
        $cand2 = Candidate::create([
            'nama' => 'Siti Rahma',
            'email' => 'siti.rahma@gmail.com',
            'telepon' => '081244558899',
            'posisi' => 'UI/UX Designer',
            'berkas' => 'https://example.com/cv/siti.pdf',
            'status' => 'Reschedule Requested',
        ]);

        $int2 = Interview::create([
            'candidate_id' => $cand2->id,
            'tahap' => 'Tahap 1',
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(3)->setHour(10)->setMinute(0),
            'durasi_menit' => 45,
            'interviewer' => 'Dimas Ardiansyah',
            'link_meeting' => 'https://meet.google.com/xyz-interview-siti',
            'status' => 'Reschedule Requested',
            'jumlah_reschedule' => 1,
            'batas_respon' => now()->addDays(2)->setHour(10)->setMinute(0),
        ]);

        $token2 = ResponseToken::create([
            'interview_id' => $int2->id,
            'token' => 'tok_demo_siti_rahma',
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => $int2->batas_respon,
        ]);

        RescheduleRequest::create([
            'interview_id' => $int2->id,
            'alasan' => 'Ada sidang tugas akhir kampus di hari Selasa. Mohon reschedule ke Kamis jam 14:00.',
            'saran_waktu' => 'Kamis atau Jumat jam 14:00 WIB',
            'status_persetujuan' => 'Pending',
            'diajukan_at' => now()->subHours(2),
        ]);

        StatusLog::create([
            'candidate_id' => $cand2->id,
            'interview_id' => $int2->id,
            'dari_status' => 'Invited',
            'ke_status' => 'Reschedule Requested',
            'aktor' => 'Kandidat',
            'keterangan' => 'Pengajuan reschedule ke-1 oleh pelamar',
        ]);

        // 3. Ahmad Fauzi - Invited (Awaiting response)
        $cand3 = Candidate::create([
            'nama' => 'Ahmad Fauzi',
            'email' => 'fauzi.ahmad@gmail.com',
            'telepon' => '085611223344',
            'posisi' => 'Backend Engineer',
            'berkas' => 'https://example.com/cv/fauzi.pdf',
            'status' => 'Invited',
        ]);

        $int3 = Interview::create([
            'candidate_id' => $cand3->id,
            'tahap' => 'Tahap 1',
            'kategori' => 'online',
            'tanggal_waktu' => now()->addDays(6)->setHour(14)->setMinute(0),
            'durasi_menit' => 60,
            'interviewer' => 'Rian Pratama',
            'link_meeting' => 'https://meet.google.com/xyz-interview-fauzi',
            'status' => 'Invited',
            'jumlah_reschedule' => 0,
            'batas_respon' => now()->addDays(5)->setHour(14)->setMinute(0),
        ]);

        ResponseToken::create([
            'interview_id' => $int3->id,
            'token' => 'tok_demo_ahmad_fauzi',
            'sudah_dipakai' => false,
            'kedaluwarsa_at' => $int3->batas_respon,
        ]);

        // 4. Dewi Lestari - New Application (Needs schedule)
        Candidate::create([
            'nama' => 'Dewi Lestari',
            'email' => 'dewi.lestari@yahoo.com',
            'telepon' => '081399881234',
            'posisi' => 'Product Manager',
            'berkas' => 'https://example.com/cv/dewi.pdf',
            'status' => 'New Application',
        ]);

        // 5. Budi Santoso - New Application (Needs schedule)
        Candidate::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'telepon' => '081233445566',
            'posisi' => 'Frontend Developer',
            'berkas' => 'https://example.com/cv/budi.pdf',
            'status' => 'New Application',
        ]);

        // 6. Fajar Nugraha - Offline Interview (Confirmed)
        $cand6 = Candidate::create([
            'nama' => 'Fajar Nugraha',
            'email' => 'fajar.nugraha@outlook.com',
            'telepon' => '081988776655',
            'posisi' => 'DevOps Engineer',
            'berkas' => 'https://example.com/cv/fajar.pdf',
            'status' => 'Confirmed',
        ]);

        $int6 = Interview::create([
            'candidate_id' => $cand6->id,
            'tahap' => 'Tahap 1',
            'kategori' => 'offline',
            'tanggal_waktu' => now()->addDays(4)->setHour(13)->setMinute(30),
            'durasi_menit' => 60,
            'interviewer' => 'Nabila Putri',
            'lokasi' => 'Gedung Menara Cyber Lt. 5, Ruang Meeting Alpha, Jakarta Selatan',
            'status' => 'Confirmed',
            'jumlah_reschedule' => 0,
            'batas_respon' => now()->addDays(3)->setHour(13)->setMinute(30),
        ]);

        ResponseToken::create([
            'interview_id' => $int6->id,
            'token' => 'tok_demo_fajar_offline',
            'sudah_dipakai' => true,
            'kedaluwarsa_at' => $int6->batas_respon,
        ]);
    }
}
