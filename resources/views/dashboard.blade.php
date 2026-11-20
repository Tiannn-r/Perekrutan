@extends('layouts.app')

@section('title', 'Dashboard HR - Sistem Konfirmasi Wawancara')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Jadwal Wawancara</h2>
            <p class="text-xs text-slate-400 mt-0.5">Kelola konfirmasi kehadiran dan jadwal wawancara kandidat.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                Database Terhubung
            </span>
            <button onclick="loadDashboardData()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Refresh
            </button>
        </div>
    </div>

    <!-- 5 Clean Metric Cards (Concise, High Contrast, No Redundant Icons) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Total Pelamar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition" onclick="setFilter('all')">
            <span class="text-xs font-semibold text-slate-400 block">Total Pelamar</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block" id="metric-total">-</span>
        </div>

        <!-- Perlu Jadwal -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-blue-300 transition" onclick="setFilter('new')">
            <span class="text-xs font-semibold text-slate-400 block">Perlu Jadwal</span>
            <span class="text-2xl font-black text-blue-600 mt-1 block" id="metric-perlu-jadwal">-</span>
        </div>

        <!-- Menunggu Respon -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-purple-300 transition" onclick="setFilter('invited')">
            <span class="text-xs font-semibold text-slate-400 block">Menunggu Respon</span>
            <span class="text-2xl font-black text-purple-600 mt-1 block" id="metric-menunggu-respon">-</span>
        </div>

        <!-- Reschedule Diminta -->
        <div class="bg-amber-50/40 p-4 rounded-xl border border-amber-300 shadow-xs cursor-pointer hover:border-amber-400 transition" onclick="setFilter('reschedule')">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-900">Reschedule</span>
                <span class="text-[10px] font-bold bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded">Perlu Aksi</span>
            </div>
            <span class="text-2xl font-black text-amber-700 mt-1 block" id="metric-reschedule">-</span>
        </div>

        <!-- Terkonfirmasi -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-emerald-300 transition" onclick="setFilter('confirmed')">
            <span class="text-xs font-semibold text-slate-400 block">Terkonfirmasi</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block" id="metric-terkonfirmasi">-</span>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Filter Toolbar -->
        <div class="p-3 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-semibold">
                <button onclick="setFilter('all')" id="tab-all" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white">Semua</button>
                <button onclick="setFilter('reschedule')" id="tab-reschedule" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600">Reschedule</button>
                <button onclick="setFilter('invited')" id="tab-invited" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600">Menunggu</button>
                <button onclick="setFilter('confirmed')" id="tab-confirmed" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600">Terkonfirmasi</button>
                <button onclick="setFilter('new')" id="tab-new" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600">Perlu Jadwal</button>
            </div>

            <div class="relative w-full sm:w-64">
                <input type="text" id="search-input" onkeyup="handleSearch()" placeholder="Cari kandidat..." class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        <!-- Candidate Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Kandidat</th>
                        <th class="py-3 px-4">Posisi</th>
                        <th class="py-3 px-4">Jadwal & Kategori</th>
                        <th class="py-3 px-4">Pewawancara</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="candidate-rows">
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">Memuat data kandidat...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: JADWALKAN WAWANCARA (HR ACTION) -->
<!-- ========================================== -->
<div id="modal-schedule" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden animate-in fade-in">
        <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-xs">Jadwalkan Wawancara</h3>
            <button onclick="closeModal('modal-schedule')" class="text-slate-400 hover:text-slate-600 text-base font-bold">&times;</button>
        </div>

        <form id="form-schedule" onsubmit="submitSchedule(event)" class="p-5 space-y-3 text-xs">
            <input type="hidden" id="sched-candidate-id">

            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex justify-between items-center">
                <span class="font-bold text-slate-900" id="sched-candidate-name">-</span>
                <span class="text-slate-500" id="sched-candidate-posisi">-</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Tanggal</label>
                    <input type="date" id="sched-date" required class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Jam (WIB)</label>
                    <input type="time" id="sched-time" required value="10:00" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Pewawancara</label>
                    <input type="text" id="sched-interviewer" required value="Rian Pratama" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Durasi</label>
                    <select id="sched-duration" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <option value="45">45 Menit</option>
                        <option value="60">60 Menit</option>
                        <option value="30">30 Menit</option>
                    </select>
                </div>
            </div>

            <!-- Kategori Wawancara (Online / Offline) -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kategori Wawancara</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100">
                        <input type="radio" name="schedule-category" value="online" checked onchange="toggleCategoryFields('sched', this.value)" class="text-indigo-600">
                        <span class="font-bold text-slate-800">Online</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100">
                        <input type="radio" name="schedule-category" value="offline" onchange="toggleCategoryFields('sched', this.value)" class="text-indigo-600">
                        <span class="font-bold text-slate-800">Offline (Tatap Muka)</span>
                    </label>
                </div>
            </div>

            <!-- Input Online: Link Meeting -->
            <div id="sched-field-online">
                <label class="block font-semibold text-slate-600 mb-1">Link Meeting</label>
                <input type="url" id="sched-meeting-link" value="https://meet.google.com/xyz-interview-slot" placeholder="https://meet.google.com/..." class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg font-mono text-[11px] text-indigo-600">
            </div>

            <!-- Input Offline: Lokasi & Ruangan Kantor -->
            <div id="sched-field-offline" class="hidden">
                <label class="block font-semibold text-slate-600 mb-1">Lokasi & Ruangan Kantor</label>
                <textarea id="sched-location" rows="2" placeholder="Gedung Menara Cyber Lt. 5, Ruang Meeting Alpha, Jakarta Selatan" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">Gedung Menara Cyber Lt. 5, Ruang Meeting Alpha, Jakarta Selatan</textarea>
            </div>

            <div class="px-0 pt-2 flex justify-end gap-2 text-xs font-bold">
                <button type="button" onclick="closeModal('modal-schedule')" class="px-3 py-1.5 text-slate-500 hover:text-slate-800">Batal</button>
                <button type="submit" id="btn-submit-schedule" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-xs">
                    Kirim Undangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: REVIEW RESCHEDULE (HR ACTION) -->
