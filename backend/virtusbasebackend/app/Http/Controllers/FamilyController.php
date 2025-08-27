<?php

namespace App\Http\Controllers\API;

use App\Models\Family;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseApiController;

class FamilyController extends BaseApiController
{
    public function index()
    {
        $user = auth()->user();
        $families = $user->families()->with('members')->get();

        return $this->sendResponse($families, 'Families retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $family = Family::create(['name' => $request->name]);

        // Maak de huidige user eigenaar van het gezin
        auth()->user()->families()->attach($family->id, ['role' => 'owner']);

        return $this->sendResponse($family, 'Family created successfully.', 201);
    }

    public function show(Family $family)
    {
        // Authorisatie: is user lid van dit gezin?
        if(!auth()->user()->families->contains($family->id)) {
            return $this->sendError('Unauthorized access to family.', [], 403);
        }

        $family->load('members');
        return $this->sendResponse($family, 'Family retrieved successfully.');
    }

    public function update(Request $request, Family $family)
    {
        // Authorisatie: is user eigenaar?
        if(auth()->user()->getRoleInFamily($family) !== 'owner') {
            return $this->sendError('Only owners can update the family.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $family->update($request->all());

        return $this->sendResponse($family, 'Family updated successfully.');
    }

    public function destroy(Family $family)
    {
        // Authorisatie: is user eigenaar?
        if(auth()->user()->getRoleInFamily($family) !== 'owner') {
            return $this->sendError('Only owners can delete the family.', [], 403);
        }

        $family->delete();

        return $this->sendResponse(null, 'Family deleted successfully.');
    }

    public function addMember(Request $request, Family $family)
    {
        // Authorisatie: heeft user rechten om leden toe te voegen?
        if(!auth()->user()->hasPermissionInFamily($family, 'invite_members')) {
            return $this->sendError('Unauthorized to add members.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:parent,child,guest'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        // Voeg gebruiker toe aan gezin
        $family->members()->attach($user->id, ['role' => $request->role]);

        return $this->sendResponse(null, 'Member added successfully.');
    }

    public function removeMember(Family $family, User $member)
    {
        // Authorisatie: heeft user rechten om leden te verwijderen?
        if(!auth()->user()->hasPermissionInFamily($family, 'remove_members')) {
            return $this->sendError('Unauthorized to remove members.', [], 403);
        }

        $family->members()->detach($member->id);

        return $this->sendResponse(null, 'Member removed successfully.');
    }
}
