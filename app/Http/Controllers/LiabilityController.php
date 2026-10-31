<?php

namespace App\Http\Controllers;

use App\Liability;
use App\Account;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $liabilities = Liability::with('transactions.account')->where('company_id', companyId())->latest()->get();
        $accounts = Account::companies()->pluck('account_name', 'id');
        return view('admin.account.liabilities.index', compact('liabilities', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accounts = Account::companies()->pluck('account_name', 'id');
        return view('admin.account.liabilities.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required',
            'opening'    => 'required|numeric|min:0',
            'account_id' => 'required',
            'date'       => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $liability = Liability::create($request->all());
                $liability->update([
                    'current_balance' => $liability->opening
                ]);

                if ($request->opening > 0) {
                    $liability->transactions()->create([
                        'fk_account_id' => $request->account_id,
                        'date'          => $request->date,
                        'amount'        => $request->opening, // positive because money is coming in
                        'fk_company_id' => companyId(),
                        'created_by'    => auth()->id(),
                    ]);
                }
            });

            return redirect()->route('liabilities.index')->withSuccess('New liability added and funds transferred successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withError('Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $liability = Liability::with(['transactions' => function($q) {
            $q->orderBy('date', 'desc')->orderBy('id', 'desc');
        }, 'transactions.account', 'transactions.created_user'])->where('company_id', companyId())->findOrFail($id);

        return view('admin.account.liabilities.show', compact('liability'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Liability $liability)
    {
        return view('admin.account.liabilities.edit', compact('liability'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Liability $liability)
    {
        $request->validate([
            'name' => 'required',
            'opening' => 'required|numeric|min:0'
        ]);

        try {
            DB::transaction(function () use ($request, $liability) {
                $oldOpening = $liability->opening;
                $newOpening = $request->opening;
                
                $liability->update($request->except(['opening', 'current_balance']));
                
                if ($oldOpening != $newOpening) {
                    $liability->opening = $newOpening;
                    $liability->current_balance = $liability->current_balance + ($newOpening - $oldOpening);
                    $liability->save();

                    // Update the initial transaction (cash-in) if it exists
                    $initialTransaction = $liability->transactions()->where('amount', '>', 0)->first();
                    if ($initialTransaction) {
                        $initialTransaction->update(['amount' => $newOpening]);
                    }
                }
            });
            return redirect()->route('liabilities.index')->withSuccess('Liability updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withError('Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $liability = Liability::where('company_id', companyId())->findOrFail($id);
            if ($liability->default_account != 1) {
                DB::transaction(function () use ($liability) {
                    $liability->transactions()->delete();
                    $liability->delete();
                });
                return redirect()->back()->withSuccess('Liability and its transactions deleted successfully!');
            }
            return redirect()->back()->withError('Cannot delete default liability!');
        } catch (\Exception $e) {
            return redirect()->back()->withError('Something went wrong!');
        }
    }

    public function payment(Request $request)
    {
        $request->validate([
            'liability_id' => 'required',
            'amount'       => 'required|numeric|min:0',
            'date'         => 'required|date',
            'account_id'   => 'required'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $liability = Liability::where('company_id', companyId())->findOrFail($request->liability_id);

                // Decrease liability current balance since we are paying it off
                $liability->current_balance -= $request->amount;
                $liability->save();

                // Record the payment in transactions (money going out from account)
                $liability->transactions()->create([
                    'fk_account_id' => $request->account_id,
                    'date'          => $request->date,
                    'amount'        => -$request->amount, // negative because money is leaving
                    'note'          => $request->note,
                    'fk_company_id' => companyId(),
                    'created_by'    => auth()->id(),
                ]);
            });

            return redirect()->back()->withSuccess('Liability payment successfully added!');
        } catch (\Exception $e) {
            return redirect()->back()->withError('Error: ' . $e->getMessage());
        }
    }
}
