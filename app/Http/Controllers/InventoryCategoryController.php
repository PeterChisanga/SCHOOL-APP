<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class InventoryCategoryController extends Controller
{
    public function index()
    {
        try {
            $schoolId = Auth::user()->school_id;

            $categories = InventoryCategory::where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            return view('inventory.categories.index', compact('categories'));

        } catch (Exception $e) {
            \Log::error('Error fetching inventory categories: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch inventory categories.');
        }
    }

    public function create()
    {
        try {
            return view('inventory.categories.create');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the category creation form.');
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:100',
            ]);

            InventoryCategory::create([
                'school_id' => Auth::user()->school_id,
                'name' => $request->name,
            ]);

            return redirect()->route('inventory.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();

        } catch (Exception $e) {
            \Log::error('Error storing inventory category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create category: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(InventoryCategory $inventoryCategory)
    {
        try {
            $this->authorizeSchool($inventoryCategory);

            return view('inventory.categories.edit', compact('inventoryCategory'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the category edit form.');
        }
    }

    public function update(Request $request, InventoryCategory $inventoryCategory)
    {
        try {
            $this->authorizeSchool($inventoryCategory);

            $this->validate($request, [
                'name' => 'required|string|max:100',
            ]);

            $inventoryCategory->update([
                'name' => $request->name,
            ]);

            return redirect()->route('inventory.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();

        } catch (Exception $e) {
            \Log::error('Error updating inventory category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update category: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(InventoryCategory $inventoryCategory)
    {
        try {

            $this->authorizeSchool($inventoryCategory);

            $inventoryCategory->delete();

            return redirect()->route('inventory.categories.index')
                ->with('success', 'Category deleted successfully!');

        } catch (Exception $e) {
            \Log::error('Error deleting inventory category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete category.');
        }
    }

    // Multi-tenant protection
    private function authorizeSchool($model)
    {
        if ($model->school_id !== Auth::user()->school_id) {
            abort(403);
        }
    }
}
