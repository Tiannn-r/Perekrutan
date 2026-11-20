@extends('layouts.app')

@section('title', 'Dashboard HR - Nanggala Kencana Rekatama Indonesia')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Jadwal Wawancara</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola konfirmasi kehadiran dan jadwal wawancara kandidat Nanggala Kencana Rekatama Indonesia.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-red-50 text-red-700 text-xs font-semibold border border-red-200">
                Database Terhubung
            </span>
            <button onclick="loadDashboardData()" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-700 hover:border-red-200 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs">
                Refresh
            </button>
        </div>
    </div>

    <!-- 5 Clean Metric Cards (Merah Putih Clean Theme) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Total Pelamar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-red-300 transition" onclick="setFilter('all')">
            <span class="text-xs font-semibold text-slate-400 block">Total Pelamar</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block" id="metric-total">-</span>
        </div>

        <!-- Perlu Jadwal -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-red-300 transition" onclick="setFilter('new')">
            <span class="text-xs font-semibold text-slate-400 block">Perlu Jadwal</span>
            <span class="text-2xl font-black text-slate-700 mt-1 block" id="metric-perlu-jadwal">-</span>
        </div>

        <!-- Menunggu Respon -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-red-300 transition" onclick="setFilter('invited')">
            <span class="text-xs font-semibold text-slate-400 block">Menunggu Respon</span>
            <span class="text-2xl font-black text-red-600 mt-1 block" id="metric-menunggu-respon">-</span>
        </div>

        <!-- Reschedule Diminta -->
        <div class="bg-red-50/40 p-4 rounded-xl border border-red-200 shadow-xs cursor-pointer hover:border-red-400 transition" onclick="setFilter('reschedule')">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-red-900">Reschedule</span>
                <span class="text-[10px] font-bold bg-red-100 text-red-800 px-1.5 py-0.5 rounded border border-red-200">Perlu Aksi</span>
            </div>
            <span class="text-2xl font-black text-red-700 mt-1 block" id="metric-reschedule">-</span>
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
                <button onclick="setFilter('all')" id="tab-all" class="px-3 py-1.5 rounded-lg bg-red-600 text-white shadow-xs">Semua</button>
                <button onclick="setFilter('reschedule')" id="tab-reschedule" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700">Reschedule</button>
                <button onclick="setFilter('invited')" id="tab-invited" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700">Menunggu</button>
                <button onclick="setFilter('confirmed')" id="tab-confirmed" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700">Terkonfirmasi</button>
                <button onclick="setFilter('new')" id="tab-new" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700">Perlu Jadwal</button>
            </div>

            <div class="relative w-full sm:w-64">
                <input type="text" id="search-input" onkeyup="handleSearch()" placeholder="Cari kandidat..." class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:bg-white">
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
    <div class="bg-white rounded-2xl max-w-md w-full border border-red-200 shadow-2xl overflow-hidden animate-in fade-in">
        <div class="px-5 py-3.5 bg-red-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/nkri-icon.png') }}" class="w-5 h-5 bg-white rounded-full p-0.5 object-contain" alt="NKRI">
                <h3 class="font-bold text-xs tracking-wide">Jadwalkan Wawancara</h3>
            </div>
            <button onclick="closeModal('modal-schedule')" class="text-white hover:text-red-100 text-base font-bold">&times;</button>
        </div>

        <form id="form-schedule" onsubmit="submitSchedule(event)" class="p-5 space-y-3.5 text-xs">
            <input type="hidden" id="sched-candidate-id">

            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex justify-between items-center">
                <span class="font-bold text-slate-900" id="sched-candidate-name">-</span>
                <span class="text-slate-500 font-medium" id="sched-candidate-posisi">-</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Tanggal</label>
                    <input type="date" id="sched-date" required class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Jam (WIB)</label>
                    <input type="time" id="sched-time" required value="10:00" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Pewawancara</label>
                    <input type="text" id="sched-interviewer" required value="Tim HR Nanggala Kencana Rekatama Indonesia" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Durasi</label>
                    <select id="sched-duration" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
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
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-red-50 hover:border-red-200">
                        <input type="radio" name="schedule-category" value="online" checked onchange="toggleCategoryFields('sched', this.value)" class="text-red-600 focus:ring-red-500">
                        <span class="font-bold text-slate-800">Online</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-red-50 hover:border-red-200">
                        <input type="radio" name="schedule-category" value="offline" onchange="toggleCategoryFields('sched', this.value)" class="text-red-600 focus:ring-red-500">
                        <span class="font-bold text-slate-800">Offline (Kantor)</span>
                    </label>
                </div>
            </div>

            <!-- Input Online: Link Meeting -->
            <div id="sched-field-online">
                <label class="block font-semibold text-slate-600 mb-1">Link Meeting (Google Meet / Zoom)</label>
                <input type="url" id="sched-meeting-link" value="https://meet.google.com/nkri-interview" placeholder="https://meet.google.com/..." class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg font-mono text-[11px] text-red-600 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            <!-- Input Offline: Lokasi Kantor -->
            <div id="sched-field-offline" class="hidden">
                <label class="block font-semibold text-slate-600 mb-1">Lokasi & Ruangan Pertemuan</label>
                <textarea id="sched-location" rows="2" placeholder="Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-red-500">Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta</textarea>
            </div>

            <div class="px-0 pt-2 flex justify-end gap-2 text-xs font-bold">
                <button type="button" onclick="closeModal('modal-schedule')" class="px-3 py-1.5 text-slate-500 hover:text-slate-800">Batal</button>
                <button type="submit" id="btn-submit-schedule" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-xs transition">
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
    <div class="bg-white rounded-2xl max-w-md w-full border border-red-300 shadow-2xl overflow-hidden">
        <div class="px-5 py-3.5 bg-red-600 text-white flex items-center justify-between">
            <h3 class="font-bold text-xs tracking-wide">Tinjau Permohonan Reschedule</h3>
            <button onclick="closeModal('modal-reschedule')" class="text-white hover:text-red-100 text-base font-bold">&times;</button>
        </div>

        <form id="form-reschedule" onsubmit="submitResolveReschedule(event)" class="p-5 space-y-3.5 text-xs">
            <input type="hidden" id="resched-interview-id">

            <div class="flex justify-between items-center">
                <span class="font-bold text-slate-900" id="resched-cand-name">-</span>
                <span class="text-slate-500 font-medium" id="resched-cand-posisi">-</span>
            </div>

            <!-- Alasan dari kandidat -->
            <div class="p-3 rounded-lg bg-red-50/60 border border-red-200 space-y-1">
                <span class="font-bold text-red-900 text-[11px] block">Alasan Kandidat:</span>
                <p class="text-slate-700 text-xs" id="resched-alasan">-</p>
                <div class="pt-1 text-[11px] text-red-800">
                    Saran waktu: <strong id="resched-saran">-</strong>
                </div>
            </div>

            <div class="space-y-1 pt-1">
                <span class="font-bold text-slate-700 block">Tentukan Jadwal Baru:</span>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" id="resched-new-date" required class="p-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                    <input type="time" id="resched-new-time" required value="14:00" class="p-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
            </div>

            <!-- Kategori Pengganti (Online / Offline) -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kategori</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-red-50">
                        <input type="radio" name="resched-category" value="online" checked onchange="toggleCategoryFields('resched', this.value)" class="text-red-600 focus:ring-red-500">
                        <span class="font-bold text-slate-800">Online</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-red-50">
                        <input type="radio" name="resched-category" value="offline" onchange="toggleCategoryFields('resched', this.value)" class="text-red-600 focus:ring-red-500">
                        <span class="font-bold text-slate-800">Offline</span>
                    </label>
                </div>
            </div>

            <div id="resched-field-online">
                <label class="block font-semibold text-slate-600 mb-1">Link Meeting Baru</label>
                <input type="url" id="resched-new-link" value="https://meet.google.com/nkri-interview-reschedule" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg font-mono text-[11px] text-red-600 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            <div id="resched-field-offline" class="hidden">
                <label class="block font-semibold text-slate-600 mb-1">Lokasi Kantor</label>
                <textarea id="resched-new-location" rows="2" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-red-500">Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta</textarea>
            </div>

            <div class="px-0 pt-2 flex justify-end gap-2 text-xs font-bold">
                <button type="button" onclick="closeModal('modal-reschedule')" class="px-3 py-1.5 text-slate-500 hover:text-slate-800">Batal</button>
                <button type="submit" id="btn-submit-resched" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-xs transition">
                    Konfirmasi Jadwal Baru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DETAIL KANDIDAT & WAWANCARA (HR ACTION) -->
