@extends('layouts.guest')

@section('title', 'Log in — Ledger')
@section('heading', 'Log in')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf

        <div class="flex flex-col gap-1">
            <label for="email" class="text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @if ($errors->has('email')) aria-describedby="email-error" @endif
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
            @include('partials.field-error', ['name' => 'email'])
        </div>

        <div class="flex flex-col gap-1">
            <label for="password" class="text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                @if ($errors->has('password')) aria-describedby="password-error" @endif
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
            @include('partials.field-error', ['name' => 'password'])
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-emerald-800 focus:ring-emerald-700 dark:border-stone-600 dark:bg-stone-950">
            Remember me
        </label>

        <button type="submit" class="rounded-md bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900 dark:bg-emerald-600 dark:hover:bg-emerald-500">Log in</button>
    </form>

    <p class="text-sm text-stone-600 dark:text-stone-400">
        No account yet?
        <a href="{{ route('register') }}" class="font-medium text-emerald-800 underline-offset-4 hover:underline dark:text-emerald-400">Create one</a>
    </p>
@endsection
