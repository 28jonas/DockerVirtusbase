<?php

namespace App\Http\Controllers\API;

use App\Models\Calendar;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseApiController;

class CalendarController extends BaseApiController
{
    public function index()
    {
        $user = auth()->user();

        // Haal alle kalenders op waar gebruiker toegang toe heeft
        $calendars = Calendar::where(function($query) use ($user) {
            // Persoonlijke kalenders
            $query->whereMorph('owner', User::class, function($q) use ($user) {
                $q->where('id', $user->id);
            })
                // Gezinskalenders
                ->orWhereMorph('owner', Family::class, function($q) use ($user) {
                    $q->whereIn('id', $user->families->pluck('id'));
                });
        })->with('events')->get();

        return $this->sendResponse($calendars, 'Calendars retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'owner_type' => 'required|in:user,family',
            'family_id' => 'required_if:owner_type,family|exists:families,id',
            'color' => 'string',
            'is_public' => 'boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $data = $request->all();

        // Bepaal eigenaar op basis van type
        if ($request->owner_type === 'family') {
            $family = Family::find($request->family_id);
            // Authorisatie: is user lid van dit gezin?
            if(!auth()->user()->families->contains($family->id)) {
                return $this->sendError('Unauthorized access to family.', [], 403);
            }
            $data['owner_type'] = Family::class;
            $data['owner_id'] = $family->id;
        } else {
            $data['owner_type'] = User::class;
            $data['owner_id'] = auth()->id();
        }

        $calendar = Calendar::create($data);

        return $this->sendResponse($calendar, 'Calendar created successfully.', 201);
    }

    public function show(Calendar $calendar)
    {
        // Authorisatie: heeft gebruiker toegang tot deze kalender?
        $this->authorize('view', $calendar);

        $calendar->load('events');
        return $this->sendResponse($calendar, 'Calendar retrieved successfully.');
    }

    public function update(Request $request, Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'color' => 'string',
            'is_public' => 'boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $calendar->update($request->all());

        return $this->sendResponse($calendar, 'Calendar updated successfully.');
    }

    public function destroy(Calendar $calendar)
    {
        $this->authorize('delete', $calendar);

        $calendar->delete();

        return $this->sendResponse(null, 'Calendar deleted successfully.');
    }
}
