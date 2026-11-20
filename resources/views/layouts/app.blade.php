<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RecruitFlow - Sistem Konfirmasi Wawancara')</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for standalone & fast rendering) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#EEF2FF',
                            100: '#E0E7FF',
                            500: '#6366F1',
                            600: '#4F46E5',
                            700: '#4338CA',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .tab-nav-active {
            background-color: #4F46E5;
            color: #FFFFFF;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
        }
        .tab-nav-inactive {
            color: #64748B;
            background-color: transparent;
        }
        .tab-nav-inactive:hover {
            background-color: #F1F5F9;
            color: #0F172A;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 px-4 lg:px-8 py-3">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shadow-xs">RF</span>
                    <div>
                        <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-tight">RecruitFlow</h1>
                        <p class="text-[11px] text-slate-400">Konfirmasi Jadwal Wawancara</p>
                    </div>
                </a>
            </div>

            <!-- Global View Switcher -->
            <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold">
                <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') || request()->is('/') ? 'tab-nav-active' : 'tab-nav-inactive' }} px-3 py-1.5 rounded-lg transition">
                    Dashboard HR
                </a>
                <a href="{{ url('/portal/tok_demo_ahmad_fauzi') }}" class="{{ request()->is('portal*') ? 'tab-nav-active' : 'tab-nav-inactive' }} px-3 py-1.5 rounded-lg transition">
                    Portal Pelamar (Demo)
                </a>
                <a href="{{ url('/email-preview') }}" class="{{ request()->is('email-preview*') ? 'tab-nav-active' : 'tab-nav-inactive' }} px-3 py-1.5 rounded-lg transition">
                    Email Undangan
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Global Toast / Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <script>
        // Helper to show concise UI toasts
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let bgClass = 'bg-slate-900 text-white';
            if (type === 'success') bgClass = 'bg-emerald-600 text-white';
            if (type === 'error') bgClass = 'bg-rose-600 text-white';
            if (type === 'warning') bgClass = 'bg-amber-600 text-white';

            toast.className = `${bgClass} px-4 py-2.5 rounded-xl shadow-lg text-xs font-bold transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto flex items-center justify-between gap-3`;
            toast.innerHTML = `<span>${message}</span><button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">&times;</button>`;

            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
    </script>
    @stack('scripts')
</body>
</html>
