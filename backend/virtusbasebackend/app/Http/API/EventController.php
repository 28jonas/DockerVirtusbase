<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Models\Calendar;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseApiController;

class EventController extends BaseApiController
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'calendar_id' => 'nullable|exists:calendars,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $user = auth()->user();
        $query = Event::whereHas('calendar', function($q) use ($user) {
            $q->where(function($query) use ($user) {
                $query->whereMorph('owner', User::class, function($q) use ($user) {
                    $q->where('id', $user->id);
                })
                    ->orWhereMorph('owner', Family::class, function($q) use ($user) {
                        $q->whereIn('id', $user->families->pluck('id'));
                    });
            });
        });

        if ($request->calendar_id) {
            $query->where('calendar_id', $request->calendar_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start', [$request->start_date, $request->end_date]);
        }

        $events = $query->get();

        return $this->sendResponse($events, 'Events retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'calendar_id' => 'required|exists:calendars,id',
            'start' => 'required|date',
            'end' => 'nullable|date|after:start',
            'owner_type' => 'required|in:user,family',
            'family_id' => 'required_if:owner_type,family|exists:families,id'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $calendar = Calendar::find($request->calendar_id);
        $this->authorize('view', $calendar);

        $data = $request->all();

        // Bepaal eigenaar voor het event
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

        unset($data['family_id']);

        $event = Event::create($data);

        return $this->sendResponse($event, 'Event created successfully.', 201);
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        return $this->sendResponse($event, 'Event retrieved successfully.');
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'start' => 'date',
            'end' => 'nullable|date|after:start'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $event->update($request->all());

        return $this->sendResponse($event, 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();

        return $this->sendResponse(null, 'Event deleted successfully.');
    }
}
