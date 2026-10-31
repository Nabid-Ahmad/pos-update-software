<?php

namespace App\Http\Controllers;

use App\Liability;
use App\LiabilityPurchase;
use App\LiabilityPurchaseDetail;
use Illuminate\Http\Request;

class LiabilityPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $liabilityPurchases = LiabilityPurchase::with('details.liability', 'account')->latest()->get();
        return view('admin.account.liability-purchases.index', compact('liabilityPurchases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $liabilities = Liability::orderBy('name')->pluck('name', 'id');
        $accounts = \App\Account::companies()->pluck('account_name', 'id');
        return view('admin.account.liability-purchases.create', compact('liabilities', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (is_array($request->amounts) && array_sum($request->amounts) > 0) {
          
            $purchase = LiabilityPurchase::create([
                'date'          => fdate(today(), 'Y-m-d'),
                'description'   => $request->description,
                'account_id'    => $request->account_id,
                'amount'        => 0
            ]);

            foreach($request->amounts as $key => $amount) {
                LiabilityPurchaseDetail::create([
                    'particular' => $request->particulars[$key],
                    'amount' => $amount,
                    'liability_id' => $request->liability_ids[$key],
                    'company_id' => companyId(),
                    'liability_purchase_id' => $purchase->id
                ]);

                $liability = Liability::find($request->liability_ids[$key]);
                
                // Increase the liability balance
                $liability->increment('current_balance', $amount);

                // Add a transaction so this shows up in the Payment History as 'Loan Taken'
                $liability->transactions()->create([
                    'fk_account_id' => $request->account_id, // The optional account they selected
                    'date'          => fdate(today(), 'Y-m-d'),
                    'amount'        => $amount, // Positive because liability increases
                    'note'          => 'Purchase: ' . $request->particulars[$key],
                    'fk_company_id' => companyId(),
                    'created_by'    => auth()->id(),
                ]);
            }
            $totalAmount = LiabilityPurchaseDetail::where('liability_purchase_id', $purchase->id)->sum('amount');

            $purchase->update([
                'amount' => $totalAmount
            ]);

            return redirect()->route('liability-purchases.index')->withSuccess('Liability purchase successful');
            
        }

        return redirect()->back()->withError('Please add at least one liability item before saving.');
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\LiabilityPurchase  $liabilityPurchase
     * @return \Illuminate\Http\Response
     */
    public function show(LiabilityPurchase $liabilityPurchase)
    {
        return view('admin.account.liability-purchases.show', compact('liabilityPurchase'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LiabilityPurchase  $liabilityPurchase
     * @return \Illuminate\Http\Response
     */
    public function edit(LiabilityPurchase $liabilityPurchase)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LiabilityPurchase  $liabilityPurchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LiabilityPurchase $liabilityPurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LiabilityPurchase  $liabilityPurchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(LiabilityPurchase $liabilityPurchase)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($liabilityPurchase) {
                foreach($liabilityPurchase->details as $detail) {
                    $liability = \App\Liability::find($detail->liability_id);
                    if ($liability) {
                        // Reverse the balance
                        $liability->decrement('current_balance', $detail->amount);

                        // Remove the corresponding transaction from history
                        $liability->transactions()
                            ->where('amount', $detail->amount)
                            ->where('note', 'Purchase: ' . $detail->particular)
                            ->where('created_at', '>=', $detail->created_at->subMinutes(5))
                            ->where('created_at', '<=', $detail->created_at->addMinutes(5))
                            ->delete();
                    }
                }
                
                $liabilityPurchase->details()->delete();
                $liabilityPurchase->delete();
            });

            return redirect()->back()->withSuccess('Liability purchase successfully deleted');
        } catch (\Exception $e) {
            return redirect()->back()->withError('Error: ' . $e->getMessage());
        }
    }
}
