@extends('layouts.app')

@section('title', 'Panel Super Admin - Nanggala Kencana Rekatama Indonesia')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-red-600 text-white tracking-wider uppercase">Super Admin</span>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Pengaturan Default & Master Sistem</h2>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Kelola konfigurasi default jadwal, master template pesan email, dan audit log aktivitas.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/dashboard') }}" class="px-3.5 py-1.5 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-700 hover:border-red-200 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs">
                &larr; Buka Panel HR
            </a>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                Sistem Aktif
            </span>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs font-bold pb-2 overflow-x-auto">
        <button id="admin-tab-btn-settings" onclick="switchAdminTab('settings')" class="px-4 py-2 rounded-xl bg-red-600 text-white shadow-xs transition">
            1. Parameter Default Jadwal
        </button>
        <button id="admin-tab-btn-templates" onclick="switchAdminTab('templates')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700 transition">
            2. Master Template Email
        </button>
        <button id="admin-tab-btn-logs" onclick="switchAdminTab('logs')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700 transition">
            3. Audit Log Sistem
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: PARAMETER DEFAULT JADWAL -->
    <!-- ======================================================== -->
    <div id="section-settings" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Konfigurasi Nilai Bawaan (Default System Parameters)</h3>
                    <p class="text-xs text-slate-500">Nilai default ini otomatis terisi di form pembuatan jadwal baru pada Panel HR.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="resetDefaultSettings()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Reset Standar
                    </button>
                    <button type="button" onclick="saveDefaultSettings()" id="btn-save-settings" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        Simpan Pengaturan
                    </button>
                </div>
            </div>

            <form id="form-admin-settings" class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                <!-- Pewawancara Default -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Nama Pewawancara Default</label>
                    <input type="text" id="setting-default_interviewer" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Contoh: Tim HR Nanggala Kencana Rekatama Indonesia">
                    <span class="text-[11px] text-slate-400">Otomatis mengisi kolom Pewawancara di form HR.</span>
                </div>

                <!-- Durasi Default -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Durasi Sesi Wawancara (Menit)</label>
                    <input type="number" id="setting-default_duration" min="15" max="180" step="5" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="45">
                    <span class="text-[11px] text-slate-400">Durasi estimasi per sesi wawancara.</span>
                </div>

                <!-- Google Meet Link Prefix -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Tautan Google Meet Default (Online)</label>
                    <input type="url" id="setting-default_meet_prefix" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="https://meet.google.com/nkri-interview">
                    <span class="text-[11px] text-slate-400">Prefix link Google Meet otomatis untuk wawancara online.</span>
                </div>

                <!-- Lokasi Offline Default -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Alamat Kantor & Ruangan Default (Offline)</label>
                    <input type="text" id="setting-default_location" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Gedung Menara Rekatama Lt. 8, Jl. Jend. Sudirman Kav. 50, Jakarta">
                    <span class="text-[11px] text-slate-400">Lokasi tatap muka default jika kategori wawancara offline dipilih.</span>
                </div>

                <!-- Batas Respon Default -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Batas Waktu Respon Konfirmasi (Jam sebelum Jadwal)</label>
                    <input type="number" id="setting-default_deadline_hours" min="1" max="168" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="24">
                    <span class="text-[11px] text-slate-400">Deadline kandidat untuk submit konfirmasi atau ajukan reschedule.</span>
                </div>

                <!-- Kuota Maksimal Reschedule -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Kuota Maksimal Reschedule per Kandidat (Aturan BR-06)</label>
                    <input type="number" id="setting-default_max_reschedule" min="1" max="3" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="1">
                    <span class="text-[11px] text-slate-400">Sesuai PRD, kandidat dibatasi maksimal 1 kali permintaan reschedule.</span>
                </div>

                <!-- Nama Perusahaan -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Nama Resmi Perusahaan (Branding)</label>
                    <input type="text" id="setting-company_name" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Nanggala Kencana Rekatama Indonesia">
                    <span class="text-[11px] text-slate-400">Digunakan pada header email, notifikasi, dan portal.</span>
                </div>

                <!-- Email Resmi HR -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-800">Email Resmi Rekrutmen / HR</label>
                    <input type="email" id="setting-company_email" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="hr@nkri.co.id">
                    <span class="text-[11px] text-slate-400">Alamat kontak pengirim resmi surat undangan.</span>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: MASTER TEMPLATE PESAN EMAIL -->
    <!-- ======================================================== -->
    <div id="section-templates" class="hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Master Template Pesan Email (PRD FR-19 s/d FR-24)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Edit susunan draft email resmi yang dipakai otomatis oleh sistem dan tim HR.</p>
            </div>

            <!-- Template Type Selector Pills (PRD v1.1 FR-20 s/d FR-23) -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs overflow-x-auto">
                <button type="button" id="tab-tmpl-invitation" onclick="selectAdminTemplate('invitation')" class="flex-1 min-w-[140px] py-2 px-3 rounded-lg bg-red-600 text-white font-bold transition">
                    1. Undangan Awal
                </button>
                <button type="button" id="tab-tmpl-reminder_online" onclick="selectAdminTemplate('reminder_online')" class="flex-1 min-w-[170px] py-2 px-3 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                    2. Pengingat Hari H Online
                </button>
                <button type="button" id="tab-tmpl-reminder_offline" onclick="selectAdminTemplate('reminder_offline')" class="flex-1 min-w-[170px] py-2 px-3 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                    3. Pengingat Hari H Offline
                </button>
            </div>

            <!-- Template Variables Quick Bar -->
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1.5">
                <span class="text-[11px] font-bold text-slate-700 block">Klik Variabel Dinamis untuk Menyisipkan ke Editor:</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="insertVariable('{nama_kandidat}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{nama_kandidat}</button>
                    <button type="button" onclick="insertVariable('{posisi}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{posisi}</button>
                    <button type="button" onclick="insertVariable('{jadwal}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{jadwal}</button>
                    <button type="button" onclick="insertVariable('{durasi}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{durasi}</button>
                    <button type="button" onclick="insertVariable('{interviewer}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{interviewer}</button>
                    <button type="button" onclick="insertVariable('{link_meeting}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{link_meeting}</button>
                    <button type="button" onclick="insertVariable('{lokasi_kantor}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{lokasi_kantor}</button>
                    <button type="button" onclick="insertVariable('{deadline_konfirmasi}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{deadline_konfirmasi}</button>
                    <button type="button" onclick="insertVariable('{link_portal}')" class="px-2 py-0.5 bg-white hover:bg-red-50 hover:text-red-700 border border-slate-200 rounded text-[11px] font-mono font-semibold transition">{link_portal}</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Editor Form -->
                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Subjek Email</label>
                        <input type="text" id="admin-template-subjek" oninput="renderTemplateLivePreview()" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                    </div>

                    <div id="wrapper-ketentuan-pakaian">
                        <label class="block font-bold text-slate-800 mb-1">Ketentuan Pakaian (Dresscode)</label>
                        <input type="text" id="admin-template-pakaian" oninput="renderTemplateLivePreview()" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Pakaian kemeja bebas rapi atau pakaian formal">
                    </div>

                    <div id="wrapper-petunjuk-persiapan">
                        <label class="block font-bold text-slate-800 mb-1">Petunjuk Teknis & Kesiapan Kandidat</label>
                        <textarea id="admin-template-petunjuk" oninput="renderTemplateLivePreview()" rows="3" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs leading-relaxed text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Hal-hal yang harus disiapkan..."></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Format Pesan Lengkap</label>
                        <textarea id="admin-template-isi" oninput="renderTemplateLivePreview()" rows="7" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono leading-relaxed text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="resetCurrentTemplate()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                            Reset Template Ini
                        </button>
                        <button type="button" onclick="saveCurrentTemplate()" id="btn-save-template" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-xs transition">
                            Simpan Master Template
                        </button>
                    </div>
                </div>

                <!-- Live Render Preview -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-800 block">Pratinjau Hasil Render (Live Preview)</span>
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 space-y-3 text-xs">
                        <div class="border-b border-slate-200 pb-2">
                            <span class="text-slate-400 text-[11px] block">Subjek:</span>
                            <span class="font-bold text-slate-900 text-xs" id="preview-subjek">-</span>
                        </div>
                        <div class="border-b border-slate-200 pb-2 flex justify-between text-[11px] text-slate-500">
                            <span>Kepada: <strong>ibnhu@example.com</strong></span>
                            <span class="font-mono text-emerald-700 font-bold">Simulasi Render Data</span>
                        </div>
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-2 leading-relaxed whitespace-pre-wrap text-slate-800 font-sans" id="preview-isi-pesan">
                            -
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 3: AUDIT LOG SISTEM -->
    <!-- ======================================================== -->
    <div id="section-logs" class="hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Audit Log Sistem & Pengiriman Notifikasi</h3>
                    <p class="text-xs text-slate-500">Rekam jejak perubahan status kandidat dan pengiriman email resmi secara otomatis.</p>
                </div>
                <button type="button" onclick="loadAuditLogs()" class="px-3.5 py-1.5 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-700 hover:border-red-200 text-slate-700 font-bold text-xs rounded-xl shadow-2xs transition">
                    Perbarui Log
                </button>
            </div>

            <!-- 2 Audit Tables Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
                <!-- Status Change Logs -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900">1. Riwayat Status Pelamar (Status Logs)</span>
                        <span class="text-[11px] text-slate-400">30 Terakhir</span>
                    </div>
                    <div class="overflow-x-auto border border-slate-200 rounded-xl max-h-[460px] overflow-y-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-[11px] sticky top-0 border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3 font-semibold">Waktu</th>
                                    <th class="py-2.5 px-3 font-semibold">Kandidat</th>
                                    <th class="py-2.5 px-3 font-semibold">Perubahan Status</th>
                                    <th class="py-2.5 px-3 font-semibold">Pemicu</th>
                                </tr>
                            </thead>
                            <tbody id="audit-status-rows" class="divide-y divide-slate-100 text-[11px]">
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">Memuat riwayat status...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notification Dispatch Logs -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900">2. Riwayat Email Terkirim (Notification Logs)</span>
                        <span class="text-[11px] text-slate-400">30 Terakhir</span>
                    </div>
                    <div class="overflow-x-auto border border-slate-200 rounded-xl max-h-[460px] overflow-y-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-[11px] sticky top-0 border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3 font-semibold">Waktu</th>
                                    <th class="py-2.5 px-3 font-semibold">Penerima</th>
                                    <th class="py-2.5 px-3 font-semibold">Jenis Email</th>
                                    <th class="py-2.5 px-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody id="audit-notif-rows" class="divide-y divide-slate-100 text-[11px]">
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">Memuat riwayat pengiriman...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let adminSettings = {};
    let adminTemplates = {};
    let activeTemplateKey = 'invitation';

    document.addEventListener('DOMContentLoaded', () => {
        loadDefaultSettings();
        loadMasterTemplates();
    });

    // Tab Navigation
    function switchAdminTab(tab) {
        ['settings', 'templates', 'logs'].forEach(t => {
            const sec = document.getElementById(`section-${t}`);
            const btn = document.getElementById(`admin-tab-btn-${t}`);
            if (t === tab) {
                sec.classList.remove('hidden');
                btn.className = 'px-4 py-2 rounded-xl bg-red-600 text-white shadow-xs transition';
            } else {
                sec.classList.add('hidden');
                btn.className = 'px-4 py-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700 transition';
            }
        });

        if (tab === 'logs') {
            loadAuditLogs();
        }
    }

    // 1. Settings Handler
    async function loadDefaultSettings() {
        try {
            const res = await fetch('/api/admin/settings');
            const json = await res.json();
            if (json.success && json.data) {
                adminSettings = json.data;
                for (const key in json.data) {
                    const el = document.getElementById(`setting-${key}`);
                    if (el) el.value = json.data[key];
                }
            }
        } catch (err) {
            console.error('Error loading settings:', err);
            showToast('Gagal memuat pengaturan default', 'error');
        }
    }

    async function saveDefaultSettings() {
        const btn = document.getElementById('btn-save-settings');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const keys = [
            'default_interviewer',
            'default_duration',
            'default_meet_prefix',
            'default_location',
            'default_deadline_hours',
            'default_max_reschedule',
            'company_name',
            'company_email'
        ];

        const payload = {};
        keys.forEach(k => {
            const el = document.getElementById(`setting-${k}`);
            if (el) payload[k] = el.value;
        });

        try {
            const res = await fetch('/api/admin/settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Pengaturan default berhasil disimpan!');
                loadDefaultSettings();
            } else {
                showToast(data.message || 'Gagal menyimpan pengaturan', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan koneksi', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan Pengaturan';
        }
    }

    async function resetDefaultSettings() {
        if (!confirm('Kembalikan seluruh pengaturan default sistem ke standar awal?')) return;

        try {
            const res = await fetch('/api/admin/settings/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (data.success) {
                showToast('Pengaturan default dikembalikan ke standar awal');
                loadDefaultSettings();
            } else {
                showToast(data.message || 'Gagal mereset pengaturan', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan koneksi', 'error');
        }
    }

    // 2. Master Templates Handler
    async function loadMasterTemplates() {
        try {
            const res = await fetch('/api/templates');
            const json = await res.json();
            if (json.success && Array.isArray(json.data)) {
                json.data.forEach(t => {
                    adminTemplates[t.key] = t;
                });
                selectAdminTemplate(activeTemplateKey);
            }
        } catch (err) {
            console.error('Error loading master templates:', err);
        }
    }

    function selectAdminTemplate(key) {
        activeTemplateKey = key;

        ['invitation', 'reminder_online', 'reminder_offline'].forEach(k => {
            const btn = document.getElementById(`tab-tmpl-${k}`);
            if (btn) {
                if (k === key) {
                    btn.className = 'flex-1 min-w-[140px] py-2 px-3 rounded-lg bg-red-600 text-white font-bold transition';
                } else {
                    btn.className = 'flex-1 min-w-[140px] py-2 px-3 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition';
                }
            }
        });

        const tmpl = adminTemplates[key] || { subjek: '', isi_pesan: '', petunjuk_persiapan: '', ketentuan_pakaian: '' };
        document.getElementById('admin-template-subjek').value = tmpl.subjek || '';
        document.getElementById('admin-template-pakaian').value = tmpl.ketentuan_pakaian || '';
        document.getElementById('admin-template-petunjuk').value = tmpl.petunjuk_persiapan || '';
        document.getElementById('admin-template-isi').value = tmpl.isi_pesan || '';

        renderTemplateLivePreview();
    }

    function insertVariable(variable) {
        const textarea = document.getElementById('admin-template-isi');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + variable + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + variable.length;
        textarea.focus();
        renderTemplateLivePreview();
    }

    function renderTemplateLivePreview() {
        const subjek = document.getElementById('admin-template-subjek').value;
        const pakaian = document.getElementById('admin-template-pakaian').value;
        const petunjuk = document.getElementById('admin-template-petunjuk').value;
        const isi = document.getElementById('admin-template-isi').value;

        // Mock replacement map
        const replacements = {
            '{nama_kandidat}': 'Muhamad Ibnhu',
            '{posisi}': 'Frontend Engineer',
            '{jadwal}': 'Rabu, 25 November 2026 pukul 17.00 WIB',
            '{durasi}': '45 Menit',
            '{interviewer}': 'Tim HR Nanggala Kencana Rekatama Indonesia',
            '{link_meeting}': 'https://meet.google.com/xyz-interview-ibnhu',
            '{lokasi_kantor}': 'Gedung Menara Rekatama Lt. 8, Ruang Alpha, Jakarta',
            '{deadline_konfirmasi}': 'Selasa, 24 November 2026 pukul 17.00 WIB',
            '{link_portal}': 'http://127.0.0.1:8000/portal/tok_demo_ahmad_fauzi',
            '{ketentuan_pakaian}': pakaian,
            '{petunjuk_persiapan}': petunjuk
        };

        let renderedSubject = subjek;
        let renderedIsi = isi;

        for (const [find, replace] of Object.entries(replacements)) {
            renderedSubject = renderedSubject.split(find).join(replace);
            renderedIsi = renderedIsi.split(find).join(replace);
        }

        document.getElementById('preview-subjek').innerText = renderedSubject || '-';
        document.getElementById('preview-isi-pesan').innerText = renderedIsi || '-';
    }

    async function saveCurrentTemplate() {
        const btn = document.getElementById('btn-save-template');
        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        const payload = {
            subjek: document.getElementById('admin-template-subjek').value,
            ketentuan_pakaian: document.getElementById('admin-template-pakaian').value,
            petunjuk_persiapan: document.getElementById('admin-template-petunjuk').value,
            isi_pesan: document.getElementById('admin-template-isi').value,
        };

        try {
            const res = await fetch(`/api/templates/${activeTemplateKey}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                showToast('Master template berhasil diperbarui!');
                adminTemplates[activeTemplateKey] = data.data;
            } else {
                showToast(data.message || 'Gagal menyimpan template', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan koneksi', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Simpan Master Template';
        }
    }

    async function resetCurrentTemplate() {
        if (!confirm('Kembalikan template ini ke standar bawaan sistem?')) return;

        try {
            const res = await fetch(`/api/templates/${activeTemplateKey}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (data.success) {
                showToast('Template dikembalikan ke format standar');
                adminTemplates[activeTemplateKey] = data.data;
                selectAdminTemplate(activeTemplateKey);
            } else {
                showToast(data.message || 'Gagal mereset template', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan koneksi', 'error');
        }
    }

    // 3. Audit Logs Handler
    async function loadAuditLogs() {
        const statusTbody = document.getElementById('audit-status-rows');
        const notifTbody = document.getElementById('audit-notif-rows');

        try {
            const res = await fetch('/api/admin/logs');
            const data = await res.json();

            if (data.success) {
                // Status Logs
                if (data.status_logs && data.status_logs.length > 0) {
                    statusTbody.innerHTML = data.status_logs.map(log => {
                        const date = new Date(log.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
                        return `
                            <tr class="hover:bg-slate-50">
                                <td class="py-2 px-3 text-slate-400 font-mono">${date}</td>
                                <td class="py-2 px-3 font-semibold text-slate-900">${log.candidate?.nama || '-'}</td>
                                <td class="py-2 px-3">
                                    <span class="text-slate-400">${log.status_lama || 'Baru'}</span>
                                    <span class="text-slate-300 mx-1">&rarr;</span>
                                    <span class="font-bold text-red-700">${log.status_baru}</span>
                                </td>
                                <td class="py-2 px-3 text-slate-500">${log.pemicu || 'Sistem'}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    statusTbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-slate-400">Belum ada riwayat aktivitas.</td></tr>`;
                }

                // Notification Logs
                if (data.notification_logs && data.notification_logs.length > 0) {
                    notifTbody.innerHTML = data.notification_logs.map(log => {
                        const date = new Date(log.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
                        const cand = log.interview?.candidate;
                        return `
                            <tr class="hover:bg-slate-50">
                                <td class="py-2 px-3 text-slate-400 font-mono">${date}</td>
                                <td class="py-2 px-3 font-semibold text-slate-900">${cand?.nama || '-'}</td>
                                <td class="py-2 px-3 text-slate-700 capitalize">${(log.tipe_notifikasi || 'email').replace(/_/g, ' ')}</td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Terkirim</span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    notifTbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-slate-400">Belum ada log pengiriman email.</td></tr>`;
                }
            }
        } catch (err) {
            console.error('Error loading logs:', err);
            statusTbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-rose-500">Gagal memuat log audit.</td></tr>`;
            notifTbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-rose-500">Gagal memuat log audit.</td></tr>`;
        }
    }
</script>
@endpush
