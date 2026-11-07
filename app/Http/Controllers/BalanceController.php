<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBalanceRequest;
use App\Models\Balance;
use App\Services\UsdExchangeRates;
use App\Support\Currencies;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BalanceController extends Controller
{
    public function index(Request $request, UsdExchangeRates $rates): View
    {
        Gate::authorize('viewAny', Balance::class);

        $quote = $rates->quote();
        $balances = $request->user()
            ->balances()
            ->orderBy('currency')
            ->orderBy('id')
            ->get();

        $total = '0.00';
        $missingRate = false;
        $rows = [];

        foreach ($balances as $balance) {
            $usd = $quote->toUsd((string) $balance->amount, $balance->currency);
            $rate = $quote->rate($balance->currency);

            if ($usd === null) {
                $missingRate = true;
            } else {
                $total = bcadd($total, $usd, 2);
            }

            $rows[] = [
                'id' => $balance->id,
                'code' => $balance->currency,
                'name' => Currencies::name($balance->currency),
                'amount' => Money::format((string) $balance->amount, $balance->currency),
                'rate' => $rate === null ? null : Money::formatRate($rate),
                'usd' => $usd === null ? null : Money::format($usd, 'USD'),
            ];
        }

        return view('balances.index', [
            'rows' => $rows,
            'total' => Money::format($total, 'USD'),
            'missingRate' => $missingRate,
            'rateNote' => $quote->fallback
                ? "Saved European Central Bank rates for {$quote->date}."
                : "European Central Bank rates for {$quote->date}.",
            'currencies' => Currencies::options(),
        ]);
    }

    public function store(StoreBalanceRequest $request): RedirectResponse
    {
        $currency = $request->string('currency')->toString();
        $amount = Money::round($request->string('amount')->toString());
        $balance = $request->user()->balances()->where('currency', $currency)->first();

        if ($balance instanceof Balance) {
            Gate::authorize('update', $balance);
            $balance->update(['amount' => $amount]);
        } else {
            $request->user()->balances()->create([
                'currency' => $currency,
                'amount' => $amount,
            ]);
        }

        return redirect()
            ->route('balances.index')
            ->with('status', "Saved {$currency} balance.");
    }

    public function destroy(Request $request, int $balance): RedirectResponse
    {
        $record = $request->user()->balances()->findOrFail($balance);

        Gate::authorize('delete', $record);

        $currency = $record->currency;
        $record->delete();

        return redirect()
            ->route('balances.index')
            ->with('status', "Removed {$currency}.");
    }
}