<!-- ========================================== -->
<div id="modal-candidate-detail" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in">
        <!-- Header -->
        <div class="px-5 py-3.5 bg-red-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/nkri-icon.png') }}" class="w-5 h-5 bg-white rounded-full p-0.5 object-contain" alt="NKRI">
                <h3 class="font-bold text-xs tracking-wide">Detail Wawancara Kandidat</h3>
            </div>
            <button onclick="closeModal('modal-candidate-detail')" class="text-white hover:text-red-100 text-base font-bold">&times;</button>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <!-- Candidate Profile Info -->
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm" id="detail-cand-name">-</h4>
                    <p class="text-slate-500 text-xs mt-0.5" id="detail-cand-role">-</p>
                    <div class="flex items-center gap-3 text-[11px] text-slate-400 mt-1">
                        <span id="detail-cand-email">-</span>
                        <span>•</span>
                        <span id="detail-cand-phone">-</span>
                    </div>
                </div>
                <div id="detail-status-badge">
                    <!-- Status Badge -->
                </div>
            </div>

            <!-- Schedule & Interviewer -->
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-0.5">
                    <span class="text-slate-400 text-[11px] block">Waktu Wawancara</span>
                    <span class="font-bold text-slate-900" id="detail-schedule-time">-</span>
                    <span class="text-[11px] text-slate-500 block" id="detail-schedule-duration">-</span>
                </div>
                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-0.5">
                    <span class="text-slate-400 text-[11px] block">Pewawancara</span>
                    <span class="font-bold text-slate-900" id="detail-interviewer">-</span>
                    <span class="text-[11px] text-slate-500 block" id="detail-tahap">Tahap 1</span>
                </div>
            </div>

            <!-- Kategori Section (Online / Offline) -->
            <div id="detail-section-online" class="hidden bg-red-50/60 border border-red-200 rounded-xl p-4 space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white">Online</span>
                        <span class="font-bold text-red-950 text-xs">Google Meet / Virtual Room</span>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">Tautan Aktif</span>
                </div>
                <div class="p-2.5 bg-white rounded-lg border border-red-100 font-mono text-[11px] text-red-700 select-all break-all" id="detail-meeting-link">
                    -
                </div>
                <div class="flex gap-2 pt-1">
                    <a id="btn-detail-join-meeting" href="#" target="_blank" class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs text-center transition shadow-xs">
                        Gabung Rapat
                    </a>
                    <button onclick="copyMeetingLink()" class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-lg text-xs transition">
                        Salin Link
                    </button>
                </div>
            </div>

            <div id="detail-section-offline" class="hidden bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-white">Offline</span>
                    <span class="font-bold text-slate-900 text-xs">Tatap Muka di Kantor</span>
                </div>
                <span class="text-slate-400 text-[11px] block font-semibold">Alamat & Ruangan:</span>
                <p class="text-xs text-slate-700 leading-relaxed font-medium" id="detail-location-text">-</p>
                <p class="text-[11px] text-slate-400 pt-1">Kandidat dan pewawancara hadir langsung di lokasi di atas.</p>
            </div>

            <!-- Portal Token Link Section -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-slate-500 font-medium">Tautan Portal Respon Kandidat:</span>
                    <span class="text-slate-400 font-mono" id="detail-token-used-status">Terkonfirmasi</span>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200 font-mono text-[10px] text-slate-600 select-all break-all" id="detail-portal-link-url">
                    -
                </div>
                <div class="flex gap-2">
                    <button onclick="copyDetailPortalLink()" class="flex-1 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">
                        Salin Tautan Portal
                    </button>
                    <a id="btn-detail-open-portal" href="#" target="_blank" class="py-1.5 px-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold rounded-lg text-xs transition">
                        Buka Portal
                    </a>
                </div>
            </div>

            <!-- Action Pengingat Wawancara (Untuk Kandidat Terkonfirmasi) -->
            <div id="detail-reminder-section" class="hidden p-4 bg-red-50/60 border border-red-200 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 text-xs">Pengingat Wawancara</span>
                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded">Terkonfirmasi</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    Kandidat telah menyetujui jadwal. Kirimkan pengingat berisi tautan Google Meet (online) atau lokasi kantor (offline), petunjuk persiapan/dokumen yang wajib dibawa, dan ketentuan pakaian resmi.
                </p>
                <div class="pt-1">
                    <button onclick="openReminderModalFromDetail()" class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-2">
                        Kirim Pengingat Wawancara
                    </button>
                </div>
            </div>

            <div class="pt-1 flex justify-end">
                <button onclick="closeModal('modal-candidate-detail')" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: KIRIM PENGINGAT WAWANCARA (HR ACTION) -->
