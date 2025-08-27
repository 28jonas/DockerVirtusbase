<?php

namespace App\Http\Controllers\API;

use App\Models\ShoppingList;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseApiController;

class ShoppingListController extends BaseApiController
{
    public function index()
    {
        $user = auth()->user();

        $lists = ShoppingList::where(function($query) use ($user) {
            $query->whereMorph('owner', User::class, function($q) use ($user) {
                $q->where('id', $user->id);
            })
                ->orWhereMorph('owner', Family::class, function($q) use ($user) {
                    $q->whereIn('id', $user->families->pluck('id'));
                })
                ->orWhereHas('sharedWithUsers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })->with(['items', 'sharedWithUsers'])->get();

        return $this->sendResponse($lists, 'Shopping lists retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'owner_type' => 'required|in:user,family',
            'family_id' => 'required_if:owner_type,family|exists:families,id'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $data = $request->all();

        if ($request->owner_type === 'family') {
            $family = Family::find($request->family_id);
            if(!auth()->user()->families->contains($family->id)) {
                return $this->sendError('Unauthorized access to family.', [], 403);
            }
            $data['owner_type'] = Family::class;
            $data['owner_id'] = $family->id;
        } else {
            $data['owner_type'] = User::class;
            $data['owner_id'] = auth()->id();
        }

        $shoppingList = ShoppingList::create($data);

        return $this->sendResponse($shoppingList, 'Shopping list created successfully.', 201);
    }

    public function show(ShoppingList $shoppingList)
    {
        $this->authorize('view', $shoppingList);
        $shoppingList->load(['items', 'sharedWithUsers']);

        return $this->sendResponse($shoppingList, 'Shopping list retrieved successfully.');
    }

    public function update(Request $request, ShoppingList $shoppingList)
    {
        $this->authorize('update', $shoppingList);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $shoppingList->update($request->all());

        return $this->sendResponse($shoppingList, 'Shopping list updated successfully.');
    }

    public function destroy(ShoppingList $shoppingList)
    {
        $this->authorize('delete', $shoppingList);
        $shoppingList->delete();

        return $this->sendResponse(null, 'Shopping list deleted successfully.');
    }

    public function share(Request $request, ShoppingList $shoppingList)
    {
        $this->authorize('update', $shoppingList);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'permission_level' => 'required|in:view,edit'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        // Voeg sharing toe
        $shoppingList->sharedWithUsers()->attach($user->id, [
            'permission_level' => $request->permission_level
        ]);

        $shoppingList->update(['is_shared' => true]);

        return $this->sendResponse(null, 'Shopping list shared successfully.');
    }

    public function unshare(ShoppingList $shoppingList, User $user)
    {
        $this->authorize('update', $shoppingList);

        $shoppingList->sharedWithUsers()->detach($user->id);

        // Zet is_shared op false als er geen gedeelde gebruikers meer zijn
        if ($shoppingList->sharedWithUsers()->count() === 0) {
            $shoppingList->update(['is_shared' => false]);
        }

        return $this->sendResponse(null, 'User removed from shopping list.');
    }
}
