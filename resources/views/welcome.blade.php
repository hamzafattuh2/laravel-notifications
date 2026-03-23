<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Notifications</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-cyan-50 to-white text-slate-900">
    <nav class="border-b border-slate-200/80 bg-white/80 backdrop-blur-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div>
                <p class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Laravel') }}</p>
                <p class="text-xs text-slate-500">Facebook-style notification center</p>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <span class="hidden text-sm text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                @endauth

                <x-notification-bell />
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-sm">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Notification System Ready</h1>
            <p class="mt-3 max-w-2xl text-sm text-slate-600">
                Your backend API, queued database notifications, and dynamic dropdown bell are now wired.
                Sign in and dispatch notifications using <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">App\Services\NotificationService</code>.
            </p>
            @if (session('status'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mt-6 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                <div class="rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3">
                    <p class="font-semibold">Backend APIs</p>
                    <p class="mt-1 text-xs text-slate-600">/notifications, /mark-as-read/{id}, /mark-all-as-read, /unread-count</p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <p class="font-semibold">UI Features</p>
                    <p class="mt-1 text-xs text-slate-600">Unread badge, mark read/all, pagination, optional Echo real-time updates</p>
                </div>
            </div>

            @if (app()->environment('local'))
                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-800">Local Testing Tools</p>
                    @guest
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('dev.login') }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                Quick login as dev@example.com
                            </a>
                        </div>
                    @endguest
                    @auth
                        <div class="mt-3 flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('dev.send-notification') }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-cyan-700 px-3 py-2 text-xs font-semibold text-red transition hover:bg-cyan-600">
                                    Send test notification
                                </button>
                            </form>
                            <form method="POST" action="{{ route('dev.logout') }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-blue   transition hover:bg-slate-600">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            @endif
        </div>
    </main>
</body>
</html>