<!-- ========================================== -->
<div id="modal-reminder" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in max-h-[92vh] flex flex-col">
        <!-- Header -->
        <div class="px-5 py-3.5 bg-red-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/nkri-icon.png') }}" class="w-5 h-5 bg-white rounded-full p-0.5 object-contain" alt="Logo">
                <h3 class="font-bold text-xs tracking-wide">Kirim Pengingat Wawancara</h3>
            </div>
            <button onclick="closeModal('modal-reminder')" class="text-white hover:text-red-100 text-base font-bold">&times;</button>
        </div>

        <div class="p-5 space-y-4 text-xs overflow-y-auto flex-1">
            <input type="hidden" id="reminder-interview-id">

            <!-- Candidate Summary -->
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex justify-between items-center">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm" id="reminder-cand-name">-</h4>
                    <p class="text-slate-500 text-xs" id="reminder-cand-posisi">-</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-900 text-white" id="reminder-kategori-badge">Online</span>
            </div>

            <!-- Schedule info box -->
            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500">Jadwal:</span>
                    <strong class="text-slate-900" id="reminder-schedule-time">-</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pewawancara:</span>
                    <strong class="text-slate-900" id="reminder-interviewer">-</strong>
                </div>
                <div class="flex justify-between items-start" id="reminder-venue-row">
                    <span class="text-slate-500" id="reminder-venue-label">Media / Lokasi:</span>
                    <span class="text-slate-900 font-medium text-right text-[11px] max-w-[240px] break-words" id="reminder-venue-val">-</span>
                </div>
            </div>

            <!-- Online Checklist Section (Muncul jika kategori Online) -->
            <div id="reminder-fields-online" class="space-y-3">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Link Google Meet</label>
                    <input type="url" id="reminder-link-input" oninput="updateReminderPreviews()" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-[11px] text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-800 mb-1">Instruksi Kesiapan Wawancara Online</label>
                    <textarea id="reminder-online-prep" oninput="updateReminderPreviews()" rows="4" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs leading-relaxed focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">1. Pastikan koneksi internet stabil & ruangan tenang/bebas gangguan.
