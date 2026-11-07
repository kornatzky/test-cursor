@extends('layouts.app')

@section('title', 'Ledger')

@section('content')
    <section class="flex flex-col gap-2 border-b-4 border-double border-stone-900 pb-6 dark:border-stone-100">
        <h1 class="text-sm font-medium tracking-wide text-stone-500 uppercase dark:text-stone-400">Total balance</h1>
        <p class="font-serif text-5xl tracking-tight tabular-nums">{{ $total }}</p>
        <p class="text-sm text-stone-600 dark:text-stone-400">{{ $rateNote }}</p>
        @if ($missingRate)
            <p class="text-sm text-amber-900 dark:text-amber-200">A balance without a USD rate is excluded from the total.</p>
        @endif
    </section>

    <section class="flex flex-col gap-4 rounded-lg border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
        <div class="flex flex-col gap-1">
            <h2 class="text-lg font-semibold">Add or update a balance</h2>
            <p class="text-sm text-stone-600 dark:text-stone-400">Saving a currency you already track replaces its balance.</p>
        </div>

        <form method="POST" action="{{ route('balances.store') }}" class="grid gap-4 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_auto] sm:items-end">
            @csrf

            <div class="flex flex-col gap-1">
                <label for="currency" class="text-sm font-medium">Currency</label>
                <select id="currency" name="currency" required
                    aria-invalid="{{ $errors->has('currency') ? 'true' : 'false' }}"
                    @if ($errors->has('currency')) aria-describedby="currency-error" @endif
                    class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
                    <option value="" @selected(old('currency') === null || old('currency') === '')>Select a currency</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency['code'] }}" @selected(old('currency') === $currency['code'])>
                            {{ $currency['code'] }} — {{ $currency['name'] }}
                        </option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'currency'])
            </div>

            <div class="flex flex-col gap-1">
                <label for="amount" class="text-sm font-medium">Amount</label>
                <input id="amount" name="amount" inputmode="decimal" value="{{ old('amount') }}" required autocomplete="off"
                    aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}"
                    @if ($errors->has('amount')) aria-describedby="amount-error" @endif
                    class="rounded-md border border-stone-300 bg-white px-3 py-2 text-stone-900 tabular-nums focus:ring-2 focus:ring-emerald-700 focus:outline-none dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:focus:ring-emerald-400">
                @include('partials.field-error', ['name' => 'amount'])
            </div>

            <button type="submit" class="rounded-md bg-emerald-800 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-900 dark:bg-emerald-600 dark:hover:bg-emerald-500">Save balance</button>
        </form>
    </section>

    <section class="overflow-x-auto rounded-lg border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
        <table class="w-full min-w-[40rem] border-collapse text-sm">
            <caption class="sr-only">Balances by currency and their value in US dollars</caption>
            <thead class="border-b border-stone-200 text-left text-xs tracking-wide text-stone-500 uppercase dark:border-stone-800 dark:text-stone-400">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium">Currency</th>
                    <th scope="col" class="px-4 py-3 text-right font-medium">Balance</th>
                    <th scope="col" class="px-4 py-3 text-right font-medium">USD per unit</th>
                    <th scope="col" class="px-4 py-3 text-right font-medium">Value in USD</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-stone-100 dark:border-stone-800">
                        <th scope="row" class="px-4 py-3 text-left font-medium">
                            <span class="block">{{ $row['code'] }}</span>
                            <span class="block text-xs font-normal text-stone-500 dark:text-stone-400">{{ $row['name'] }}</span>
                        </th>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $row['amount'] }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $row['rate'] ?? 'Unavailable' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $row['usd'] ?? 'Unavailable' }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('balances.destroy', $row['id']) }}" onsubmit="return confirm('Remove the {{ $row['code'] }} balance?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-stone-600 underline-offset-4 hover:underline dark:text-stone-300">Remove {{ $row['code'] }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-stone-500 dark:text-stone-400">No balances yet.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th scope="row" colspan="3" class="px-4 py-3 text-left font-semibold">Total in USD</th>
                    <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $total }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </section>
@endsection
