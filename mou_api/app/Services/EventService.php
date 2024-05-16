<?php

namespace App\Services;

use App\Contact;
use App\Enums\EventType;
use App\Enums\ToDoStatus;
use App\Enums\UserType;
use App\Event;
use App\Jobs\SendNotifyToNextTaskJob;
use App\Notifications\NotifyDoneTask;
use App\Notifications\NotifyEmployeeActionTask;
use App\Notifications\NotifyMsg;
use App\Notifications\SmsUseApp;
use App\Roster;
use App\User;
use DB;
use Illuminate\Database\Eloquent\Builder;

class EventService
{
    private $authService;

    private $companyService;

    private $rosterService;

    public $orderTypeRaw =  "CASE 
    WHEN type = 'PROJECT_TASK' THEN 1 
    WHEN type = 'TASK' THEN 2 
    WHEN type = 'EVENT' THEN 3 
    WHEN type = 'ROSTER' THEN 4 
    END ASC";

    public function __construct(AuthService $authService, CompanyService $companyService, RosterService $rosterService)
    {
        $this->authService = $authService;
        $this->companyService = $companyService;
        $this->rosterService = $rosterService;
    }

    /**
     * Query by date
     *
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function queryByDate(?string $startDate, ?string $userType)
    {
        $user = $this->authService->getUserAuth();
        $events = Event::select('events.*', DB::raw("'' as status, '' as company_employee_id, '' as company_name"))
        ->with('contacts', 'contacts.userContact', 'creator', 'project', 'project.employeeResponsible.contact')
        ->orderByRaw($this->orderTypeRaw)
        ->orderBy('start_date', 'desc');

        // 1. Get Event
        $onlyEvents = $events->clone()->where(function (Builder $query) use ($user, $startDate) {
            $query->where(function (Builder $query) use ($user) {
                // Event của mình tạo hoặc có đồng ý tham gia
                $query->where('type', EventType::EVENT->value);
                $query->where(function (Builder $subQuery) use ($user) {
                    $subQuery->where(function (Builder $query) use ($user) {
                        $query->where('creator_id', $user->id)->where(function (Builder $query) use ($user) {
                            $query->whereDoesntHave('contacts')->orWhereHas('contacts', function (Builder $query) use ($user) {
                                $query->where('user_contact_id', '<>', $user->id)->orWhereNull('user_contact_id');
                            });
                        });
                    })->orWhereHas('contacts', function (Builder $query) use ($user) {
                        $query->where('user_contact_id', $user->id)->where('status', '<>', config('constant.event.status.deny'));
                    });
                });
            });
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($qu) use ($startDate) {
                    $qu->whereDate('start_date', '<=', $startDate)
                        ->whereDate('end_date', '>=', $startDate);
                })->orWhere(function ($qu) use ($startDate) {
                    $qu->whereDate('start_date', $startDate)
                        ->whereNull('end_date');
                });
            });
        });
        $otherTypes = $events->clone()->where(function (Builder $query) use ($user, $startDate, $userType) {
            $query->where(function (Builder $query) use ($user, $userType) {
                if ($userType == UserType::BUSINESS) {
                    $query->where(function (Builder $subQuery) use ($user) {
                        $subQuery->where(function (Builder $query) use ($user) {
                            $query->where('creator_id', $user->id)->where(function (Builder $query) use ($user) {
                                $query->whereDoesntHave('project.employeeResponsible.contact')->orWhereHas('project.employeeResponsible.contact', function (Builder $query) use ($user) {
                                    $query->where('user_contact_id', '<>', $user->id)->orWhereNull('user_contact_id');
                                });
                            });
                        })->orWhere(function (Builder $query) use ($user) {
                            $query->whereHas('project.employeeResponsible', function ($subQuery) {
                                $subQuery->where('employee_confirm', '<>', config('constant.event.status.deny'));
                            })->whereHas('project.employeeResponsible.contact', function ($subQuery) use ($user) {
                                $subQuery->where('user_contact_id', $user->id);
                            });
                        });
                    })
                        ->where('type', '!=', EventType::EVENT->value);
                } else {
                    $query->where('type', '!=', EventType::EVENT->value)
                        ->whereDoesntHave('contactDenies')
                        ->where(function (Builder $subQuery) use ($user) {
                            $subQuery->whereHas('contacts', function (Builder $query) use ($user) {
                                $query->where('user_contact_id', $user->id);
                            })->orWhereHas('project.employeeResponsible.contact', function ($subQuery) use ($user) {
                                $subQuery->where('user_contact_id', $user->id);
                            });
                        });
                }
            });
            $query->where(function ($q) use ($startDate) {
                $q->where(function ($qu) use ($startDate) {
                    $qu->whereDate('start_date', '<=', $startDate)
                        ->whereDate('end_date', '>=', $startDate);
                })->orWhere(function ($qu) use ($startDate) {
                    $qu->whereDate('start_date', $startDate)
                        ->whereNull('end_date');
                });
            });
        });
        $newEvents = $onlyEvents->union($otherTypes);

        $rosters = Roster::with(['employee', 'creator'])->where('status', '<>', config('constant.event.status.deny'))
            ->where(function (Builder $query) use ($user, $userType) {
                $query->where(function (Builder $query) use ($user, $userType) {
                    if ($userType == UserType::BUSINESS) {
                        $query->where('rosters.creator_id', $user->id);
                    } else {
                        $query->where('rosters.creator_id', $user->id)
                            ->orWhereHas('employee.contact', function ($q) use ($user) {
                                $q->where('user_contact_id', $user->id);
                            });
                    }
                });
            })
            ->select(
                DB::raw("rosters.id as id, NULL as title, start_time as start_date, end_time as end_date,NULL as show_end_date, NULL as comment, NULL as 'repeat', NULL as alarm, NULL as place, NULL as busy_mode,rosters.creator_id as creator_id, rosters.created_at as created_at,rosters.updated_at as updated_at, NULL as deleted_at, NULL as chat, 'ROSTER' as type, NULL as company_id, NULL as project_id, NULL as done_time, NULL as room_chat_id, rosters.store_id as store_id, rosters.status,rosters.company_employee_id ,companies.name as company_name")
            )
            ->whereDate('start_time', '<=', $startDate)
            ->whereDate('end_time', '>=', $startDate)
            ->join('company_employees', 'company_employees.id', '=', 'rosters.company_employee_id')
            ->join('companies', 'companies.id', '=', 'company_employees.company_id');

        return $newEvents->union($rosters)->orderByRaw($this->orderTypeRaw)->orderBy('start_date', 'desc');
    }

    /**
     * ---------------------------
     * Query For you confirm - Get Event/Task/Project's task
     * ---------------------------
     */
    public function queryForYouToConfirm(?array $type, ?string $status)
    {
        $user = $this->authService->getUserAuth();
        $event = Event::select('events.*', \DB::raw('NULL as status, NULL as company_employee_id, NULL as company_name'))
            ->with('contacts', 'contacts.userContact', 'creator', 'project', 'creator.company', 'store')
            ->orderBy('start_date', 'desc')
            ->orderBy('type');
        // 1. Get Event
        $event = $event->where(function (Builder $query) use ($user) {
            $query->whereHas('contactWaitings', function (Builder $query) use ($user) {
                $query->where('user_contact_id', $user->id);
            });
            $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
                config('constant.event.status.confirm'),
                false
            );
            $arMyEmployeeID = $myEmployeeCompanies ? $myEmployeeCompanies->pluck('id') : [];
            $query->orWhere(function (Builder $query) use ($arMyEmployeeID) {
                $query->whereHas('employeeWaitings', function (Builder $query) use ($arMyEmployeeID) {
                    $query->whereIn('employee_id', $arMyEmployeeID);
                });
            });
        });

