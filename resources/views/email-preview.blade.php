@extends('layouts.app')

@section('title', 'Preview Email Undangan - Sistem Rekrutmen NKRI')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8 space-y-4">

    <!-- Information banner -->
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-center justify-between text-xs">
        <span class="text-red-900 font-semibold">Ini adalah preview format email resmi NKRI yang diterima kandidat di inbox mereka.</span>
        <a href="{{ url('/portal/' . ($token ?? 'tok_demo_ahmad_fauzi')) }}" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold text-[11px] transition shadow-xs">
            Buka Portal Terkait
        </a>
    </div>

    <!-- Email Client Wrapper -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <!-- Email Metadata Header -->
        <div class="bg-slate-50 border-b border-slate-200 p-4 text-xs space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-semibold w-16">Subjek:</span>
                <span class="font-bold text-slate-800">Undangan Wawancara Kerja: {{ $candidate?->posisi ?? 'Frontend Engineer' }} - {{ $candidate?->nama ?? 'Ahmad Fauzi' }} (NKRI)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-semibold w-16">Dari:</span>
                <span class="text-slate-600 font-medium">HR NKRI &lt;recruitment@nkri.id&gt;</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-semibold w-16">Kepada:</span>
                <span class="text-slate-600 font-medium">{{ $candidate?->nama ?? 'Ahmad Fauzi' }} &lt;{{ $candidate?->email ?? 'fauzi.ahmad@gmail.com' }}&gt;</span>
            </div>
        </div>

        <!-- Email Body Content -->
        <div class="p-6 text-xs text-slate-700 space-y-4 leading-relaxed">
            <!-- Brand Logo in Email Header -->
            <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                <img src="{{ asset('images/nkri-logo.png') }}" alt="Logo NKRI" class="h-8 w-auto object-contain">
                <span class="text-[11px] text-red-600 font-bold uppercase tracking-wider">Tim Rekrutmen</span>
            </div>

            <p>Halo <strong>{{ $candidate?->nama ?? 'Ahmad Fauzi' }}</strong>,</p>

            <p>
                Terima kasih atas minat dan lamaran Anda untuk posisi <strong>{{ $candidate?->posisi ?? 'Frontend Engineer' }}</strong> di <strong>NKRI</strong>. Berdasarkan hasil peninjauan awal berkas lamaran Anda, kami bermaksud mengundang Anda untuk mengikuti tahapan wawancara.
            </p>

            <!-- Interview Detail Summary Box -->
            <div class="bg-slate-50 border border-red-100 rounded-xl overflow-hidden my-3">
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-200/80">
                        <tr>
                            <td class="p-3 text-slate-400 font-semibold w-1/3 bg-slate-100/50">Waktu Wawancara</td>
                            <td class="p-3 font-bold text-slate-900">
                                {{ $interview?->tanggal_waktu ? $interview->tanggal_waktu->translatedFormat('l, d F Y, H:i') . ' WIB' : 'Jumat, 27 November 2026, 14:00 WIB' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-3 text-slate-400 font-semibold bg-slate-100/50">Pewawancara</td>
                            <td class="p-3 font-bold text-slate-900">{{ $interview?->interviewer ?? 'Tim HR NKRI' }}</td>
                        </tr>
                        <tr>
                            <td class="p-3 text-slate-400 font-semibold bg-slate-100/50">Kategori & Media</td>
                            <td class="p-3 font-bold text-red-600">
                                @if(($interview?->kategori ?? 'online') === 'online')
                                    Online (Google Meet)
                                @else
                                    Offline (Tatap Muka di Kantor NKRI)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="p-3 text-slate-400 font-semibold bg-slate-100/50">Batas Konfirmasi</td>
                            <td class="p-3 font-bold text-red-700">
                                {{ $interview?->batas_respon ? $interview->batas_respon->translatedFormat('l, d M Y, H:i') . ' WIB' : '24 jam sebelum jadwal' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-slate-500">
                Untuk memastikan kelancaran jadwal, silakan konfirmasikan kehadiran Anda melalui tombol di bawah ini:
            </p>

            <!-- Email Call To Action -->
            <div class="py-4 text-center space-y-3">
                <a href="{{ url('/portal/' . ($token ?? 'tok_demo_ahmad_fauzi')) }}" class="inline-block px-7 py-3 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-red-500/20 transition">
                    Konfirmasi Kehadiran Sekarang
                </a>
                
                <div class="flex items-center justify-center gap-3 text-[11px]">
                    <a href="{{ url('/portal/' . ($token ?? 'tok_demo_ahmad_fauzi')) }}" class="text-slate-600 font-medium hover:text-red-600 hover:underline">
                        Ajukan Reschedule
                    </a>
                    <span class="text-slate-300">•</span>
                    <a href="{{ url('/portal/' . ($token ?? 'tok_demo_ahmad_fauzi')) }}" class="text-slate-400 font-medium hover:text-red-600 hover:underline">
                        Tolak Undangan
                    </a>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">
                Catatan: Permohonan jadwal ulang (reschedule) hanya dapat diajukan maksimal 1 (satu) kali. Jika ada kendala teknis, silakan hubungi <a href="mailto:recruitment@nkri.id" class="text-red-600 underline">recruitment@nkri.id</a>.
            </p>
        </div>
    </div>
</div>
@endsection