<!-- ========================================== -->
<div id="modal-reschedule" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-amber-300 shadow-xl overflow-hidden">
        <div class="px-5 py-3 bg-amber-500 text-white flex items-center justify-between">
            <h3 class="font-bold text-xs">Tinjau Permohonan Reschedule</h3>
            <button onclick="closeModal('modal-reschedule')" class="text-white text-base font-bold">&times;</button>
        </div>

        <form id="form-reschedule" onsubmit="submitResolveReschedule(event)" class="p-5 space-y-3 text-xs">
            <input type="hidden" id="resched-interview-id">

            <div class="flex justify-between items-center">
                <span class="font-bold text-slate-900" id="resched-cand-name">-</span>
                <span class="text-slate-500" id="resched-cand-posisi">-</span>
            </div>

            <!-- Alasan dari kandidat -->
            <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 space-y-1">
                <span class="font-bold text-amber-900 text-[11px] block">Alasan Kandidat:</span>
                <p class="text-slate-700 text-xs" id="resched-alasan">-</p>
                <div class="pt-1 text-[11px] text-amber-800">
                    Saran waktu: <strong id="resched-saran">-</strong>
                </div>
            </div>

            <div class="space-y-1 pt-1">
                <span class="font-bold text-slate-700 block">Tentukan Jadwal Baru:</span>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" id="resched-new-date" required class="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                    <input type="time" id="resched-new-time" required value="14:00" class="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                </div>
            </div>

            <!-- Kategori Pengganti (Online / Offline) -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kategori</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer">
                        <input type="radio" name="resched-category" value="online" checked onchange="toggleCategoryFields('resched', this.value)" class="text-amber-600">
                        <span class="font-bold text-slate-800">Online</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer">
                        <input type="radio" name="resched-category" value="offline" onchange="toggleCategoryFields('resched', this.value)" class="text-amber-600">
                        <span class="font-bold text-slate-800">Offline</span>
                    </label>
                </div>
            </div>

            <div id="resched-field-online">
                <label class="block font-semibold text-slate-600 mb-1">Link Meeting Baru</label>
                <input type="url" id="resched-new-link" value="https://meet.google.com/xyz-rescheduled" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg font-mono text-[11px] text-indigo-600">
            </div>

            <div id="resched-field-offline" class="hidden">
                <label class="block font-semibold text-slate-600 mb-1">Lokasi Kantor</label>
                <textarea id="resched-new-location" rows="2" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">Gedung Menara Cyber Lt. 5, Ruang Meeting Alpha, Jakarta Selatan</textarea>
            </div>

            <div class="px-0 pt-2 flex justify-end gap-2 text-xs font-bold">
                <button type="button" onclick="closeModal('modal-reschedule')" class="px-3 py-1.5 text-slate-500">Batal</button>
                <button type="submit" id="btn-submit-resched" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg shadow-xs">
                    Konfirmasi Jadwal Baru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: LINK PORTAL KANDIDAT & DETAIL -->
