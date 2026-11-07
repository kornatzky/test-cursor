<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ledger')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center gap-6 px-4 py-12">
        <div class="flex flex-col gap-2">
            <p class="text-sm font-medium tracking-wide text-stone-500 uppercase dark:text-stone-400">Ledger</p>
            <h1 class="text-3xl font-semibold tracking-tight">@yield('heading')</h1>
            <p class="text-sm text-stone-600 dark:text-stone-400">Balances in the currencies you hold, totaled in US dollars.</p>
        </div>

        <section class="flex flex-col gap-4 rounded-lg border border-stone-200 bg-white p-6 dark:border-stone-800 dark:bg-stone-900">
            @yield('content')
        </section>
    </main>
</body>
</html>
