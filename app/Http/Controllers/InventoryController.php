<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $schoolId = Auth::user()->school_id;

            $query = Inventory::where('school_id', $schoolId)
                ->with('category')
                ->orderBy('created_at', 'desc');

            // Filter by category
            if ($request->has('category') && $request->category != '') {
                $query->where('category_id', $request->category);
            }

            // Search by name
            if ($request->has('search') && $request->search != '') {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $inventories = $query->get();

            $categories = InventoryCategory::where('school_id', $schoolId)->get();

            return view('inventory.index', compact('inventories', 'categories'));

        } catch (Exception $e) {
            \Log::error('Error fetching inventory items: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch inventory items.');
        }
    }

    public function create()
    {
        try {
            $schoolId = Auth::user()->school_id;
            $categories = InventoryCategory::where('school_id', $schoolId)->get();

            return view('inventory.create', compact('categories'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the inventory creation form.');
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'inventory_category_id' => [
                    'required',
                    Rule::exists('inventory_categories', 'id')
                        ->where('school_id', Auth::user()->school_id),
                ],
                'quantity' => 'required|integer|min:0',
                'condition' => 'required|string|max:100',
                'location' => 'nullable|string|max:255',
                'date_added' => 'required|date',
            ]);

            DB::transaction(function () use ($request, &$inventory) {
                $inventory = Inventory::create([
                    'school_id' => Auth::user()->school_id,
                    'category_id' => $request->inventory_category_id,
                    'item_name' => $request->name,
                    'quantity' => $request->quantity,
                    'condition' => $request->condition,
                    'location' => $request->location,
                    'date_added' => $request->date_added,
                ]);

                InventoryActivityLog::create([
                    'inventory_id' => $inventory->id,
                    'user_id' => auth()->id(),
                    'old_quantity' => 0,
                    'new_quantity' => $inventory->quantity,
                    'change_amount' => $inventory->quantity,
                    'action_type' => 'created',
                    'note' => 'Initial stock added',
                ]);
            });

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory item added successfully!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();

        } catch (Exception $e) {
            \Log::error('Error storing inventory item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add inventory item: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Inventory $inventory)
    {
        try {
            $this->authorizeSchool($inventory);

            $categories = InventoryCategory::where('school_id', Auth::user()->school_id)->get();

            return view('inventory.edit', compact('inventory', 'categories'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong while loading the edit form.');
        }
    }

    public function update(Request $request, Inventory $inventory)
    {
        try {

            $this->authorizeSchool($inventory);

            $this->validate($request, [
                'name' => 'required|string|max:255',
                'inventory_category_id' => [
                    'required',
                    Rule::exists('inventory_categories', 'id')
                            ->where('school_id', Auth::user()->school_id),
                ],
                'quantity' => 'required|integer|min:0',
                'condition' => 'required|string|max:100',
                'location' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:500',
                'date_added' => 'required|date',
            ]);

            DB::transaction(function () use ($inventory, $request) {

                $oldQuantity = $inventory->quantity;
                $newQuantity = $request->quantity;
                $changeAmount = $newQuantity - $oldQuantity;

                $inventory->update([
                    'category_id' => $request->inventory_category_id,
                    'item_name' => $request->name,
                    'quantity' => $newQuantity,
                    'condition' => $request->condition,
                    'location' => $request->location,
                    'date_added' => $request->date_added,
                ]);

                if ($oldQuantity != $newQuantity) {
                    InventoryActivityLog::create([
                        'inventory_id' => $inventory->id,
                        'user_id' => auth()->id(),
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'change_amount' => $changeAmount,
                        'action_type' => 'adjusted',
                        'note' => $request->note,
                    ]);
                }
            });

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory item updated successfully!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();

        } catch (Exception $e) {
            \Log::error('Error updating inventory item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update inventory item: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Inventory $inventory)
    {
        try {
            $this->authorizeSchool($inventory);

            $inventory->delete();

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory item deleted successfully!');

        } catch (Exception $e) {
            \Log::error('Error deleting inventory item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete inventory item.');
        }
    }

    public function addStock(Request $request, Inventory $inventory)
    {
        try {
            $this->authorizeSchool($inventory);

            $request->validate([
                'amount' => 'required|integer|min:1',
                'note'   => 'nullable|string|max:500'
            ]);

            DB::transaction(function () use ($inventory, $request) {

                $oldQuantity = $inventory->quantity;
                $amount = $request->amount;
                $newQuantity = $oldQuantity + $amount;

                $inventory->update([
                    'quantity' => $newQuantity
                ]);

                InventoryActivityLog::create([
                    'inventory_id'  => $inventory->id,
                    'user_id'       => auth()->id(),
                    'old_quantity'  => $oldQuantity,
                    'new_quantity'  => $newQuantity,
                    'change_amount' => $amount,
                    'action_type'   => 'added',
                    'note'          => $request->note,
                ]);
            });

            return back()->with('success', 'Stock added successfully.');

        } catch (Exception $e) {
            \Log::error('Error adding stock: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeStock(Request $request, Inventory $inventory)
    {
        try {
            $this->authorizeSchool($inventory);

            $request->validate([
                'amount' => 'required|integer|min:1',
                'note'   => 'nullable|string|max:500'
            ]);

            if ($inventory->quantity < $request->amount) {
                return back()->with('error', 'Not enough stock available.');
            }

            DB::transaction(function () use ($inventory, $request) {

                $oldQuantity = $inventory->quantity;
                $amount = $request->amount;
                $newQuantity = $oldQuantity - $amount;

                $inventory->update([
                    'quantity' => $newQuantity
                ]);

                InventoryActivityLog::create([
                    'inventory_id'  => $inventory->id,
                    'user_id'       => auth()->id(),
                    'old_quantity'  => $oldQuantity,
                    'new_quantity'  => $newQuantity,
                    'change_amount' => -$amount,
                    'action_type'   => 'removed',
                    'note'          => $request->note,
                ]);
            });

            return back()->with('success', 'Stock removed successfully.');

        } catch (Exception $e) {
            \Log::error('Error removing stock: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function activity(Inventory $inventory)
    {
        try {

            $this->authorizeSchool($inventory);

            $logs = $inventory->activityLogs()
                ->with('user')
                ->latest()
                ->get();

            return view('inventory.activity', compact('inventory', 'logs'));

        } catch (Exception $e) {

            \Log::error('Error loading inventory activity: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to load activity log.');
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
