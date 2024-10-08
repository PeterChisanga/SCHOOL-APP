<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        return view('classes.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $schoolId = Auth::user()->school_id;

        ClassModel::create([
            'name' => $request->input('name'),
            'school_id' => $schoolId,
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Class created successfully!');
    }

    public function show(ClassModel $class)
    {
        $schoolId = Auth::user()->school_id;

        if ($class->school_id !== $schoolId) {
            return redirect()->route('classes.index')
                ->with('error', 'You are not authorized to view this class.');
        }

        return view('classes.show', compact('class'));
    }

    public function edit(ClassModel $class)
    {
        $schoolId = Auth::user()->school_id;

        if ($class->school_id !== $schoolId) {
            return redirect()->route('classes.index')
                ->with('error', 'You are not authorized to edit this class.');
        }

        return view('classes.edit', compact('class'));
    }

    public function update(Request $request, ClassModel $class)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $schoolId = Auth::user()->school_id;

        if ($class->school_id !== $schoolId) {
            return redirect()->route('classes.index')
                ->with('error', 'You are not authorized to update this class.');
        }

        $class->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Class updated successfully!');
    }

    public function destroy(ClassModel $class)
    {
        $schoolId = Auth::user()->school_id;

        if ($class->school_id !== $schoolId) {
            return redirect()->route('classes.index')
                ->with('error', 'You are not authorized to delete this class.');
        }

        $class->delete();

        return redirect()->route('classes.index')
            ->with('success', 'Class deleted successfully!');
    }
}
