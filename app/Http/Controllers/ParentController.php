<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\Pupil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;
        $parents = ParentModel::where('school_id', $schoolId)->get();

        return view('parents.index', compact('parents'));
    }

    public function create(Pupil $pupil)
    {
        return view('parents.create', compact('pupil'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:parents,phone',
            'email' => 'nullable|string|email|max:255|unique:parents,email',
            'address' => 'nullable|string|max:255',
            'pupil_id' => 'required|exists:pupils,id',
        ]);

        $schoolId = Auth::user()->school_id;

        $parentData = $request->all();
        $parentData['school_id'] = $schoolId;

        ParentModel::create($parentData);

        return redirect()->route('pupils.show',$request->pupil_id)
            ->with('success', 'Parent registered successfully!');
    }

    // public function show(ParentModel $parent)
    // {
    //     return view('parents.show', compact('parent'));
    // }

    public function edit(ParentModel $parent)
    {
        $pupils = Pupil::all();
        return view('parents.edit', compact('parent', 'pupils'));
    }

    public function update(Request $request, ParentModel $parent)
    {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string|max:255',
            'pupil_id' => 'required|exists:pupils,id',
        ]);

        $parent->update($request->all());

        return redirect()->route('pupils.show',$request->pupil_id)
            ->with('success', 'Parent information updated successfully!');
    }

    // public function destroy(ParentModel $parent)
    // {
    //     $parent->delete();

    //     return redirect()->route('parents.index')
    //         ->with('success', 'Parent deleted successfully!');
    // }
}