<!-- ========================================== -->
<div id="modal-portal-link" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-xs">Tautan Portal Respon Kandidat</h3>
            <button onclick="closeModal('modal-portal-link')" class="text-slate-400 hover:text-slate-600 text-base font-bold">&times;</button>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <p class="text-slate-600 leading-relaxed">
                Tautan ini dikirimkan ke email kandidat secara otomatis dan memuat token rahasia untuk konfirmasi kehadiran:
            </p>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 font-mono text-[11px] text-slate-800 break-all select-all" id="portal-link-url">
                -
            </div>

            <div class="flex gap-2 pt-2">
                <button onclick="copyPortalLink()" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
                    Salin Tautan
                </button>
                <a id="btn-open-portal-tab" href="#" target="_blank" class="py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs text-center transition">
                    Buka Portal
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentFilter = 'all';
    let candidatesData = [];

    document.addEventListener('DOMContentLoaded', () => {
        // Set default date for scheduling modal to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 2);
        document.getElementById('sched-date').value = tomorrow.toISOString().split('T')[0];
        
        loadDashboardData();
    });

    async function loadDashboardData() {
        try {
            // Load metrics and candidate table in parallel
            const [metricsRes, candidatesRes] = await Promise.all([
                fetch('/api/dashboard/metrics'),
                fetch(`/api/candidates?status=${currentFilter}`)
            ]);

            const metrics = await metricsRes.json();
            const candidates = await candidatesRes.json();

            // Render Metrics
            document.getElementById('metric-total').innerText = metrics.total;
            document.getElementById('metric-perlu-jadwal').innerText = metrics.perlu_jadwal;
            document.getElementById('metric-menunggu-respon').innerText = metrics.menunggu_respon;
            document.getElementById('metric-reschedule').innerText = metrics.reschedule;
            document.getElementById('metric-terkonfirmasi').innerText = metrics.terkonfirmasi;

            // Render Candidates Table
            candidatesData = candidates.data || [];
            renderCandidateRows(candidatesData);

        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            showToast('Gagal memuat data dari database', 'error');
        }
    }

    function renderCandidateRows(candidates) {
        const tbody = document.getElementById('candidate-rows');
        if (!candidates.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data kandidat pada kategori ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = candidates.map(c => {
            const latestInterview = c.latest_interview;
            const token = latestInterview?.token?.token;
            const reschedReq = latestInterview?.latest_reschedule_request;
            
            // Format status badge
            let statusBadge = '';
            if (c.status === 'New Application') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Perlu Jadwal</span>';
            } else if (c.status === 'Invited') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Menunggu</span>';
            } else if (c.status === 'Confirmed') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Terkonfirmasi</span>';
            } else if (c.status === 'Reschedule Requested') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Reschedule (1x)</span>';
            } else if (c.status === 'Declined') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Menolak</span>';
            }

            // Format schedule cell
            let scheduleText = '<span class="text-slate-400 italic">Belum dijadwalkan</span>';
            if (latestInterview) {
                const dateObj = new Date(latestInterview.tanggal_waktu);
                const formattedDate = dateObj.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                
                const kategoriBadge = latestInterview.kategori === 'online' 
                    ? '<span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded ml-1">Online</span>'
                    : '<span class="text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded ml-1">Offline</span>';

                if (c.status === 'Reschedule Requested' && reschedReq) {
                    scheduleText = `
                        <span class="text-slate-400 line-through block text-[11px]">${formattedDate}</span>
                        <span class="text-[11px] text-amber-700 font-bold block">Minta: ${reschedReq.saran_waktu || 'Jadwal lain'}</span>
                    `;
                } else {
                    scheduleText = `
                        <span class="font-semibold text-slate-800 block">${formattedDate}</span>
                        <div class="mt-0.5">${kategoriBadge}</div>
                    `;
                }
            }

            // Action button
            let actionBtn = '';
            if (c.status === 'New Application') {
                actionBtn = `<button onclick="openScheduleModal(${c.id}, '${escapeHtml(c.nama)}', '${escapeHtml(c.posisi)}')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition">Jadwalkan</button>`;
            } else if (c.status === 'Reschedule Requested') {
                actionBtn = `<button onclick="openRescheduleModal(${latestInterview?.id}, '${escapeHtml(c.nama)}', '${escapeHtml(c.posisi)}', '${escapeHtml(reschedReq?.alasan || '-')}', '${escapeHtml(reschedReq?.saran_waktu || '-')}')" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs transition">Tinjau</button>`;
            } else if (token) {
                actionBtn = `
                    <div class="flex items-center justify-end gap-1">
                        <button onclick="openPortalLinkModal('${token}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">Link Portal</button>
                    </div>
                `;
            } else {
                actionBtn = `<span class="text-slate-300">-</span>`;
            }

            return `
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="py-3 px-4">
                        <span class="font-bold text-slate-900 block text-xs">${escapeHtml(c.nama)}</span>
                        <span class="text-slate-400 text-[11px]">${escapeHtml(c.email)}</span>
                    </td>
                    <td class="py-3 px-4 font-medium text-slate-800">${escapeHtml(c.posisi)}</td>
                    <td class="py-3 px-4">${scheduleText}</td>
                    <td class="py-3 px-4 text-slate-800">${latestInterview?.interviewer || '-'}</td>
                    <td class="py-3 px-4">${statusBadge}</td>
                    <td class="py-3 px-4 text-right">${actionBtn}</td>
                </tr>
            `;
        }).join('');
    }

    function setFilter(status) {
        currentFilter = status;
        
        // Update tab styles
        const tabs = ['all', 'reschedule', 'invited', 'confirmed', 'new'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (btn) {
                if (t === status) {
                    btn.className = 'px-3 py-1.5 rounded-lg bg-slate-900 text-white';
                } else {
                    btn.className = 'px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200';
                }
            }
        });

        loadDashboardData();
    }

    function handleSearch() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        if (!query) {
            renderCandidateRows(candidatesData);
            return;
        }

        const filtered = candidatesData.filter(c => 
            c.nama.toLowerCase().includes(query) ||
            c.email.toLowerCase().includes(query) ||
            c.posisi.toLowerCase().includes(query)
        );

        renderCandidateRows(filtered);
    }

    // Category switch between Online and Offline in Modals
    function toggleCategoryFields(prefix, value) {
        const onlineDiv = document.getElementById(`${prefix}-field-online`);
        const offlineDiv = document.getElementById(`${prefix}-field-offline`);

        if (value === 'online') {
            onlineDiv?.classList.remove('hidden');
            offlineDiv?.classList.add('hidden');
        } else {
            onlineDiv?.classList.add('hidden');
            offlineDiv?.classList.remove('hidden');
        }
    }

    // Modal Schedule
    function openScheduleModal(candId, candName, candPosisi) {
        document.getElementById('sched-candidate-id').value = candId;
        document.getElementById('sched-candidate-name').innerText = candName;
        document.getElementById('sched-candidate-posisi').innerText = candPosisi;
        
        document.getElementById('modal-schedule').classList.remove('hidden');
    }

    async function submitSchedule(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-submit-schedule');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Menyimpan...';

        const candidateId = document.getElementById('sched-candidate-id').value;
        const date = document.getElementById('sched-date').value;
        const time = document.getElementById('sched-time').value;
        const kategori = document.querySelector('input[name="schedule-category"]:checked').value;
        const interviewer = document.getElementById('sched-interviewer').value;
        const duration = document.getElementById('sched-duration').value;
        const meetingLink = document.getElementById('sched-meeting-link').value;
        const location = document.getElementById('sched-location').value;

        try {
            const res = await fetch('/api/interviews/schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    candidate_id: candidateId,
                    tanggal_waktu: `${date} ${time}:00`,
                    durasi_menit: duration,
                    kategori: kategori,
                    interviewer: interviewer,
                    link_meeting: kategori === 'online' ? meetingLink : null,
                    lokasi: kategori === 'offline' ? location : null,
                })
            });

            const data = await res.json();
            if (data.success) {
                closeModal('modal-schedule');
                showToast('Jadwal berhasil dibuat & undangan dikirim!');
                loadDashboardData();

                // Open Portal Link Preview directly
                if (data.token) {
                    openPortalLinkModal(data.token);
                }
            } else {
                showToast(data.message || 'Gagal membuat jadwal', 'error');
            }
        } catch (error) {
            console.error('Error scheduling:', error);
            showToast('Terjadi kesalahan koneksi', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Kirim Undangan';
        }
    }

    // Modal Reschedule Review
    function openRescheduleModal(interviewId, candName, candPosisi, alasan, saran) {
        document.getElementById('resched-interview-id').value = interviewId;
        document.getElementById('resched-cand-name').innerText = candName;
        document.getElementById('resched-cand-posisi').innerText = candPosisi;
        document.getElementById('resched-alasan').innerText = `"${alasan}"`;
        document.getElementById('resched-saran').innerText = saran;

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 3);
        document.getElementById('resched-new-date').value = tomorrow.toISOString().split('T')[0];

        document.getElementById('modal-reschedule').classList.remove('hidden');
    }

    async function submitResolveReschedule(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-submit-resched');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Menyimpan...';

        const interviewId = document.getElementById('resched-interview-id').value;
        const date = document.getElementById('resched-new-date').value;
        const time = document.getElementById('resched-new-time').value;
        const kategori = document.querySelector('input[name="resched-category"]:checked').value;
        const meetingLink = document.getElementById('resched-new-link').value;
        const location = document.getElementById('resched-new-location').value;

        try {
            const res = await fetch(`/api/interviews/${interviewId}/reschedule-resolve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'accept',
                    new_tanggal_waktu: `${date} ${time}:00`,
                    kategori: kategori,
                    link_meeting: kategori === 'online' ? meetingLink : null,
                    lokasi: kategori === 'offline' ? location : null,
                })
            });

            const data = await res.json();
            if (data.success) {
                closeModal('modal-reschedule');
                showToast('Jadwal baru disetujui & token diperbarui!');
                loadDashboardData();
            } else {
                showToast(data.message || 'Gagal memperbarui jadwal', 'error');
            }
        } catch (error) {
            console.error('Error resolving reschedule:', error);
            showToast('Terjadi kesalahan koneksi', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Konfirmasi Jadwal Baru';
        }
    }

    // Modal Portal Link
    function openPortalLinkModal(token) {
        const fullUrl = `${window.location.origin}/portal/${token}`;
        document.getElementById('portal-link-url').innerText = fullUrl;
        document.getElementById('btn-open-portal-tab').href = fullUrl;
        document.getElementById('modal-portal-link').classList.remove('hidden');
    }

    function copyPortalLink() {
        const url = document.getElementById('portal-link-url').innerText;
        navigator.clipboard.writeText(url).then(() => {
            showToast('Tautan portal berhasil disalin ke clipboard!');
        });
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endpush
