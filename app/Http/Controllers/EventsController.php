<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class EventsController extends BaseController
{
    public function getWarmupEvents() {
        return Event::all();
    }
    /* TODO: complete getEventsWithWorkshops so that it returns all events including the workshops
     Requirements:
    - maximum 2 sql queries
    - Don't post process query result in PHP
    - verify your solution with `php artisan test`
    - do a `git commit && git push` after you are done or when the time limit is over

    Hints:
    - partial or not working answers also get graded so make sure you commit what you have

    Sample response on GET /events:
    ```json
    [
        {
            "id": 1,
            "name": "Laravel convention 2020",
            "created_at": "2021-04-25T09:32:27.000000Z",
            "updated_at": "2021-04-25T09:32:27.000000Z",
            "workshops": [
                {
                    "id": 1,
                    "start": "2020-02-21 10:00:00",
                    "end": "2020-02-21 16:00:00",
                    "event_id": 1,
                    "name": "Illuminate your knowledge of the laravel code base",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                }
            ]
        },
        {
            "id": 2,
            "name": "Laravel convention 2021",
            "created_at": "2021-04-25T09:32:27.000000Z",
            "updated_at": "2021-04-25T09:32:27.000000Z",
            "workshops": [
                {
                    "id": 2,
                    "start": "2021-10-21 10:00:00",
                    "end": "2021-10-21 18:00:00",
                    "event_id": 2,
                    "name": "The new Eloquent - load more with less",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                },
                {
                    "id": 3,
                    "start": "2021-11-21 09:00:00",
                    "end": "2021-11-21 17:00:00",
                    "event_id": 2,
                    "name": "AutoEx - handles exceptions 100% automatic",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                }
            ]
        },
        {
            "id": 3,
            "name": "React convention 2021",
            "created_at": "2021-04-25T09:32:27.000000Z",
            "updated_at": "2021-04-25T09:32:27.000000Z",
            "workshops": [
                {
                    "id": 4,
                    "start": "2021-08-21 10:00:00",
                    "end": "2021-08-21 18:00:00",
                    "event_id": 3,
                    "name": "#NoClass pure functional programming",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                },
                {
                    "id": 5,
                    "start": "2021-08-21 09:00:00",
                    "end": "2021-08-21 17:00:00",
                    "event_id": 3,
                    "name": "Navigating the function jungle",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                }
            ]
        }
    ]
     */

    public function getEventsWithWorkshops() {
        $events = DB::table('events')
            ->select('events.id', 'events.name', 'events.created_at as eventCreatedAt', 'events.updated_at as eventUpdatedAt', 'workshops.start', 'workshops.end','workshops.id as workshop_id', 'workshops.name as workshop_title', 'workshops.event_id as event_id','workshops.created_at as workshopCreatedAt','workshops.updated_at as workshopUpdatedAt')
            ->leftJoin('workshops', 'events.id', '=', 'workshops.event_id')
            ->get();

        $result = [];
        foreach ($events as $event) {
            $eventId = $event->id;
            if (!isset($result[$eventId])) {
                $result[$eventId] = [
                    'id' => $eventId,
                    'name' => $event->name,
                    'created_at' => $event->eventCreatedAt,
                    'updated_at' => $event->eventUpdatedAt,
                    'workshops' => [],
                ];
            }
            if ($event->workshop_title) {
                $result[$eventId]['workshops'][] = [
                    'id' => $event->workshop_id,
                    'start' => $event->start,
                    'end' => $event->end,
                    'event_id' => $event->event_id,
                    'name' => $event->workshop_title,
                    'created_at' => $event->workshopCreatedAt,
                    'updated_at' => $event->workshopUpdatedAt,
                ];
            }
        }

        return array_values($result);
        // throw new \Exception('implement in coding task 1');
    }


    /* TODO: complete getFutureEventWithWorkshops so that it returns events with workshops, that have not yet started
    Requirements:
    - only events that have not yet started should be included
    - the event starting time is determined by the first workshop of the event
    - the eloquent expressions should result in maximum 3 SQL queries, no matter the amount of events
    - Don't post process query result in PHP
    - verify your solution with `php artisan test`
    - do a `git commit && git push` after you are done or when the time limit is over

    Hints:
    - partial or not working answers also get graded so make sure you commit what you have
    - join, whereIn, min, groupBy, havingRaw might be helpful
    - in the sample data set  the event with id 1 is already in the past and should therefore be excluded

    Sample response on GET /futureevents:
    ```json
    [
        {
            "id": 2,
            "name": "Laravel convention 2021",
            "created_at": "2021-04-20T07:01:14.000000Z",
            "updated_at": "2021-04-20T07:01:14.000000Z",
            "workshops": [
                {
                    "id": 2,
                    "start": "2021-10-21 10:00:00",
                    "end": "2021-10-21 18:00:00",
                    "event_id": 2,
                    "name": "The new Eloquent - load more with less",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                },
                {
                    "id": 3,
                    "start": "2021-11-21 09:00:00",
                    "end": "2021-11-21 17:00:00",
                    "event_id": 2,
                    "name": "AutoEx - handles exceptions 100% automatic",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                }
            ]
        },
        {
            "id": 3,
            "name": "React convention 2021",
            "created_at": "2021-04-20T07:01:14.000000Z",
            "updated_at": "2021-04-20T07:01:14.000000Z",
            "workshops": [
                {
                    "id": 4,
                    "start": "2021-08-21 10:00:00",
                    "end": "2021-08-21 18:00:00",
                    "event_id": 3,
                    "name": "#NoClass pure functional programming",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                },
                {
                    "id": 5,
                    "start": "2021-08-21 09:00:00",
                    "end": "2021-08-21 17:00:00",
                    "event_id": 3,
                    "name": "Navigating the function jungle",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                }
            ]
        }
    ]
    ```
     */

     public function getFutureEventsWithWorkshops()
     {
        $now = now();
        $futureEvents = Event::select('events.id', 'events.name', 'events.created_at', 'events.updated_at', 'workshops.id as workshop_id', 'workshops.name as workshop_name', 'workshops.start', 'workshops.end')
            ->leftJoin('workshops', function($join) {
                $join->on('events.id', '=', 'workshops.event_id')
                    ->where('workshops.start', '>', now());
            })
            ->whereNotNull('workshops.id')
            ->orderBy('events.id', 'asc')
            ->orderBy('workshops.start', 'asc')
            ->get()
            ->groupBy('id');

        $result = [];

        foreach ($futureEvents as $eventId => $eventData) {
            $workshops = [];

            foreach ($eventData as $event) {
                $workshops[] = [
                    'id' => $event->workshop_id,
                    'name' => $event->workshop_name,
                    'start' => $event->start,
                    'end' => $event->end,
                ];
            }

            $result[] = [
                'id' => $eventId,
                'name' => $eventData->first()->name,
                'created_at' => $eventData->first()->created_at,
                'updated_at' => $eventData->first()->updated_at,
                'workshops' => $workshops,
            ];
        }

        return $result;
    }
}
