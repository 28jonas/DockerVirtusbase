<?php

namespace App\Http\Controllers\API;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseApiController;

class ShoppingItemController extends BaseApiController
{
    public function index(ShoppingList $shoppingList)
    {
        $this->authorize('view', $shoppingList);

        $items = $shoppingList->items()->with('addedBy')->get();
        return $this->sendResponse($items, 'Shopping items retrieved successfully.');
    }

    public function store(Request $request, ShoppingList $shoppingList)
    {
        $this->authorize('update', $shoppingList);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|integer|min:1'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $item = $shoppingList->items()->create([
            'name' => $request->name,
            'quantity' => $request->quantity ?? 1,
            'added_by_user_id' => auth()->id()
        ]);

        return $this->sendResponse($item, 'Item added successfully.', 201);
    }

    public function update(Request $request, ShoppingItem $shoppingItem)
    {
        $this->authorize('update', $shoppingItem->list);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'quantity' => 'integer|min:1',
            'is_completed' => 'boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $data = $request->all();
        if ($request->has('is_completed') && $request->is_completed) {
            $data['completed_at'] = now();
        } elseif ($request->has('is_completed') && !$request->is_completed) {
            $data['completed_at'] = null;
        }

        $shoppingItem->update($data);

        return $this->sendResponse($shoppingItem, 'Item updated successfully.');
    }

    public function destroy(ShoppingItem $shoppingItem)
    {
        $this->authorize('update', $shoppingItem->list);
        $shoppingItem->delete();

        return $this->sendResponse(null, 'Item deleted successfully.');
    }

    public function toggleComplete(ShoppingItem $shoppingItem)
    {
        $this->authorize('update', $shoppingItem->list);

        $shoppingItem->update([
            'is_completed' => !$shoppingItem->is_completed,
            'completed_at' => $shoppingItem->is_completed ? null : now()
        ]);

        return $this->sendResponse($shoppingItem, 'Item status updated successfully.');
    }
}
