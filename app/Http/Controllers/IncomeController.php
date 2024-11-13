<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class IncomeController extends Controller {
    public function index(Request $request) {
        $schoolId = auth()->user()->school_id;

        $years = Payment::where('school_id', $schoolId)
                    ->selectRaw('YEAR(created_at) as year')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

        $query = Payment::where('school_id', $schoolId);

        if ($request->term) {
            $query->where('term', $request->term);
        }

        if ($request->year) {
            $query->whereYear('created_at', $request->year);
        }

        $incomes = $query->select('term', 'type')
                        ->selectRaw('SUM(amount_paid) as total_income')
                        ->groupBy('term', 'type')
                        ->orderBy('term', 'asc')
                        ->get();

        return view('incomes.index', compact('incomes', 'years'));
    }
}
