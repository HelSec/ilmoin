<?php

namespace App\Http\Controllers\Organizations;

use App\Http\Controllers\Controller;
use App\Organizations\Events\Event;
use App\Organizations\Events\EventRegistration;
use App\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        $event->loadMissing('organization', 'registrationOptions');
        return view('events.view', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }

    public function register(Request $request, Event $event)
    {
        if (!Gate::check('attend', $event)) {
            return view('generic.message', [
                'header' => 'Can\'t attend this event',
                'title' => 'Can\'t attend this event',
                'message' => 'You are currently to unable to attend this event. There might be multiple reasons for this, such as the event being full, the registration being closed, or some other reason.',
            ]);
        }

        $option = $event->getRegistrationOption($request->user());

        if (!$option) {
            return 'wtf <!-- EventController#showRegisterForm -->';
        }

        $confirmed = !$option->count_to_slots || $event->max_slots - $event->getTakenSlots() >= 1;

        if ($request->isMethod('post')) {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
                'confirmed' => $confirmed,
                'waitlist_priority' => $option->waitlist_priority,
                'count_to_slots' => $option->count_to_slots,
            ]);

            return redirect()->route('events.show', $event);
        }

        return view('events.register', [
            'event' => $event,
            'confirmed' => $confirmed,
        ]);
    }
}
