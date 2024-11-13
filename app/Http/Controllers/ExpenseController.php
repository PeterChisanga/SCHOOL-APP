<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index() {
        $schoolId = Auth::user()->school_id;
        $expenses = Expense::where('school_id', $schoolId)->get();

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $expense = new Expense($request->all());
        $expense->school_id = Auth::user()->school_id;
        $expense->save();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    public function show(Expense $expense)
    {
        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->validate($request, [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        $expense->update($request->all());

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->school_id !== Auth::user()->school_id) {
            return redirect()->route('expenses.index')->with('error', 'Unauthorized access to this expense.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }
}