        $event->when(
            $status,
            fn ($query) => $status == ToDoStatus::DONE->value ?
                $query->whereNotNull('done_time') : $query->whereNull('done_time')
        )->eventType($type);
        if (($type && in_array(EventType::ROSTER->value, $type))) {
            $nowDate = date('Y-m-d');
            $rosters = $this->rosterService
                ->queryRosterByEmployee($user->id, config('constant.event.status.waiting'))
                ->select(
                    DB::raw("rosters.id as id, NULL as title, start_time as start_date, end_time as end_date,NULL as show_end_date, NULL as comment, NULL as 'repeat', NULL as alarm, NULL as place, NULL as busy_mode,rosters.creator_id as creator_id, rosters.created_at as created_at,rosters.updated_at as updated_at, NULL as deleted_at, NULL as chat, 'ROSTER' as type, NULL as company_id, NULL as project_id, NULL as done_time, NULL as room_chat_id, rosters.store_id as store_id, rosters.status,rosters.company_employee_id ,companies.name as company_name")
                )
                ->where(function ($q) use ($nowDate) {
                    $q->whereDate('start_time', '>=', $nowDate)
                        ->orWhereDate('end_time', '>=', $nowDate);
                })
                ->join('company_employees', 'company_employees.id', '=', 'rosters.company_employee_id')
                ->join('companies', 'companies.id', '=', 'company_employees.company_id');

            return $event->union($rosters)->orderByRaw($this->orderTypeRaw)->orderBy('start_date', 'desc');
        }

