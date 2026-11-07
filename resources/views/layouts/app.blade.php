<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ledger')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
    <header class="bg-stone-950 text-stone-50 dark:bg-stone-900">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('balances.index') }}" class="text-lg font-semibold tracking-tight">Ledger</a>
            <div class="flex items-center gap-4 text-sm">
                <span class="max-w-48 truncate text-stone-300">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-200 underline-offset-4 hover:underline">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto flex max-w-5xl flex-col gap-8 px-4 py-8">
        @if (session('status'))
            <p role="status" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">{{ session('status') }}</p>
        @endif

        @yield('content')
    </main>
</body>
</html>
