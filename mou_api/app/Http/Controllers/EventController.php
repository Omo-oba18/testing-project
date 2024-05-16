<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Enums\EventAction;
use App\Enums\EventTab;
use App\Enums\EventType;
use App\Enums\UserType;
use App\Event;
use App\Events\EventCreate;
use App\Events\EventInteract;
use App\Events\EventUpdate;
use App\Http\Requests\EventFilterRequest;
use App\Http\Requests\EventRequest;
use App\Http\Resources\EventAlarmResource;
use App\Http\Resources\EventAndRosterResource;
use App\Http\Resources\EventResource;
use App\Notifications\NotifyEvent;
use App\Notifications\SmsUseApp;
use App\Services\AuthService;
use App\Services\EventService;
use App\Services\RosterService;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    private $eventService;

    private $rosterService;

    public function __construct(AuthService $authService, EventService $eventService, RosterService $rosterService)
    {
        parent::__construct($authService);
        $this->eventService = $eventService;
        $this->rosterService = $rosterService;
    }

    public function checkEventDateOfMonth(Request $request)
    {
        $validated = $this->validate($request, [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after:start_date'],
            'type' => ['required', Rule::in(UserType::getValues())],
        ]);

        $data = $this->eventService->checkEventAndTaskInDateOfMonth($validated['start_date'], $validated['end_date'], $validated['type']);

        return response()->json(count($data) > 0 ? $data : null);
    }

    /**
     * List event by start_date
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function indexByDate(Request $request)
    {
        $this->validate($request, [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'user_type' => ['nullable', Rule::in(UserType::getValues())],
        ]);

        $startDay = $request->get('start_date');
        $userType = $request->user_type;
        $events = $this->eventService->queryByDate($startDay, $userType);
        $events = $events->paginate(20);

        return EventAndRosterResource::collection($events);
    }

    /**
     * Count event status by waiting to user - waiting - confirmed
     */
    public function countEventStatus(EventFilterRequest $request): JsonResponse
    {
        $type = $request->type;
        $status = $request->status;
        $userType = $request->user_type;
        $tab = $request->tab;
        $type1 = $type;
        $type2 = $type;
        $type3 = $type;
        $typeDefault = array_filter([
            $userType == UserType::PERSONAL ? EventType::EVENT->value : null,
            EventType::PROJECT_TASK->value,
            EventType::TASK->value,
            EventType::ROSTER->value,
        ]);

        switch ($tab) {
            case EventTab::FOR_YOU_TO_CONFIRM->value:
                $type2 = $userType == UserType::PERSONAL ? [EventType::EVENT->value] : $typeDefault;
                $type3 = $typeDefault;
                break;
            case EventTab::WAITING_TO_CONFIRM->value:
                $type1 = $typeDefault;
                $type3 = $typeDefault;
                $type2 = $userType == UserType::PERSONAL ? [EventType::EVENT->value] : $type;
                break;
            case EventTab::CONFIRMED->value:
                $type2 = $userType == UserType::PERSONAL ? [EventType::EVENT->value] : $typeDefault;
                $type1 = $typeDefault;
                break;
        }

        $countForYouToConfirm = $this->eventService->queryForYouToConfirm($type1 ? $type1 : [], $status)->count();

        $countWaitingToConfirm = $userType == UserType::BUSINESS ?
            $this->eventService->queryWaitingToConfirmBusiness($type2 ? $type2 : [], $status)->count() :
            $this->eventService->queryWaitingToConfirmPersonal($type2 ? $type2 : [], $status)->count();
        $countConfirmed = $this->eventService->queryConfirmed($type3 ? $type3 : [], $status, $userType)->count();
        $countDenied = $userType == UserType::BUSINESS ? $this->eventService->queryDenied($type ? $type : [], $status)->count() : 0;

        return response()->json([
            'for_you_to_confirm' => $countForYouToConfirm,
            'waiting_to_confirm' => $countWaitingToConfirm,
            'confirmed' => $countConfirmed,
            'denied' => $countDenied,
        ]);
    }

    /**
     * List event: For you to confirm
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function indexForYouToConfirm(EventFilterRequest $request)
    {
        $type = $request->type;
        $status = $request->status;
        $events = $this->eventService->queryForYouToConfirm($type ? $type : [], $status);
        $events = $events->paginate(20);

        return EventAndRosterResource::collection($events);
    }

    /**
     * List event: Waiting to confirm
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function indexWaitingToConfirm(EventFilterRequest $request)
    {
        $type = $request->type;
        $status = $request->status;
        $userType = $request->user_type;
        if ($userType == UserType::BUSINESS) {
            $events = $this->eventService->queryWaitingToConfirmBusiness($type ? $type : [], $status)->paginate();

            return EventAndRosterResource::collection($events);
        }
        $events = $this->eventService->queryWaitingToConfirmPersonal([EventType::EVENT->value], $status)->paginate();

        return EventResource::collection($events);
    }

    /**
     * List event: Waiting to confirm
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function indexConfirmed(EventFilterRequest $request)
    {
        $type = $request->type;
        $status = $request->status;
        $userType = $request->user_type;
        $events = $this->eventService->queryConfirmed($type ? $type : [], $status, $userType);
        $events = $events->paginate(20);

        return EventAndRosterResource::collection($events);
    }

    /**
     * List event: Denied
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function indexDenied(EventFilterRequest $request)
    {
        $type = $request->type;
        $status = $request->status;
        $events = $this->eventService->queryDenied($type ? $type : [], $status);
        $events = $events->paginate(20);

        return EventAndRosterResource::collection($events);
    }

    /**
     * List event: alarm on device
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function eventAlarmOnDevice(Request $request)
    {

        $events = Event::queryAlarm();

        $events = $events->get();

        return EventAlarmResource::collection($events);
    }

    /**
     * Create Event
     *
     *
     * @return EventResource|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function store(EventRequest $request)
    {

        $this->validateEventTag($request);
        $event = null;
        \DB::transaction(function () use (&$event, $request) {
            $input = $request->except('users');
            if (! isset($input['end_date'])) {
                $input['end_date'] = Carbon::parse($input['start_date'])->format('Y-m-d 23:59:59');
                $input['show_end_date'] = false;
            }
            //1. Create Event
            $event = Event::create($input);
            //2. Create Event User
            $arContactId = $request->get('users');
            if (! empty($arContactId)) {
                foreach ($arContactId as $uId) {
                    $eventUsers[$uId] = ['status' => config('constant.event.status.waiting')];
                }
                $event->contacts()->attach($eventUsers);
            }
        });
        $user = $this->getUserAuth();
        //TOdo: send email, sms, notification
        if ($event) {
            \event(new EventCreate($event));
        }
        if (! empty($request->users) && count($request->users)) {
            // Send SMS to users who have not used the app
            $list_contact = Contact::whereIn('id', $request->users)->whereNull('user_contact_id')->get();
            if (! empty($list_contact) && count($list_contact)) {
                $params = '?page=home';
                foreach ($list_contact as $contact) {
                    $contact->notify(new SmsUseApp($contact->name ?? '', $user->name, optional($user->setting)->language_code ?? 'en', $params, $event));
                }
            }
        }

        return new EventResource($event);
    }

    /**
     * Create Event
     *
     *
     * @return EventResource|\Illuminate\Http\JsonResponse
     */
    public function update($id, EventRequest $request)
    {

        $this->validateEventTag($request);

        $user = $this->getUserAuth();

        $query = Event::where('id', $id)->where('creator_id', $user->id);
        $event = $query->first();
        if (! $event) {
            return response()->json(['message' => __('event.event_not_exist')], 403);
        }
        \DB::transaction(function () use (&$event, $request, $user) {
            $input = $request->except('users');
            if (! isset($input['end_date'])) {
                $input['end_date'] = Carbon::parse($input['start_date'])->format('Y-m-d 23:59:59');
                $input['show_end_date'] = false;
            }
            //1. Update Event
            $event->update($input);

            //2. Update Event User
            $arContactId = $request->get('users');
            if (! empty($arContactId)) {
                $eventUsers = [];
                // no update status if user exist before
                // ko cap nhat status cho user da them trc do
                $eventOld = [];
                if ($event->contacts->count() > 0) {
                    $eventOld = $event->contacts->pluck('pivot.status', 'id');
                }

                foreach ($arContactId as $uId) {
                    $eventUsers[$uId] = ['status' => isset($eventOld[$uId]) && $eventOld[$uId] == config('constant.event.status.confirm') ? $eventOld[$uId] : config('constant.event.status.waiting')];
                }
                $event->contacts()->sync($eventUsers);
                // Send SMS to users who have not used the app
                $list_contact = Contact::whereIn('id', $arContactId)->whereNull('user_contact_id')->get();

                if (! empty($list_contact) && count($list_contact)) {
                    $params = '?page=event&key=0';
                    foreach ($list_contact as $contact) {
                        $contact->notify(new SmsUseApp($contact->name ?? '', $user->name, optional($user->setting)->language_code ?? 'en', $params, $event));
                    }
                }
            } else {
                $event->contacts()->detach();
            }
        });

        $newEvent = $query->first();

        //TOdo: send email, sms, notification
        \event(new EventUpdate($newEvent));

        EventResource::withoutWrapping();

        return new EventResource($newEvent);
    }

    /**
     * Check validate event - user tags
     *
     *
     * @return mixed
     */
    private function validateEventTag(Request $request)
    {
        $user = $this->getUserAuth();

        $arContactId = $request->get('users');
        if (! empty($arContactId) && count($arContactId) > 0) {
            $countContact = Contact::whereIn('id', $arContactId)->where('user_id', $user->id)->count();
            if ($countContact != count($arContactId)) {
                return response()->json(['message' => __('event.contact_not_exist')], 422);
            }
        }
    }

    /**
     * Delete event
     *
     *
     * @return mixed
     */
    public function destroy($id)
    {
        $user = $this->getUserAuth();

        $event = Event::where('id', $id)->where('creator_id', $user->id)->first();
        if ($event) {
            //TOdo: send email, sms, notification
            $event->delete();

            return 1;
        }

        return 0;
    }

    /**
     * Update status
     *
     *
     * @return EventResource|\Illuminate\Http\JsonResponse
     */
    private function updateStatus($id, $status)
    {
        $user = $this->getUserAuth();

        $event = Event::event()->where('id', $id)
            ->with([
                'contacts' => function ($query) use ($user) {
                    $query->where('user_contact_id', $user->id);
                },
            ])
            ->whereHas('contacts', function (Builder $query) use ($user) {
                $query->where('user_contact_id', $user->id);
            })->first();

        if (! $event) {
            return response()->json(['message' => __('event.event_not_exist')], 403);
        }
        $contactId = null;
        foreach ($event->contacts as $c) {
            if ($c->user_contact_id = $user->id) {
                $contactId = $c->id;
                break;
            }
        }
        if ($contactId) {
            //TOdo: send email, sms, notification
            if ($event->creator && (empty($event->creator->setting) || ! $event->creator->setting->busy_mode)) {
                if ($event->contacts()->wherePivot('status', config('constant.event.status.confirm'))->wherePivot('contact_id', $contactId)->exists() && $status == config('constant.event.status.deny')) {
                    $lang = optional($event->creator?->setting)->language_code;
                    $event->creator->notify(new NotifyEvent($event, $user, EventAction::USER_CANCEL, $lang));
                } else {
                    \event(new EventInteract($event, $user, $status, (int) $contactId));
                }
            }

            $event->contacts()->updateExistingPivot($contactId, ['status' => $status]);
        }
        //TODO tmp
        EventResource::withoutWrapping();

        return new EventResource($event);
    }

    /**
     * confirm event
     *
     *
     * @return EventResource|\Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        $event = Event::findOrFail($id);
        // Is event
        if ($event->isEvent()) {
            return $this->updateStatus($id, config('constant.event.status.confirm'));
        }
        // Task and Project' task
        $event = $this->eventService->employeeAcceptOrDenyTask($id, true);

        return response()->json(new EventResource($event), 200);
    }

    /**
     * deny event
     *
     *
     * @return EventResource|\Illuminate\Http\JsonResponse
     */
    public function deny($id)
    {
        $event = Event::findOrFail($id);
        // Is event
        if ($event->isEvent()) {
            return $this->updateStatus($id, config('constant.event.status.deny'));
        }
        // Task and Project' task
        $event = $this->eventService->employeeAcceptOrDenyTask($id, false);

        return response()->json(new EventResource($event), 200);
    }

    /**
     * deny event
     *
     *
     * @return EventResource|\Illuminate\Http\JsonResponse
     */
    public function leave($id)
    {
        $event = Event::findOrFail($id);
        // Is event
        if ($event->isEvent()) {
            return $this->updateStatus($id, config('constant.event.status.deny'));
        }
        // Task and Project' task
        $event = $this->eventService->employeeAcceptOrDenyTask($id, false);

        return response()->json(new EventResource($event), 200);
    }

    /**
     * Employee set done task/project task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function doneTask($eventTaskID)
    {
        $event = $this->eventService->setDoneTask($eventTaskID);

        return response()->json(new EventResource($event), 200);
    }

    public function addRoomChat($id, Request $request)
    {
        $request->validate([
            'room_chat_id' => 'nullable|string|max:255',
        ]);

        return $this->eventService->addRoomChat($id, $request->room_chat_id);
    }

    public function sendSms(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array',
            'contacts.*' => 'required|integer',
        ]);

        return $this->eventService->sendSmsTo($request->contacts);
    }

    public function sendNotify(Request $request)
    {
        $request->validate([
            'user_cube_ids' => 'required|array',
            'user_cube_ids.*' => 'required|integer',
            'message' => 'required|string',
            'title' => 'required|string',
            'room_chat_id' => 'required|string',
        ]);

        return $this->eventService->sendNotify($request->all());
    }
}