2. Siapkan laptop/PC dengan webcam dan mikrofon aktif.
3. Siapkan file CV dan portofolio digital yang siap dipresentasikan (screen share).
4. Bergabung ke link Google Meet 5-10 menit sebelum waktu wawancara dimulai.</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-800 mb-1">Ketentuan Pakaian (Dresscode)</label>
                    <input type="text" id="reminder-online-dress" oninput="updateReminderPreviews()" value="Berpakaian kemeja formal / rapi dan sopan (standar wawancara kerja profesional)." class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
            </div>

            <!-- Offline Checklist Section (Muncul jika kategori Offline) -->
            <div id="reminder-fields-offline" class="hidden space-y-3">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Lokasi Pertemuan & Ruangan Kantor</label>
                    <textarea id="reminder-offline-location" oninput="updateReminderPreviews()" rows="2" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-800 mb-1">Dokumen & Perlengkapan yang Wajib Dibawa</label>
                    <textarea id="reminder-offline-prep" oninput="updateReminderPreviews()" rows="4" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs leading-relaxed focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">1. Berkas cetak CV & Portofolio terbaru (hardcopy rangkap 2).
2. Kartu Tanda Pengenal asli (KTP / SIM) untuk penukaran visitor pass di resepsionis lobi.
3. Alat tulis pribadi (pulpen hitam) dan laptop jika ada pengujian teknis.
4. Hadir di lokasi kantor 15 menit sebelum waktu wawancara untuk proses registrasi tamu.</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-800 mb-1">Ketentuan Pakaian (Dresscode)</label>
                    <input type="text" id="reminder-offline-dress" oninput="updateReminderPreviews()" value="Pakaian kerja formal: kemeja berkerah rapi, celana bahan panjang (non-jeans), dan sepatu tertutup pantofel." class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
            </div>

            <!-- Pesan Tambahan Opsional -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" id="reminder-extra-notes" oninput="updateReminderPreviews()" placeholder="Contoh: Konfirmasikan jika memerlukan petunjuk arah lokasi." class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            <!-- Preview Tab Toggle (Email vs WhatsApp) -->
            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800 text-[11px]">Preview Pesan Siap Kirim:</span>
                    <div class="flex gap-1 text-[10px] font-semibold">
                        <button type="button" onclick="switchReminderPreview('email')" id="btn-prev-email" class="px-2.5 py-1 rounded bg-slate-900 text-white">Email</button>
                        <button type="button" onclick="switchReminderPreview('wa')" id="btn-prev-wa" class="px-2.5 py-1 rounded bg-white text-slate-600 border border-slate-200 hover:bg-slate-100">WhatsApp</button>
                    </div>
                </div>

                <div id="prev-box-email" class="p-3 bg-white rounded-lg border border-slate-200 text-[11px] text-slate-700 leading-relaxed max-h-36 overflow-y-auto whitespace-pre-wrap select-all font-sans">
                    -
                </div>

                <div id="prev-box-wa" class="hidden p-3 bg-emerald-50/40 rounded-lg border border-emerald-200 text-[11px] text-slate-800 leading-relaxed max-h-36 overflow-y-auto whitespace-pre-wrap select-all font-mono">
                    -
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs font-bold">
            <button onclick="closeModal('modal-reminder')" class="px-3 py-1.5 text-slate-500 hover:text-slate-800">
                Batal
            </button>
            <div class="flex items-center gap-2">
                <button onclick="copyReminderWhatsApp()" class="px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl transition">
                    Salin Teks WA
                </button>
                <button onclick="openWhatsAppDirect()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-2xs">
                    Kirim via WhatsApp
                </button>
                <button onclick="submitSendEmailReminder()" id="btn-submit-reminder" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition shadow-xs">
                    Kirim Email Pengingat
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: LINK PORTAL KANDIDAT & DETAIL -->
<!-- ========================================== -->
<div id="modal-portal-link" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-xs tracking-wide">Tautan Portal Respon Kandidat</h3>
            <button onclick="closeModal('modal-portal-link')" class="text-slate-400 hover:text-slate-600 text-base font-bold">&times;</button>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <p class="text-slate-600 leading-relaxed">
                Tautan ini dikirimkan ke email kandidat secara otomatis dan memuat token rahasia untuk konfirmasi kehadiran:
            </p>

            <div class="p-3 bg-red-50/50 rounded-xl border border-red-200 font-mono text-[11px] text-red-900 break-all select-all" id="portal-link-url">
                -
            </div>

            <div class="flex gap-2 pt-2">
                <button onclick="copyPortalLink()" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow-xs">
                    Salin Tautan
                </button>
                <a id="btn-open-portal-tab" href="#" target="_blank" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs text-center transition">
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
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 2);
        document.getElementById('sched-date').value = tomorrow.toISOString().split('T')[0];
        
        loadDashboardData();
    });

    async function loadDashboardData() {
        try {
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
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">Menunggu</span>';
            } else if (c.status === 'Confirmed') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Terkonfirmasi</span>';
            } else if (c.status === 'Reschedule Requested') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800 border border-red-300">Reschedule (1x)</span>';
            } else if (c.status === 'Declined') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Menolak</span>';
            }

            // Format schedule cell
            let scheduleText = '<span class="text-slate-400 italic">Belum dijadwalkan</span>';
            if (latestInterview) {
                const dateObj = new Date(latestInterview.tanggal_waktu);
                const formattedDate = dateObj.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                
                const kategoriBadge = latestInterview.kategori === 'online' 
                    ? '<span class="text-[10px] font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded ml-1">Online</span>'
                    : '<span class="text-[10px] font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded ml-1">Offline</span>';

                if (c.status === 'Reschedule Requested' && reschedReq) {
                    scheduleText = `
                        <span class="text-slate-400 line-through block text-[11px]">${formattedDate}</span>
                        <span class="text-[11px] text-red-700 font-bold block">Minta: ${reschedReq.saran_waktu || 'Jadwal lain'}</span>
                    `;
                } else {
                    scheduleText = `
                        <span class="font-semibold text-slate-800 block">${formattedDate}</span>
                        <div class="mt-0.5">${kategoriBadge}</div>
                    `;
                }
            }

            // Action button (strictly 1 button per row)
            let actionBtn = '';
            if (c.status === 'New Application') {
                actionBtn = `<button onclick="openScheduleModal(${c.id}, '${escapeHtml(c.nama)}', '${escapeHtml(c.posisi)}')" class="px-3.5 py-1 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs transition shadow-xs">Jadwalkan</button>`;
            } else if (c.status === 'Reschedule Requested') {
                actionBtn = `<button onclick="openRescheduleModal(${latestInterview?.id}, '${escapeHtml(c.nama)}', '${escapeHtml(c.posisi)}', '${escapeHtml(reschedReq?.alasan || '-')}', '${escapeHtml(reschedReq?.saran_waktu || '-')}')" class="px-3.5 py-1 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs transition shadow-xs">Tinjau</button>`;
            } else if (c.status === 'Confirmed') {
                actionBtn = `<button onclick="openCandidateDetailModal(${c.id})" class="px-3.5 py-1 bg-white hover:bg-red-50 hover:text-red-700 hover:border-red-200 border border-slate-200 text-slate-800 font-bold rounded-lg text-xs transition shadow-2xs">Detail</button>`;
            } else if (latestInterview) {
                actionBtn = `<button onclick="openCandidateDetailModal(${c.id})" class="px-3.5 py-1 bg-white hover:bg-red-50 hover:text-red-700 hover:border-red-200 border border-slate-200 text-slate-700 font-bold rounded-lg text-xs transition shadow-2xs">Detail</button>`;
            } else {
                actionBtn = `<span class="text-slate-300">-</span>`;
            }

            return `
                <tr class="hover:bg-red-50/20 transition">
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
        
        const tabs = ['all', 'reschedule', 'invited', 'confirmed', 'new'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (btn) {
                if (t === status) {
                    btn.className = 'px-3 py-1.5 rounded-lg bg-red-600 text-white shadow-xs';
                } else {
                    btn.className = 'px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700';
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

    let currentDetailCandidate = null;
    let reminderCandidate = null;

    function openCandidateDetailModal(candId) {
        const c = candidatesData.find(item => item.id == candId);
        if (!c) return;

        currentDetailCandidate = c;
        const interview = c.latest_interview;
        const token = interview?.token?.token;

        document.getElementById('detail-cand-name').innerText = c.nama;
        document.getElementById('detail-cand-role').innerText = c.posisi;
        document.getElementById('detail-cand-email').innerText = c.email;
        document.getElementById('detail-cand-phone').innerText = c.telepon || '-';

        // Status badge
        let statusBadgeHtml = '';
        if (c.status === 'Confirmed') {
            statusBadgeHtml = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Terkonfirmasi</span>';
            document.getElementById('detail-reminder-section').classList.remove('hidden');
        } else if (c.status === 'Invited') {
            statusBadgeHtml = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">Menunggu</span>';
            document.getElementById('detail-reminder-section').classList.add('hidden');
        } else if (c.status === 'Reschedule Requested') {
            statusBadgeHtml = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300">Reschedule (1x)</span>';
            document.getElementById('detail-reminder-section').classList.add('hidden');
        } else if (c.status === 'Declined') {
            statusBadgeHtml = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">Menolak</span>';
            document.getElementById('detail-reminder-section').classList.add('hidden');
        } else {
            statusBadgeHtml = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">${c.status}</span>`;
            document.getElementById('detail-reminder-section').classList.add('hidden');
        }
        document.getElementById('detail-status-badge').innerHTML = statusBadgeHtml;

        // Schedule & Kategori (Online / Offline)
        if (interview) {
            const dateObj = new Date(interview.tanggal_waktu);
            const formattedDate = dateObj.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
            document.getElementById('detail-schedule-time').innerText = formattedDate;
            document.getElementById('detail-schedule-duration').innerText = `Durasi: ${interview.durasi_menit || 45} Menit`;
            document.getElementById('detail-interviewer').innerText = interview.interviewer || 'Tim HR Nanggala Kencana Rekatama Indonesia';
            document.getElementById('detail-tahap').innerText = interview.tahap || 'Tahap 1';

            const onlineSection = document.getElementById('detail-section-online');
            const offlineSection = document.getElementById('detail-section-offline');

            if (interview.kategori === 'online') {
                onlineSection.classList.remove('hidden');
                offlineSection.classList.add('hidden');

                const meetingUrl = interview.link_meeting || 'https://meet.google.com/nkri-interview';
                document.getElementById('detail-meeting-link').innerText = meetingUrl;
                document.getElementById('btn-detail-join-meeting').href = meetingUrl;
            } else {
                onlineSection.classList.add('hidden');
                offlineSection.classList.remove('hidden');

                document.getElementById('detail-location-text').innerText = interview.lokasi || 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta';
            }
        }

        // Token URL & portal access
        if (token) {
            const fullPortalUrl = `${window.location.origin}/portal/${token}`;
            document.getElementById('detail-portal-link-url').innerText = fullPortalUrl;
            document.getElementById('btn-detail-open-portal').href = fullPortalUrl;
            document.getElementById('detail-token-used-status').innerText = interview?.token?.sudah_dipakai ? 'Token Terpakai (Terkunci)' : 'Token Aktif';
        } else {
            document.getElementById('detail-portal-link-url').innerText = 'Belum dibuat token';
            document.getElementById('btn-detail-open-portal').href = '#';
            document.getElementById('detail-token-used-status').innerText = '-';
        }

        document.getElementById('modal-candidate-detail').classList.remove('hidden');
    }

    function openReminderModalFromDetail() {
        if (!currentDetailCandidate) return;
        closeModal('modal-candidate-detail');
        openReminderModal(currentDetailCandidate.id);
    }

    function openReminderModal(candId) {
        const c = candidatesData.find(item => item.id == candId);
        if (!c) return;

        reminderCandidate = c;
        const interview = c.latest_interview;
        if (!interview) return;

        document.getElementById('reminder-interview-id').value = interview.id;
        document.getElementById('reminder-cand-name').innerText = c.nama;
        document.getElementById('reminder-cand-posisi').innerText = c.posisi;

        const isOnline = interview.kategori === 'online';
        document.getElementById('reminder-kategori-badge').innerText = isOnline ? 'Online (Virtual)' : 'Offline (Tatap Muka)';
        document.getElementById('reminder-kategori-badge').className = isOnline ? 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-900 text-white' : 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-800';

        const dateObj = new Date(interview.tanggal_waktu);
        const formattedDate = dateObj.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
        document.getElementById('reminder-schedule-time').innerText = formattedDate;
        document.getElementById('reminder-interviewer').innerText = interview.interviewer || 'Tim HR Nanggala Kencana Rekatama Indonesia';

        if (isOnline) {
            document.getElementById('reminder-venue-label').innerText = 'Google Meet:';
            document.getElementById('reminder-venue-val').innerText = interview.link_meeting || 'https://meet.google.com/nkri-interview';
            document.getElementById('reminder-fields-online').classList.remove('hidden');
            document.getElementById('reminder-fields-offline').classList.add('hidden');
            document.getElementById('reminder-link-input').value = interview.link_meeting || 'https://meet.google.com/nkri-interview';
        } else {
            document.getElementById('reminder-venue-label').innerText = 'Lokasi:';
            document.getElementById('reminder-venue-val').innerText = interview.lokasi || 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta';
            document.getElementById('reminder-fields-online').classList.add('hidden');
            document.getElementById('reminder-fields-offline').classList.remove('hidden');
            document.getElementById('reminder-offline-location').value = interview.lokasi || 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta';
        }

        updateReminderPreviews();
        document.getElementById('modal-reminder').classList.remove('hidden');
    }

    function updateReminderPreviews() {
        if (!reminderCandidate) return;
        const c = reminderCandidate;
        const interview = c.latest_interview;
        const isOnline = interview?.kategori === 'online';

        const dateText = document.getElementById('reminder-schedule-time').innerText;
        const interviewer = document.getElementById('reminder-interviewer').innerText;
        const extraNotes = document.getElementById('reminder-extra-notes').value.trim();

        let prepText = '';
        let dressText = '';
        let venueText = '';

        if (isOnline) {
            venueText = 'Google Meet: ' + (document.getElementById('reminder-link-input').value || 'https://meet.google.com/nkri-interview');
            prepText = document.getElementById('reminder-online-prep').value;
            dressText = document.getElementById('reminder-online-dress').value;
        } else {
            venueText = 'Lokasi Kantor: ' + (document.getElementById('reminder-offline-location').value || 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta');
            prepText = document.getElementById('reminder-offline-prep').value;
            dressText = document.getElementById('reminder-offline-dress').value;
        }

        // Email Preview Content
        const emailContent = `Subjek: [PENGINGAT RESMI] Jadwal Wawancara Kerja: ${c.posisi} - ${c.nama} (Nanggala Kencana Rekatama Indonesia)

Halo ${c.nama},

Kami mengingatkan jadwal sesi wawancara kerja Anda di Nanggala Kencana Rekatama Indonesia yang telah terkonfirmasi:

• Posisi: ${c.posisi}
• Waktu: ${dateText}
• Pewawancara: ${interviewer}
• ${venueText}

INSTRUKSI & PERSIAPAN:
${prepText}

KETENTUAN PAKAIAN:
${dressText}
${extraNotes ? '\nCATATAN TAMBAHAN:\n' + extraNotes : ''}

Salam hangat,
Tim HR Nanggala Kencana Rekatama Indonesia`;

        document.getElementById('prev-box-email').innerText = emailContent;

        // WhatsApp Preview Content
        const waContent = `Halo *${c.nama}*,

Kami dari Tim HR *Nanggala Kencana Rekatama Indonesia* mengingatkan jadwal wawancara kerja Anda yang telah terkonfirmasi:

📋 *Posisi:* ${c.posisi}
📅 *Waktu:* ${dateText}
👤 *Pewawancara:* ${interviewer}
${isOnline ? '💻 *Tautan Google Meet:* ' + (document.getElementById('reminder-link-input').value || 'https://meet.google.com/nkri-interview') : '🏢 *Lokasi Kantor:* ' + (document.getElementById('reminder-offline-location').value || 'Gedung Kantor Nanggala Kencana Rekatama Indonesia, Jakarta')}

📌 *Petunjuk Persiapan:*
${prepText}

👔 *Ketentuan Pakaian (Dresscode):*
${dressText}
${extraNotes ? '\n⚠️ *Catatan:* ' + extraNotes : ''}

Mohon hadir tepat waktu. Terima kasih dan sukses untuk sesi wawancara Anda!`;

        document.getElementById('prev-box-wa').innerText = waContent;
    }

    function switchReminderPreview(type) {
        const btnEmail = document.getElementById('btn-prev-email');
        const btnWa = document.getElementById('btn-prev-wa');
        const boxEmail = document.getElementById('prev-box-email');
        const boxWa = document.getElementById('prev-box-wa');

        if (type === 'email') {
            btnEmail.className = 'px-2.5 py-1 rounded bg-slate-900 text-white';
            btnWa.className = 'px-2.5 py-1 rounded bg-white text-slate-600 border border-slate-200 hover:bg-slate-100';
            boxEmail.classList.remove('hidden');
            boxWa.classList.add('hidden');
        } else {
            btnWa.className = 'px-2.5 py-1 rounded bg-emerald-700 text-white';
            btnEmail.className = 'px-2.5 py-1 rounded bg-white text-slate-600 border border-slate-200 hover:bg-slate-100';
            boxEmail.classList.add('hidden');
            boxWa.classList.remove('hidden');
        }
    }

    function copyReminderWhatsApp() {
        const text = document.getElementById('prev-box-wa').innerText;
        navigator.clipboard.writeText(text).then(() => {
            showToast('Teks pesan WhatsApp berhasil disalin ke clipboard!');
        });
    }

    function openWhatsAppDirect() {
        if (!reminderCandidate) return;
        let phone = (reminderCandidate.telepon || '').replace(/[^0-9]/g, '');
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }
        const text = document.getElementById('prev-box-wa').innerText;
        const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
        window.open(waUrl, '_blank');
        showToast('Membuka WhatsApp Web...');
    }

    async function submitSendEmailReminder() {
        if (!reminderCandidate) return;
        const interviewId = document.getElementById('reminder-interview-id').value;
        const submitBtn = document.getElementById('btn-submit-reminder');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Mengirim...';

        try {
            const isOnline = reminderCandidate.latest_interview?.kategori === 'online';
            const res = await fetch(`/api/interviews/${interviewId}/reminder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    channel: 'email',
                    catatan_persiapan: isOnline ? document.getElementById('reminder-online-prep')?.value : document.getElementById('reminder-offline-prep')?.value,
                    ketentuan_pakaian: isOnline ? document.getElementById('reminder-online-dress')?.value : document.getElementById('reminder-offline-dress')?.value,
                    pesan_tambahan: document.getElementById('reminder-extra-notes')?.value,
                })
            });

            const data = await res.json();
            if (data.success) {
                closeModal('modal-reminder');
                showToast(`Email pengingat berhasil dikirim ke ${reminderCandidate.nama}!`);
                loadDashboardData();
            } else {
                showToast(data.message || 'Gagal mengirim pengingat', 'error');
            }
        } catch (error) {
            console.error('Error sending reminder:', error);
            showToast('Terjadi kesalahan saat mengirim pengingat', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Kirim Email Pengingat';
        }
    }

    function copyMeetingLink() {
        const link = document.getElementById('detail-meeting-link').innerText;
        navigator.clipboard.writeText(link).then(() => {
            showToast('Tautan Google Meet berhasil disalin ke clipboard!');
        });
    }

    function copyDetailPortalLink() {
        const link = document.getElementById('detail-portal-link-url').innerText;
        navigator.clipboard.writeText(link).then(() => {
            showToast('Tautan portal pelamar berhasil disalin ke clipboard!');
        });
    }

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
