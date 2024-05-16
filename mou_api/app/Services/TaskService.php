<?php

namespace App\Services;

use App\CompanyEmployee;
use App\Enums\BusinessType;
use App\Enums\StatusEvent;
use App\Enums\TaskAndProjectAction;
use App\Enums\UserType;
use App\Event;
use App\Notifications\NotifyMyTask;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;

class TaskService
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Create task
     *
     *
     * @return null
     *
     * @throws \Throwable
     */
    public function createTask(int $companyID, array $data)
    {
        // Check permission: is employee belongs to company or company creator
        $this->authService->checkPermissionAddInCompany($companyID, 'permission_add_task');

        // Create task
        $user = $this->authService->getUserAuth();
        $data['company_id'] = $companyID;
        $data['creator_id'] = $user->id;
        $data['type'] = config('constant.event.type.task');

        // Create task
        $task = Event::create($data);

        // add task employee
        $taskEmployees = [];
        $arEmployee = array_unique($data['employees']);
        foreach ($arEmployee as $employeeID) {
            $taskEmployees[$employeeID] = ['status' => config('constant.event.status.waiting'), 'contact_id' => CompanyEmployee::find($employeeID)->contact_id];
        }
        $task->employees()->attach($taskEmployees);

        // Notification
        if (count($arEmployee)) {
            $listContacts = $task->contactNoDenies()->get();
            if (! empty($listContacts) && count($listContacts)) {
                foreach ($listContacts as $item) {
                    $employee = $item->userContact;
                    if ($employee) {
                        $lang = optional($employee?->setting)->language_code;
                        $employee->notify(new NotifyMyTask($task, config('constant.event.type.task'), TaskAndProjectAction::TASK_CREATE, $lang));
                    }
                }
            }
        }

        return $task;
    }

    /**
     * Edit task
     *
     *
     * @return mixed
     */
    public function updateTask($companyID, $taskID, array $data)
    {
        $event = Event::task()->where('company_id', $companyID)->where('id', $taskID)->firstOrFail();
        // Check permission: is employee belongs to company or company creator
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $event);

        // Edit task
        $event->update($data);

        // update task employee
        $arEmployeeStatus = [];
        if ($event->employees) {
            foreach ($event->employees as $employee) {
                $arEmployeeStatus[$employee->id] = $employee->pivot->status; // Hold employee status
            }
        }

        $taskEmployees = [];
        $arEmployee = array_unique($data['employees']);
        foreach ($arEmployee as $employeeID) {
            $taskEmployees[$employeeID] = ['status' => isset($arEmployeeStatus[$employeeID]) && $arEmployeeStatus[$employeeID] != config('constant.event.status.deny') ? $arEmployeeStatus[$employeeID] : config('constant.event.status.waiting'), 'contact_id' => CompanyEmployee::find($employeeID)->contact_id];
        }
        $oldTeams = optional($event->employees())->select('id')->pluck('id')->toArray();
        $event->employees()->sync($taskEmployees);

        // Notify
        $diffArr = array_diff($arEmployee, $oldTeams);
        if (count($diffArr)) {
            $listContacts = $event->contactNoDenies()->get();
            if (! empty($listContacts) && count($listContacts)) {
                foreach ($listContacts as $item) {
                    $employee = $item->userContact;
                    if ($employee) {
                        $lang = optional($employee?->setting)->language_code;
                        $employee->notify(new NotifyMyTask($event, config('constant.event.type.task'), TaskAndProjectAction::TASK_EDIT, $lang));
                    }
                }
            }
        }

        return Event::withCount('contactDenies')->find($taskID); // call again - because need load employees again.
    }

    /**
     * Delete task
     *
     *
     * @return mixed
     */
    public function deleteTask($companyID, $taskID)
    {
        $event = Event::task()->where('company_id', $companyID)->where('id', $taskID)->firstOrFail();
        // Check permission: is employee belongs to company or company creator
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $event);

        return $event->delete();
    }

    /**
     * Get task detail by id
     *
     * @return mixed
     */
    public function getDetail($companyID, $taskID)
    {
        // Check permission
        $this->authService->checkBelongToCompany($companyID);

        // get task
        return Event::task()->withCount('contactDenies')->where('company_id', $companyID)->where('id', $taskID)->firstOrFail();
    }

    /**
     * Get tasks list by status
     *
     * @param  string  $status  = open|progress|done
     * @return mixed
     */
    public function getTaskByStatus($companyID = null, $status = StatusEvent::OPEN, $isUnion = false, $userType = null, $type = null)
    {
        // Check permission
        if (! $isUnion) {
            $this->authService->checkBelongToCompany($companyID);
        }
        $user = $this->authService->getUserAuth();
        $nowDate = Carbon::now()->format('Y-m-d');

        $tasks = Event::select('id', 'title', \DB::raw("'TASK' AS type"), 'start_date', 'end_date', 'company_id', 'creator_id', 'project_id', 'store_id', 'comment', 'show_end_date')->with('project');

        switch ($status) {
            case StatusEvent::DONE:
                // task is done.
                $tasks = $tasks->where(function ($query) use ($nowDate) {
                    $query->where(function ($query) use ($nowDate) {
                        // case 1: end date is exists
                        $query->whereNotNull('end_date')->whereDate('end_date', '<', $nowDate);
                    });
                });
                if ($type == BusinessType::PROJECT_TASK) {
                    $tasks = $tasks->whereHas('project', function ($query) use ($nowDate) {
                        $query->whereNotNull('end_date')->whereDate('end_date', '<', $nowDate);
                    });
                }
                break;
            case StatusEvent::PROGRESS:
                // task is in-progress.
                $tasks = $tasks->where(function ($query) use ($nowDate) {
                    $query->where(function ($query) use ($nowDate) {
                        // case 1: end date is exists
                        $query->whereDate('start_date', '<=', $nowDate)->whereNotNull('end_date')->whereDate(
                            'end_date',
                            '>=',
                            $nowDate
                        );
                    })->orWhere(function ($query) use ($nowDate) {
                        // case 2: end date not exists and repeat is null
                        $query->whereNull('end_date')->whereDate('start_date', '<=', $nowDate);
                    });
                });
                if ($type == BusinessType::PROJECT_TASK) {
                    $tasks = $tasks->whereHas('project', function ($query) use ($nowDate) {
                        $query->whereNotNull('end_date')->whereDate('end_date', '>=', $nowDate);
                    });
                }
                break;
            default:
                // task is open (not running)
                $tasks = $tasks->whereDate('start_date', '>', $nowDate);
        }
        if ($isUnion) {
            $tasks = $type == BusinessType::PROJECT_TASK ? $tasks->projectTask()->addSelect(\DB::raw("'PROJECT_TASK' AS type")) : $tasks->task();

            return $userType == UserType::BUSINESS ? $this->queryByBusiness($tasks, $user) : $this->queryByPersonal($tasks, $user);
        }

        return $tasks->task()->where(
            'company_id',
            $companyID
        )->latest()->paginate();
    }

    /**
     * query user type == business
     */
    public function queryByBusiness($tasks, $user): Builder
    {
        return $tasks->with(['creator', 'contacts', 'contactConfirms', 'company', 'store', 'project.employeeResponsible.contact'])->where(function ($query) use ($user) {
            $query->where('creator_id', $user->id)->orWhereHas('contactConfirms', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            })->orWhereHas('project.employeeResponsible.contact', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            });
        });
    }

    /**
     * query user type == personal
     */
    public function queryByPersonal($tasks, $user): Builder
    {
        return $tasks->with(['creator', 'contacts', 'contactConfirms', 'company', 'project', 'store', 'project.employeeResponsible.contact'])->where(function ($query) use ($user) {
            $query->whereHas('contactConfirms', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            })->orWhereHas('project.employeeResponsible.contact', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            });
        });
    }
}
