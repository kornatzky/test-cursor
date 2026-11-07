@extends('layouts.guest')

@section('title', 'Create account — Ledger')
@section('heading', 'Create an account')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
        @csrf

        <div class="flex flex-col gap-1">
            <label for="name" class="text-sm font-medium">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                @if ($errors->has('name')) aria-describedby="name-error" @endif
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
            @include('partials.field-error', ['name' => 'name'])
        </div>

        <div class="flex flex-col gap-1">
            <label for="email" class="text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                @if ($errors->has('email')) aria-describedby="email-error" @endif
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
            @include('partials.field-error', ['name' => 'email'])
        </div>

        <div class="flex flex-col gap-1">
            <label for="password" class="text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                @if ($errors->has('password')) aria-describedby="password-error" @endif
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
            @include('partials.field-error', ['name' => 'password'])
        </div>

        <div class="flex flex-col gap-1">
            <label for="password_confirmation" class="text-sm font-medium">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
        </div>

        <button type="submit" class="rounded-md bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900 dark:bg-emerald-600 dark:hover:bg-emerald-500">Create account</button>
    </form>

    <p class="text-sm text-stone-600 dark:text-stone-400">
        Already registered?
        <a href="{{ route('login') }}" class="font-medium text-emerald-800 underline-offset-4 hover:underline dark:text-emerald-400">Log in</a>
    </p>
@endsection
