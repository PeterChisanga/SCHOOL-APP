<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\Pupil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class ParentController extends Controller
{
    public function index() {
        try {
            $schoolId = Auth::user()->school_id;
            $parents = ParentModel::where('school_id', $schoolId)->get();

            return view('parents.index', compact('parents'));
        } catch (Exception $e) {
            \Log::error('Error fetching parents: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch parents.');
        }
    }

    public function create(Pupil $pupil) {
        try {
            return view('parents.create', compact('pupil'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the parent creation form.');
        }
    }

    public function store(Request $request) {
        try {
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

            return redirect()->route('pupils.show', $request->pupil_id)
                ->with('success', 'Parent registered successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error storing parent: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to register parent: ' . $e->getMessage())->withInput();
        }
    }

    // public function show(ParentModel $parent)
    // {
    //     return view('parents.show', compact('parent'));
    // }

    public function edit(ParentModel $parent) {
        try {
            $pupils = Pupil::all();
            return view('parents.edit', compact('parent', 'pupils'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the parent edit form.');
        }
    }

    public function update(Request $request, ParentModel $parent) {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|string|email|max:255',
                'address' => 'nullable|string|max:255',
                'pupil_id' => 'required|exists:pupils,id',
            ]);

            $parent->update($request->all());

            return redirect()->route('pupils.show', $request->pupil_id)
                ->with('success', 'Parent information updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            \Log::error('Error updating parent: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update parent: ' . $e->getMessage())->withInput();
        }
    }

    // public function destroy(ParentModel $parent)
    // {
    //     $parent->delete();

    //     return redirect()->route('parents.index')
    //         ->with('success', 'Parent deleted successfully!');
    // }
}
