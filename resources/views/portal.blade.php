@extends('layouts.app')

@section('title', 'Undangan Wawancara - ' . ($candidate?->nama ?? 'Portal Kandidat'))

@section('content')
<div class="max-w-md mx-auto px-4 py-8 space-y-4">

    <!-- Token Security Indicator Bar -->
    <div class="px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-[11px] text-slate-500 font-mono flex items-center justify-between shadow-xs">
        <span class="truncate max-w-[240px]">Tautan: {{ request()->url() }}</span>
        <span class="font-bold px-2 py-0.5 rounded {{ ($responseToken && $responseToken->isValid()) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}" id="token-status-pill">
            {{ ($responseToken && $responseToken->isValid()) ? 'Token Valid' : ($responseToken?->sudah_dipakai ? 'Token Terpakai' : 'Token Tidak Aktif') }}
        </span>
    </div>

    <!-- ======================================================== -->
    <!-- CARD 1: DETAIL UNDANGAN UTAMA -->
    <!-- ======================================================== -->
    <div id="card-invitation-main" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all {{ ($responseToken && $responseToken->isValid()) ? '' : 'hidden' }}">
        
        <!-- Header Brand Banner -->
        <div class="bg-slate-900 text-white p-6 text-center space-y-1">
            <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center font-black text-base mx-auto text-white border border-white/20 mb-2">
                TN
            </div>
            <h3 class="font-bold text-base tracking-tight">TechNusantara Inc.</h3>
            <p class="text-[11px] text-slate-300">Undangan Resmi Wawancara Kerja</p>
        </div>

        <div class="p-6 space-y-5">
            <!-- Candidate Greeting -->
            <div>
                <span class="text-[10px] font-extrabold text-indigo-600 tracking-wider uppercase block mb-1">HALO,</span>
                <h4 class="text-xl font-black text-slate-900" id="portal-cand-name">{{ $candidate?->nama ?? 'Kandidat' }}</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Terima kasih atas lamaran Anda untuk posisi <strong class="text-slate-800" id="portal-cand-posisi">{{ $candidate?->posisi ?? 'Posisi Lamaran' }}</strong>. Tim HR kami mengundang Anda untuk mengikuti tahapan wawancara.
                </p>
            </div>

            <!-- Schedule Data Grid (Concise & High Legibility) -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2.5 text-xs">
                <div class="flex justify-between items-start gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400">Jadwal:</span>
                    <span class="font-bold text-slate-900 text-right" id="portal-schedule-time">
                        {{ $interview?->tanggal_waktu ? $interview->tanggal_waktu->translatedFormat('l, d F Y, H:i') . ' WIB' : '-' }}
                    </span>
                </div>

                <div class="flex justify-between items-center gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400">Kategori:</span>
                    <span class="font-bold px-2 py-0.5 rounded text-[11px] {{ $interview?->kategori === 'online' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-200 text-slate-800' }}" id="portal-kategori-text">
                        {{ strtoupper($interview?->kategori ?? 'ONLINE') }}
                    </span>
                </div>

                <div class="flex justify-between items-start gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400">{{ $interview?->kategori === 'online' ? 'Media Rapat:' : 'Lokasi Kantor:' }}</span>
                    <span class="font-medium text-slate-800 text-right text-[11px]" id="portal-lokasi-text">
                        @if($interview?->kategori === 'online')
                            Google Meet (Tautan aktif setelah konfirmasi hadir)
                        @else
                            {{ $interview?->lokasi ?? 'Alamat kantor akan diinformasikan' }}
                        @endif
                    </span>
                </div>

                <div class="flex justify-between items-center gap-2 pb-2 border-b border-slate-200/60">
                    <span class="text-slate-400">Durasi:</span>
                    <span class="font-medium text-slate-800">{{ $interview?->durasi_menit ?? 45 }} Menit</span>
                </div>

                <div class="flex justify-between items-center gap-2">
                    <span class="text-slate-400">Pewawancara:</span>
                    <span class="font-medium text-slate-800">{{ $interview?->interviewer ?? 'Tim Rekrutmen' }}</span>
                </div>
            </div>

            <!-- Batas Waktu Warning -->
            <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-[11px] text-amber-900 space-y-1">
                <div class="font-bold">Batas Waktu Konfirmasi:</div>
                <p class="text-amber-800 leading-normal">
                    Mohon konfirmasi paling lambat <strong id="portal-deadline">{{ $interview?->batas_respon ? $interview->batas_respon->translatedFormat('l, d M Y, H:i') . ' WIB' : '24 jam sebelum jadwal' }}</strong>. Permohonan reschedule hanya diperkenankan <strong>maksimal 1 (satu) kali</strong>.
                </p>
            </div>

            <!-- CTA Buttons (Strict Concise 1-2 words, No Redundant Icons) -->
            <div class="space-y-2 pt-2">
                <button onclick="submitResponse('confirm')" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs">
                    Konfirmasi Hadir
                </button>

                <button onclick="showRescheduleCard()" class="w-full py-2.5 px-4 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold rounded-xl text-xs transition">
                    Ajukan Reschedule
                </button>

                <button onclick="showDeclineCard()" class="w-full py-2 px-4 text-slate-400 hover:text-rose-600 font-semibold rounded-xl text-xs transition">
                    Tolak Undangan
                </button>
            </div>
        </div>
    </div>


    <!-- ======================================================== -->
    <!-- CARD 2: DEDICATED RESCHEDULE CARD (KUOTA STRICT 1x) -->
    <!-- ======================================================== -->
    <div id="card-reschedule-form" class="hidden bg-white rounded-2xl border-2 border-amber-300 shadow-md p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Ajukan Reschedule</h3>
                <p class="text-[11px] text-slate-400">Permohonan penjadwalan ulang wawancara</p>
            </div>
            <span class="text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded border border-amber-200">
                Kuota: {{ ($interview?->jumlah_reschedule ?? 0) >= 1 ? 'Habis (1/1)' : 'Tersedia 1x' }}
            </span>
        </div>

        @if(($interview?->jumlah_reschedule ?? 0) >= 1)
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                <span class="font-bold block">Kuota Reschedule Telah Digunakan</span>
                <p class="text-[11px] leading-relaxed">
                    Sesuai ketentuan, permohonan reschedule hanya dapat diajukan maksimal 1 (satu) kali untuk setiap tahapan seleksi. Anda tidak dapat mengajukan perubahan jadwal lagi.
                </p>
                <div class="pt-2">
                    <button onclick="backToInvitation()" class="w-full py-2 bg-slate-900 text-white font-bold rounded-lg text-xs">
                        Kembali ke Undangan
                    </button>
                </div>
            </div>
        @else
            <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                Jadwal semula: <strong class="text-slate-900">{{ $interview?->tanggal_waktu ? $interview->tanggal_waktu->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</strong>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan Penundaan <span class="text-rose-500">*</span></label>
                    <textarea id="resched-reason" rows="3" required placeholder="Tulis alasan jelas mengapa Anda tidak dapat hadir pada jadwal tersebut..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Saran Waktu Alternatif</label>
                    <input type="text" id="resched-suggested-time" placeholder="Contoh: Jumat, 27 Nov jam 14:00 WIB" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 text-xs">
                    <span class="text-[10px] text-slate-400 block mt-1">Jadwal baru final akan disesuaikan oleh tim rekrutmen.</span>
                </div>

                <div class="flex gap-2 pt-2">
                    <button onclick="backToInvitation()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button onclick="submitResponse('reschedule')" id="btn-submit-reschedule-card" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        Kirim Permohonan
                    </button>
                </div>
            </div>
        @endif
    </div>


    <!-- ======================================================== -->
    <!-- CARD 3: DEDICATED 2-STEP DECLINE VERIFICATION CARD -->
    <!-- ======================================================== -->
    <div id="card-decline-confirm" class="hidden bg-white rounded-2xl border-2 border-rose-300 shadow-md p-6 space-y-4 animate-in fade-in">
        <div class="pb-3 border-b border-rose-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 block mb-0.5">Verifikasi Penolakan</span>
            <h3 class="text-sm font-bold text-slate-900">Apakah Anda yakin menolak undangan ini?</h3>
            <p class="text-xs text-slate-500 mt-1">
                Berikan alasannya. Keputusan penolakan ini bersifat <strong class="text-rose-700">final</strong> dan membatalkan proses seleksi Anda.
            </p>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                <textarea id="decline-reason" rows="3" required placeholder="Tulis alasan Anda (misal: sudah menerima tawaran kerja lain, tidak sesuai kriteria, dll)..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-rose-500 text-xs"></textarea>
            </div>

            <div class="space-y-2 pt-2">
                <button onclick="backToInvitation()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                    Batal (Kembali)
                </button>
                <button onclick="submitResponse('decline')" id="btn-submit-decline-card" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold text-xs rounded-xl transition">
                    Konfirmasi Tolak Undangan
                </button>
            </div>
        </div>
    </div>


    <!-- ======================================================== -->
    <!-- CARD 4: RESULT / FEEDBACK STATUS CARD -->
    <!-- ======================================================== -->
    <div id="card-feedback-result" class="{{ ($responseToken && !$responseToken->isValid()) ? '' : 'hidden' }} bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-center space-y-4 animate-in fade-in">
        
        <div id="result-badge-container">
            @if($interview?->status === 'Confirmed')
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Kehadiran Terkonfirmasi
                </span>
            @elseif($interview?->status === 'Reschedule Requested')
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    Menunggu Tinjauan Reschedule
                </span>
            @elseif($interview?->status === 'Declined')
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                    Undangan Ditolak
                </span>
            @else
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                    Tautan Ditutup
                </span>
            @endif
        </div>

        <h3 id="result-title" class="text-base font-extrabold text-slate-900">
            @if($interview?->status === 'Confirmed')
                Terima Kasih, Kehadiran Anda Telah Terkonfirmasi
            @elseif($interview?->status === 'Reschedule Requested')
                Permohonan Reschedule Telah Diterima
            @elseif($interview?->status === 'Declined')
                Undangan Wawancara Ditolak
            @else
                Tautan Undangan Tidak Aktif
            @endif
        </h3>

        <p id="result-desc" class="text-xs text-slate-500 leading-relaxed">
            @if($interview?->status === 'Confirmed')
                Kami telah mengunci jadwal wawancara Anda. Silakan catat jadwal dan bergabung tepat waktu sesuai petunjuk di bawah.
            @elseif($interview?->status === 'Reschedule Requested')
                Tim rekrutmen akan meninjau ketersediaan jadwal pewawancara dan mengirimkan undangan baru ke email Anda.
            @elseif($interview?->status === 'Declined')
                Terima kasih atas konfirmasi Anda. Data lamaran Anda telah diperbarui dan proses seleksi ditutup.
            @else
                Tautan ini sudah selesai digunakan atau telah melewati batas waktu yang ditentukan.
            @endif
        </p>

        <!-- Dynamic Action Information (Meeting Link or Location) -->
        <div id="result-extra" class="pt-2 text-xs text-left">
            @if($interview?->status === 'Confirmed')
                @if($interview?->kategori === 'online')
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3.5 space-y-2">
                        <span class="text-[11px] font-bold text-indigo-900 block">Tautan Google Meet:</span>
                        <div class="font-mono text-[11px] text-indigo-700 bg-white p-2 rounded-lg border border-indigo-100 break-all select-all">
                            {{ $interview->link_meeting ?? 'https://meet.google.com/xyz-technusantara' }}
                        </div>
                        <a href="{{ $interview->link_meeting ?? '#' }}" target="_blank" class="block w-full text-center py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition">
                            Gabung Rapat
                        </a>
                    </div>
                @else
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-1.5">
                        <span class="text-[11px] font-bold text-slate-900 block">Lokasi Pertemuan Tatap Muka:</span>
                        <p class="text-slate-700 text-xs">{{ $interview->lokasi ?? 'Alamat kantor terlampir pada email' }}</p>
                        <span class="text-[10px] text-slate-400 block pt-1">Harap hadir 15 menit sebelum wawancara dimulai.</span>
                    </div>
                @endif
            @endif
        </div>

        <div class="pt-4 border-t border-slate-100">
            <a href="{{ url('/dashboard') }}" class="text-xs text-indigo-600 font-bold hover:underline">
                Kembali ke Dashboard HR
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const token = @json($token);

    function showRescheduleCard() {
        document.getElementById('card-invitation-main').classList.add('hidden');
        document.getElementById('card-decline-confirm').classList.add('hidden');
        document.getElementById('card-reschedule-form').classList.remove('hidden');
    }

    function showDeclineCard() {
        document.getElementById('card-invitation-main').classList.add('hidden');
        document.getElementById('card-reschedule-form').classList.add('hidden');
        document.getElementById('card-decline-confirm').classList.remove('hidden');
    }

    function backToInvitation() {
        document.getElementById('card-reschedule-form').classList.add('hidden');
        document.getElementById('card-decline-confirm').classList.add('hidden');
        document.getElementById('card-invitation-main').classList.remove('hidden');
    }

    async function submitResponse(action) {
        let alasan = null;
        let saranWaktu = null;

        if (action === 'reschedule') {
            alasan = document.getElementById('resched-reason')?.value.trim();
            saranWaktu = document.getElementById('resched-suggested-time')?.value.trim();
            if (!alasan) {
                showToast('Mohon isi alasan penundaan', 'warning');
                return;
            }
        } else if (action === 'decline') {
            alasan = document.getElementById('decline-reason')?.value.trim();
            if (!alasan) {
                showToast('Mohon isi alasan penolakan', 'warning');
                return;
            }
        }

        try {
            const res = await fetch(`/api/portal/${token}/respond`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    alasan: alasan,
                    saran_waktu: saranWaktu,
                })
            });

            const data = await res.json();

            if (data.success) {
                showToast(data.message || 'Respon berhasil disimpan!');
                
                // Hide active cards and display the result card
                document.getElementById('card-invitation-main').classList.add('hidden');
                document.getElementById('card-reschedule-form').classList.add('hidden');
                document.getElementById('card-decline-confirm').classList.add('hidden');

                const resultCard = document.getElementById('card-feedback-result');
                const badgeContainer = document.getElementById('result-badge-container');
                const resultTitle = document.getElementById('result-title');
                const resultDesc = document.getElementById('result-desc');
                const resultExtra = document.getElementById('result-extra');

                if (action === 'confirm') {
                    badgeContainer.innerHTML = '<span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Kehadiran Terkonfirmasi</span>';
                    resultTitle.innerText = 'Terima Kasih, Kehadiran Anda Telah Terkonfirmasi';
                    resultDesc.innerText = 'Kami telah mengunci jadwal wawancara Anda. Silakan bersiap tepat waktu sesuai detail yang diberikan.';
                    
                    if (data.interview?.kategori === 'online') {
                        resultExtra.innerHTML = `
                            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3.5 space-y-2 text-left">
                                <span class="text-[11px] font-bold text-indigo-900 block">Tautan Google Meet:</span>
                                <div class="font-mono text-[11px] text-indigo-700 bg-white p-2 rounded-lg border border-indigo-100 break-all select-all">${data.interview.link_meeting || 'https://meet.google.com/xyz'}</div>
                                <a href="${data.interview.link_meeting || '#'}" target="_blank" class="block w-full text-center py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition">Gabung Rapat</a>
                            </div>
                        `;
                    } else {
                        resultExtra.innerHTML = `
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-1 text-left">
                                <span class="text-[11px] font-bold text-slate-900 block">Lokasi Kantor:</span>
                                <p class="text-slate-700 text-xs">${data.interview.lokasi || 'Alamat kantor terlampir'}</p>
                            </div>
                        `;
                    }
                } else if (action === 'reschedule') {
                    badgeContainer.innerHTML = '<span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">Permohonan Reschedule Terkirim</span>';
                    resultTitle.innerText = 'Permohonan Anda Sedang Ditinjau Tim HR';
                    resultDesc.innerText = 'Permohonan reschedule Anda telah diteruskan ke pewawancara. Jadwal pengganti akan kami kirimkan ke email Anda.';
                    resultExtra.innerHTML = '';
                } else if (action === 'decline') {
                    badgeContainer.innerHTML = '<span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">Undangan Ditolak</span>';
                    resultTitle.innerText = 'Undangan Wawancara Resmi Ditolak';
                    resultDesc.innerText = 'Terima kasih atas konfirmasi dan waktu Anda. Proses seleksi telah ditutup.';
                    resultExtra.innerHTML = '';
                }

                // Update token status pill
                const pill = document.getElementById('token-status-pill');
                if (pill) {
                    pill.className = 'font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-700';
                    pill.innerText = 'Token Terpakai';
                }

                resultCard.classList.remove('hidden');

            } else {
                showToast(data.message || 'Gagal menyimpan respon', 'error');
            }
        } catch (error) {
            console.error('Error submitting response:', error);
            showToast('Terjadi kesalahan komunikasi dengan server', 'error');
        }
    }
</script>
@endpush
