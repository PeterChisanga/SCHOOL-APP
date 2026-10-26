<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeStructureController extends Controller
{
    private const TYPES = ['School Fees', 'Lunch', 'Transport Fee', 'Uniform'];
    private const TERMS = ['Term 1', 'Term 2', 'Term 3'];

    public function index()
    {
        $fees = FeeStructure::with('class')
            ->where('school_id', Auth::user()->school_id)
            ->orderBy('class_id')
            ->orderBy('term')
            ->orderBy('type')
            ->get();

        return view('fees.index', compact('fees'));
    }

    public function create()
    {
        return view('fees.create', [
            'classes' => $this->classes(),
            'types'   => self::TYPES,
            'terms'   => self::TERMS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['school_id'] = Auth::user()->school_id;

        FeeStructure::create($data);

        return redirect()->route('fees.index')->with('success', 'Fee added successfully.');
    }

    public function edit(FeeStructure $fee)
    {
        $this->authorizeSchool($fee);

        return view('fees.edit', [
            'fee'     => $fee,
            'classes' => $this->classes(),
            'types'   => self::TYPES,
            'terms'   => self::TERMS,
        ]);
    }

    public function update(Request $request, FeeStructure $fee)
    {
        $this->authorizeSchool($fee);

        $fee->update($this->validated($request));

        return redirect()->route('fees.index')->with('success', 'Fee updated successfully.');
    }

    public function destroy(FeeStructure $fee)
    {
        $this->authorizeSchool($fee);

        $fee->delete();

        return redirect()->route('fees.index')->with('success', 'Fee deleted successfully.');
    }

    private function classes()
    {
        return ClassModel::where('school_id', Auth::user()->school_id)->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'type'     => 'required|string|max:255',
            'amount'   => 'required|numeric|min:0',
            'term'     => 'required|string|max:20',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function authorizeSchool(FeeStructure $fee): void
    {
        abort_if((int) $fee->school_id !== (int) Auth::user()->school_id, 403);
    }
}
