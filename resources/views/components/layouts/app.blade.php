<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['title'] ?? 'Sistem Inventaris' }}</title>

    <link rel="icon" href="{{asset('assets/img/box.png')}}" type="image/svg+xml">
    
    <link href="{{asset('assets/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 font-sans">
    <div x-data="{ sidebarOpen: window.innerWidth > 768 }" class="flex h-screen bg-slate-100">
        
        @include('components.partials.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.partials.topbar')
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-100">
                {{ $slot }}
            </main>

            @include('components.partials.footer')
        </div>
    </div>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>