        return $event->orderByRaw($this->orderTypeRaw)->orderBy('start_date', 'desc');
    }

    /**
     * Query Confirmed - Get Event/Task/Project's task
     *
     * @return Builder
     */
    public function queryConfirmed(?array $type, ?string $status, ?string $userType)
    {
        $user = $this->authService->getUserAuth();

        $event = Event::select('events.*', \DB::raw("NULL as status, NULL as company_employee_id, '' as company_name"))
            ->with('contacts', 'contacts.userContact', 'creator', 'project', 'store')
            ->orderBy('start_date', 'desc')
            ->orderBy('type');
        // 1. Get Event
        $onlyEvents = $event->clone()->where(function (Builder $query) use ($user) {
            $query->where('type', EventType::EVENT->value);
            $query->where(function (Builder $query) use ($user) {
                // Event của mình tạo và tất cả mọi người đã đồng ý hoặc từ chối (ko có ai đang chờ)
                $query->where('creator_id', $user->id)
                    ->whereDoesntHave('contactWaitings')
                    ->whereDoesntHave('contactDenies');
            })->orWhereHas('contactConfirms', function (Builder $query) use ($user) {
                // Event mình đã đồng ý tham gia
                $query->where('user_contact_id', $user->id);
            });
            $myEmployeeCompanies = $this->companyService
                ->getCompanyHadInvitedToMe(config('constant.event.status.confirm'), false);
            $arMyEmployeeID = $myEmployeeCompanies ? $myEmployeeCompanies->pluck('id') : [];
            $query->orWhere(function (Builder $query) use ($arMyEmployeeID) {
                $query->whereHas('employeeConfirms', function (Builder $query) use ($arMyEmployeeID) {
                    $query->whereIn('employee_id', $arMyEmployeeID);
                });
            });
        });
        $otherTypes = $event->clone()->where(function (Builder $query) use ($user, $userType) {
            $query->where('type', '!=', EventType::EVENT->value);
            $query->where(function (Builder $query) use ($user, $userType) {
                if ($userType == UserType::BUSINESS) {
                    $query->where('creator_id', $user->id)
                        ->whereDoesntHave('contactDenies')
                        ->whereDoesntHave('contactWaitings');
                } else {
                    $query->whereDoesntHave('contactDenies')
                        ->whereDoesntHave('contactWaitings')
                        ->whereHas('contactConfirms', function (Builder $query) use ($user) {
                            $query->where('user_contact_id', $user->id);
                        });
                }
            });
            $myEmployeeCompanies = $this->companyService
                ->getCompanyHadInvitedToMe(config('constant.event.status.confirm'), false);
            $arMyEmployeeID = $myEmployeeCompanies ? $myEmployeeCompanies->pluck('id') : [];
            $query->orWhere(function (Builder $query) use ($arMyEmployeeID) {
                $query->whereHas('employeeConfirms', function (Builder $query) use ($arMyEmployeeID) {
                    $query->whereIn('employee_id', $arMyEmployeeID);
                });
            });
        });
        $newEvents = $onlyEvents->eventType($type)->union($otherTypes->eventType($type));
        $newEvents = $newEvents
            ->when(
                $status,
                fn ($query) => $status == ToDoStatus::DONE->value ?
                    $query->whereNotNull('done_time') : $query->whereNull('done_time')
            );
        if ($type && in_array(EventType::ROSTER->value, $type)) {
            $rosters = $this->rosterService
                ->queryRosterByUser($user->id, config('constant.event.status.confirm'))
                ->select(
                    DB::raw("rosters.id as id, NULL as title, start_time as start_date, end_time as end_date,NULL as show_end_date, NULL as comment, NULL as 'repeat', NULL as alarm, NULL as place, NULL as busy_mode,rosters.creator_id as creator_id, rosters.created_at as created_at,rosters.updated_at as updated_at, NULL as deleted_at, NULL as chat, 'ROSTER' as type, NULL as company_id, NULL as project_id, NULL as done_time, NULL as room_chat_id, rosters.store_id as store_id, rosters.status,rosters.company_employee_id ,companies.name as company_name")
                )
                ->join('company_employees', 'company_employees.id', '=', 'rosters.company_employee_id')
                ->join('companies', 'companies.id', '=', 'company_employees.company_id');

            return $newEvents->union($rosters)->orderBy('start_date', 'desc');
        }

        return $newEvents->orderBy('start_date', 'desc');
    }

    /**
     * Query waiting to confirm - Get Event/Task/Project's task
     *
     * @return mixed
     */
    public function queryWaitingToConfirmPersonal(?array $type, ?string $status)
    {
        $user = $this->authService->getUserAuth();

        return Event::with('contacts', 'contacts.userContact', 'creator', 'store')
            ->inFuture()->orderBy('start_date', 'desc')
            ->where('creator_id', $user->id)
            ->whereHas('contactWaitings')
            ->eventType($type)
            ->when(
                $status,
                fn ($query) => $status == ToDoStatus::DONE->value ?
                    $query->whereNotNull('done_time') : $query->whereNull('done_time')
            )->orderByRaw($this->orderTypeRaw)->orderBy('start_date', 'desc');
    }

    public function queryWaitingToConfirmBusiness(?array $type, ?string $status)
    {
        $user = $this->authService->getUserAuth();

        $event = Event::select('events.*', \DB::raw('NULL as status, NULL as company_employee_id, NULL as company_name'))
            ->with('contacts', 'contacts.userContact', 'creator', 'project', 'creator.company', 'store')
            ->inFuture()->orderBy('start_date', 'desc')
            ->where('creator_id', $user->id)
            ->whereHas('contactWaitings')
            ->eventType($type)
            ->when(
                $status,
                fn ($query) => $status == ToDoStatus::DONE->value ?
                    $query->whereNotNull('done_time') : $query->whereNull('done_time')
            );
        if ($type && in_array(EventType::ROSTER->value, $type)) {
            $rosters = $this->rosterService
                ->queryRosterByUser($user->id, config('constant.event.status.waiting'))
                ->select(
                    DB::raw("rosters.id as id, NULL as title, start_time as start_date, end_time as end_date, NULL as show_end_date, NULL as comment, NULL as 'repeat', NULL as alarm, NULL as place, NULL as busy_mode,rosters.creator_id as creator_id, rosters.created_at as created_at,rosters.updated_at as updated_at, NULL as deleted_at, NULL as chat, 'ROSTER' as type, NULL as company_id, NULL as project_id, NULL as done_time, NULL as room_chat_id, rosters.store_id as store_id, rosters.status,rosters.company_employee_id ,companies.name as company_name")
                )
                ->join('company_employees', 'company_employees.id', '=', 'rosters.company_employee_id')
                ->join('companies', 'companies.id', '=', 'company_employees.company_id');

            return $event->union($rosters)->orderByRaw($this->orderTypeRaw)->orderBy('start_date', 'desc');
        }

        return $event->orderByRaw($this->orderTypeRaw)->orderBy('start_date', 'desc');
    }

    public function queryDenied(?array $type, ?string $status)
    {
        $user = $this->authService->getUserAuth();

        $event = Event::select('events.*', \DB::raw('NULL as status, NULL as company_employee_id, NULL as company_name'))
            ->with('contacts', 'contactDenies', 'contacts.userContact', 'creator', 'project', 'creator.company', 'store')
            ->orderBy('start_date', 'desc')
            ->where('creator_id', $user->id)
            ->whereHas('contactDenies')
            ->eventType($type)
            ->when(
                $status,
                fn ($query) => $status == ToDoStatus::DONE->value ?
                    $query->whereNotNull('done_time') : $query->whereNull('done_time')
            );
        if ($type && in_array(EventType::ROSTER->value, $type)) {
            $rosters = $this->rosterService
                ->queryRosterByUser($user->id, config('constant.event.status.deny'))
                ->select(
                    DB::raw("rosters.id as id, NULL as title, start_time as start_date, end_time as end_date,NULL as show_end_date, NULL as comment, NULL as 'repeat', NULL as alarm, NULL as place, NULL as busy_mode,rosters.creator_id as creator_id, rosters.created_at as created_at,rosters.updated_at as updated_at, NULL as deleted_at, NULL as chat, 'ROSTER' as type, NULL as company_id, NULL as project_id, NULL as done_time, NULL as room_chat_id, rosters.store_id as store_id, rosters.status,rosters.company_employee_id ,companies.name as company_name")
                )
                ->join('company_employees', 'company_employees.id', '=', 'rosters.company_employee_id')
                ->join('companies', 'companies.id', '=', 'company_employees.company_id');

            return $event->union($rosters)->orderBy('start_date', 'desc');
        }

        return $event->orderBy('start_date', 'desc');
    }

    /**
     * Employee accept or deny the task (of company)
     *
     * @param  bool  $isAccept
     * @return mixed
     */
    public function employeeAcceptOrDenyTask($id, $isAccept = true)
    {
        $user = $this->authService->getUserAuth();
        // Get my companies that i accepted
        $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
            config('constant.event.status.confirm'),
            false
        );
        if (!$myEmployeeCompanies || $myEmployeeCompanies->count() == 0) {
            abort(500, 'Task is not exists!');
        }
        $event = Event::where('id', $id)->firstOrFail();
        if ($event->employees) {
            $newStatus = $isAccept ? config('constant.event.status.confirm') : config('constant.event.status.deny');
            $arMyEmployeeId = $myEmployeeCompanies->pluck('id')->toArray();
            foreach ($event->employees as $employee) {
                if (in_array($employee->id, $arMyEmployeeId) && $employee->pivot->status != $newStatus) {
                    $event->employees()->updateExistingPivot($employee->id, ['status' => $newStatus]);
                }
            }
            // Todo: notification
            $creator = $event->creator;
            // if creator exists and type event is task or project task then take action
            if ($creator && $event->type != config('constant.event.type.event')) {
                // get name from list employees
                $employees_name = $myEmployeeCompanies->pluck('contact.userContact.name')->toArray();
                foreach ($employees_name as $index => $item) {
                    // unset item in array if name is null
                    if (!$item) {
                        unset($employees_name[$index]);
                    }
                }
                $employees_name = implode(', ', $employees_name);
                // send notify to creator
                $lang = optional($creator->setting)->language_code;
                $creator->notify(new NotifyEmployeeActionTask($event, $newStatus, $employees_name, $user, $lang));
            }
        }

        return $event;
    }

    public function setDoneTask($eventTaskID)
    {
        // Get my companies that i accepted
        $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
            config('constant.event.status.confirm'),
            false
        );
        if (!$myEmployeeCompanies || $myEmployeeCompanies->count() == 0) {
            abort(500, 'Task is not exists!');
        }

        $event = Event::with('creator')->where('id', $eventTaskID)->firstOrFail();
        // Check belongs to employee
        $hasPermission = false;
        if ($event->employees) {
            $arMyEmployeeId = $myEmployeeCompanies->pluck('id')->toArray();
            foreach ($event->employees as $employee) {
                if (in_array($employee->id, $arMyEmployeeId)) {
                    if ($employee->pivot->status != config('constant.event.status.confirm')) {
                        abort(500, 'You need to click on accept the task before done it!');
                    }
                    $hasPermission = true;
                    break;
                }
            }

            // Save done
            if ($hasPermission && empty($event->done_time)) {
                $event->update(['done_time' => now()]);
                $user = $this->authService->getUserAuth();

                // Todo: notification
                // exception case user deleted
                $creator = $event->creator;
                if ($creator) {
                    $lang = optional($creator->setting)->language_code;
                    $creator->notify(new NotifyDoneTask($user, $event, $lang));
                }
                $teams = optional($event->project)->teams;
                if (!empty($teams) && count($teams)) {
                    foreach ($teams as $item) {
                        $userContact = $item->contact->userContact;
                        // Send notifications to all members except those completing task
                        if ($userContact && $userContact->id != $user->id) {
                            $lang = optional($userContact->setting)->language_code;
                            $userContact->notify(new NotifyDoneTask($user, $event, true, $lang));
                        }
                    }
                }
                if ($event->type == config('constant.event.type.project_task')) {
                    $project = $event->project;
                    $project->loadMissing('tasks');
                    SendNotifyToNextTaskJob::dispatch($event, $project, $user);
                }
            }
        }
        if (!$hasPermission) {
            abort(500, 'You are not authorized!');
        }

        return $event;
    }

    /**
     * Kiểm tra những event trong ngày đã đồng ý hết chưa
     * hoặc có tồn tại task trong ngày hay ko
     *
     * @return array
     */
    public function checkEventAndTaskInDateOfMonth($startDay, $endDate, $type)
    {
        // Prepare
        $user = $this->authService->getUserAuth();
        // $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
        //     config('constant.event.status.confirm'),
        //     false
        // );
        $colors = config('constant.event.colors.' . $type);
        /**
         * Kiểm tra những event trong ngày đã đồng ý hết chưa
         */
        $events = Event::select(\DB::raw('DATE(start_date) as date, creator_id, type, start_date, end_date, id'))
            ->withCount('contactWaitings')->with('project.employeeResponsible.contact')
            ->where(function ($q) use ($startDay, $endDate) {
                $q->where(function ($query) use ($startDay, $endDate) {
                    $query->whereDate('start_date', '<=', $startDay)
                        ->whereDate('end_date', '>=', $endDate);
                })
                    ->orWhereBetween(DB::raw('DATE(`start_date`)'), [$startDay, $endDate])
                    ->orWhereBetween(DB::raw('DATE(`end_date`)'), [$startDay, $endDate]);
            });

        $eventConfirms = $this->getEventByStatus($events, 'contactConfirms', $type);
        $eventWaitings = $this->getEventByStatus($events, 'contactWaitings', $type);

        $rosters = Roster::with(['employee', 'creator'])->where('status', '<>', config('constant.event.status.deny'))
            ->where(function (Builder $query) use ($user, $type) {
                $query->where(function (Builder $query) use ($user, $type) {
                    if ($type == UserType::BUSINESS) {
                        $query->where('rosters.creator_id', $user->id);
                    } else {
                        $query->where('rosters.creator_id', $user->id)
                            ->orWhereHas('employee.contact', function ($q) use ($user) {
                                $q->where('user_contact_id', $user->id);
                            });
                    }
                });
            })
            ->select(\DB::raw('DATE(start_time) as date, "ROSTER" as type, creator_id, start_time as start_date, end_time as end_date, 0 as contact_waitings_count')) // add contact waiting count "like event" to calculator render color
            ->where(function ($q) use ($startDay, $endDate) {
                $q->where(function ($query) use ($startDay, $endDate) {
                    $query->whereDate('start_time', '<=', $startDay)
                        ->whereDate('end_time', '>=', $endDate);
                })
                    ->orWhereBetween(DB::raw('DATE(`start_time`)'), [$startDay, $endDate])
                    ->orWhereBetween(DB::raw('DATE(`start_time`)'), [$startDay, $endDate]);
            });
        $rosterWaitings = $rosters->clone()->where('status', config('constant.event.status.waiting'))->get()->toArray();
        $rosterConfirms = $rosters->clone()->where('status', config('constant.event.status.confirm'))->get()->toArray();
        $begin = new \DateTime($startDay);
        $end = new \DateTime(date('Y-m-d', strtotime($endDate . '+1 days')));
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);
        $data = [];
        foreach ($period as $dt) {
            $day = $dt->format('Y-m-d');
            $eventConfirmsDay = $this->filterByDate($eventConfirms, $day);
            $eventWaitingsDay = $this->filterByDate($eventWaitings, $day);
            $rosterConfirmsDay = $this->filterByDate($rosterConfirms, $day);
            $rosterWaitingsDay = $this->filterByDate($rosterWaitings, $day);
            if (
                count($eventConfirmsDay) || count($eventWaitingsDay)
                || count($rosterConfirmsDay) || count($rosterWaitingsDay)
            ) {
                if (count($eventConfirmsDay) && !count($eventWaitingsDay)) {
                    if ((count($rosterConfirmsDay) && !count($rosterWaitingsDay))
                        || (!count($rosterConfirmsDay) && !count($rosterWaitingsDay))
                    ) {
                        $data[$day] = data_get($colors, 'not_waiting');
                    } else {
                        $data[$day] = data_get($colors, 'waiting');
                    }
                } elseif (!count($eventConfirmsDay) && !count($eventWaitingsDay)) {
                    if ((count($rosterConfirmsDay) && !count($rosterWaitingsDay))) {
                        $data[$day] = data_get($colors, 'not_waiting');
                    } else {
                        $data[$day] = data_get($colors, 'waiting');
                    }
                } else {
                    $data[$day] = data_get($colors, 'waiting');
                }
            }
        }

        return $data;
    }

    public function filterByDate($data, $date)
    {
        return array_filter($data, function ($item) use ($date) {
            if (!data_get($item, 'end_date')) {
                return date('Y-m-d', strtotime(data_get($item, 'start_date'))) == $date;
            }

            return date('Y-m-d', strtotime(data_get($item, 'start_date'))) <= $date
                && date('Y-m-d', strtotime(data_get($item, 'end_date'))) >= $date;
        });
    }

    public function addRoomChat($id, $room_id)
    {
        $event = Event::find($id);
        if ($room_id) {
            if (!$event) {
                abort(404, __('event.event_not_exist'));
            }
            $event->update([
                'room_chat_id' => $room_id,
            ]);
        }

        return $event;
    }

    public function sendSmsTo($arrContact)
    {
        $user = $this->authService->getUserAuth();
        // Send SMS to users who have not used the app
        $contacts = Contact::whereIn('id', $arrContact)->whereNull('user_contact_id')->get();
        if (!empty($contacts) && count($contacts)) {
            foreach ($contacts as $contact) {
                $contact->notify(new SmsUseApp($contact->name ?? '', $user->name, optional($user->setting)->language_code ?? 'en'));
            }
        }

        return ['message' => 'Sent a message'];
    }

    public function sendNotify($data)
    {
        $user = $this->authService->getUserAuth();
        $list_users = User::whereIn('connectycube_id', $data['user_cube_ids'])->where('id', '<>', $user->id)->get();
        if (!empty($list_users) && count($list_users)) {
            foreach ($list_users as $item) {
                $item->notify(new NotifyMsg($data, $user));
            }
        }

        return ['message' => 'OK'];
    }

    private function getEventByStatus(Builder $query, $status, $type): array
    {
        $user = $this->authService->getUserAuth();
        $onlyEvents = $query->clone()->where(function ($query) use ($status) {
            if ($status == 'contactWaitings') {
                $query->whereHas($status);
            } else {
                $query->whereDoesntHave('contacts')->orWhere(function (Builder $query) {
                    $query->whereDoesntHave('contactWaitings')->whereDoesntHave('contactDenies');
                });
            }
        })->where(function ($query) use ($user) {
            $query->where('type', EventType::EVENT->value)
                ->whereDoesntHave('contactDenies');
            $query->where(function (Builder $subQuery) use ($user) {
                $subQuery->where('creator_id', $user->id)->orWhereHas('contacts', function (Builder $query) use ($user) {
                    $query->where('user_contact_id', $user->id);
                });
            });
        });

        $otherTypes = $query->clone()->where(function ($query) use ($status) {
            if ($status == 'contactWaitings') {
                $query->whereHas($status);
            } else {
                $query->whereDoesntHave('contacts')->orWhere(function (Builder $query) {
                    $query->whereDoesntHave('contactWaitings')->whereDoesntHave('contactDenies');
                });
            }
        })->where(function (Builder $query) use ($user, $type) {
            if ($type == UserType::BUSINESS) {
                $query->where('type', '!=', EventType::EVENT->value)
                    ->whereDoesntHave('contactDenies')
                    ->where(function (Builder $subQuery) use ($user) {
                        $subQuery->where('creator_id', $user->id)->orWhereHas('project.employeeResponsible.contact', function ($subQuery) use ($user) {
                            $subQuery->where('user_contact_id', $user->id);
                        });
                    });
            } else {
                $query->where('type', '!=', EventType::EVENT->value)
                    ->whereDoesntHave('contactDenies')
                    ->where(function (Builder $subQuery) use ($user) {
                        $subQuery->whereHas('contacts', function (Builder $query) use ($user) {
                            $query->where('user_contact_id', $user->id);
                        })->orWhereHas('project.employeeResponsible.contact', function ($subQuery) use ($user) {
                            $subQuery->where('user_contact_id', $user->id);
                        });
                    });
            }
        });
        if ($type == UserType::BUSINESS) {
            return $otherTypes->get()->toArray();
        }

        return $onlyEvents->get()->merge($otherTypes->get())->toArray();
    }
}
