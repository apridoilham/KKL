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
</head>
<body class="bg-slate-100 font-sans text-slate-600">
    <div x-data="{ sidebarOpen: window.innerWidth > 1024 }" class="flex h-screen bg-slate-100">

        @include('components.partials.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.partials.topbar')

            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                {{ $slot }}
            </main>

            @include('components.partials.footer')
        </div>
    </div>

    @livewireScripts
    @stack('scripts')

    <div
        x-data="{
            show: false,
            message: '',
            status: 'success',
            timer: null,
            pop(event) {
                this.show = true;
                this.message = event.detail.message;
                this.status = event.detail.status;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.show = false }, 5000);
            }
        }"
        x-on:toast.window="pop($event)"
        x-show="show"
        x-transition:enter-start="opacity-0 translate-x-12"
        x-transition:enter="transition ease-out duration-300"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-end="opacity-0 translate-x-12"
        @click.away="show = false"
        class="fixed bottom-5 right-5 w-full max-w-xs z-50"
        x-cloak
    >
        <div class="p-4 rounded-lg shadow-lg border bg-white" :class="{ 'border-green-500': status === 'success', 'border-red-500': status === 'failed' }">
            <div class="flex items-center">
                <div class="mr-3">
                    <i x-show="status === 'success'" class="fas fa-check-circle fa-lg text-green-500"></i>
                    <i x-show="status === 'failed'" class="fas fa-times-circle fa-lg text-red-500"></i>
                </div>
                <div x-text="message" class="font-medium text-slate-800"></div>
                <button @click="show = false" class="ml-auto -mx-1.5 -my-1.5 p-1.5 rounded-full hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    @if (session()->has('status'))
        <div
            x-data="{
                show: true,
                timer: null,
                init() {
                    this.timer = setTimeout(() => { this.show = false }, 5000);
                }
            }"
            x-show="show"
            x-transition:enter-start="opacity-0 translate-x-12"
            x-transition:enter="transition ease-out duration-300"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-end="opacity-0 translate-x-12"
            @click.away="show = false"
            class="fixed bottom-5 right-5 w-full max-w-xs z-50"
            x-cloak
        >
            <div class="p-4 rounded-lg shadow-lg border bg-white border-green-500">
                <div class="flex items-center">
                    <div class="mr-3">
                        <i class="fas fa-check-circle fa-lg text-green-500"></i>
                    </div>
                    <div class="font-medium text-slate-800">{{ session('status') }}</div>
                    <button @click="show = false" class="ml-auto -mx-1.5 -my-1.5 p-1.5 rounded-full hover:bg-slate-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif
</body>
</html>