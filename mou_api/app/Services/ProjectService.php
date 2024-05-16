<?php

namespace App\Services;

use App\CompanyEmployee;
use App\Enums\StatusEvent;
use App\Enums\TaskAndProjectAction;
use App\Enums\UserType;
use App\Event;
use App\Notifications\NotifyLeaveProject;
use App\Notifications\NotifyMyTask;
use App\Notifications\NotifyProject;
use App\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Twilio\TwiML\Voice\Task;

class ProjectService
{
    private $authService;

    private $companyService;

    public function __construct(AuthService $authService, CompanyService $companyService)
    {
        $this->authService = $authService;
        $this->companyService = $companyService;
    }

    /**
     * Create project
     *
     *
     * @return null
     *
     * @throws \Throwable
     */
    public function createProject(int $companyId, array $data)
    {
        // Check permission: is employee belongs to company or company creator
        $this->authService->checkPermissionAddInCompany($companyId, 'permission_add_project');

        $user = $this->authService->getUserAuth();
        $project = null;
        \DB::transaction(function () use ($companyId, $data, $user, &$project) {
            // Create project
            $project = Project::create([
                'company_id' => $companyId,
                'creator_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'client' => $data['client'],
                'employee_responsible_id' => $data['employee_responsible_id'],
            ]);

            // Add Team to project
            $project->teams()->attach($data['teams']);

            // Add Tasks to project
            foreach ($data['tasks'] as $taskData) {
                $taskData['company_id'] = $companyId;
                $taskData['creator_id'] = $user->id;
                $taskData['type'] = config('constant.event.type.project_task');
                $taskData['project_id'] = $project->id;

                // Create task
                $task = Event::create($taskData);

                // add task employee
                $taskEmployees = [];
                $arEmployee = array_unique($taskData['employees']);
                foreach ($arEmployee as $employeeID) {
                    $taskEmployees[$employeeID] = ['status' => config('constant.event.status.waiting'), 'contact_id' => CompanyEmployee::find($employeeID)->contact_id];
                }
                $task->employees()->attach($taskEmployees);
            }

            // Set start_date and end date of a project
            $project = $this->updateStartAndEndDateOfProject($project);
        });

        // TODO:: notification when create project success
        if ($project) {
            // get employee responsible
            $userResponse = optional($project->employeeResponsible->contact)->userContact;
            // if user responsible exists then notify to user
            if ($userResponse) {
                $lang = optional($userResponse->setting)->language_code;
                $userResponse->notify(new NotifyProject($project, 'create', 'responsible', $lang));
            }
            // if employee in team exists then notify to all employee in team
            /* if (! empty($project->teams) && count($project->teams)) {
                foreach ($project->teams as $employee) {
                    $contact = optional($employee->contact)->userContact;
                    if ($contact) {
                        $contact->notify(new NotifyProject($project, 'create'));
                    }
                }
            } */
        }

        return $project;
    }

    /**
     * Edit project - not update task in here
     *
     *
     * @return mixed
     */
    public function updateProject($companyID, $projectID, array $data)
    {
        $project = Project::where('id', $projectID)->where('company_id', $companyID)->firstOrFail();
        // Check permission - edit project
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $project);

        // get old teams of project
        $oldTeams = $project->teams->pluck('id')->toArray() ?? [];

        \DB::transaction(function () use ($data, &$project) {
            // Update project
            $project->update($data);
            // Update Team to project
            $project->teams()->sync($data['teams']);
        });

        // TODO:: notification when update project success

        // if empty new teams then take action notification
        if (count($data['teams'] ?? [])) {
            // get new employees join to project
            $diffTeams = array_diff($data['teams'], $oldTeams);
            // if new employees exists then get list employees from CompanyEmployee table
            if (count($diffTeams ?? [])) {
                // get employee responsible
                $userRespon = optional($project->employeeResponsible->contact)->userContact;
                // if employee responsible exists then notify to employee
                if ($userRespon) {
                    $lang = optional($userRespon->setting)->language_code;
                    $userRespon->notify(new NotifyProject($project, 'update', 'responsible', $lang));
                }

                /* $list_employees = CompanyEmployee::select('id', 'contact_id')->whereIn('id', $diffTeams)->get();
                // if new employees from CompanyEmployee table exists  then notification to new employees
                if (! empty($list_employees) && count($list_employees)) {
                    foreach ($list_employees as $item) {
                        $user = optional(optional($item->contact)->userContact);
                        //if employee contact exists then take action
                        if ($user) {
                            $user->notify(new NotifyProject($project, 'update'));
                        }
                    }
                } */
            }
        }

        return $project;
    }

    /**
     * Set start_date and end date of a project
     */
    private function updateStartAndEndDateOfProject($project)
    {
        $data = [];
        //set start date
        $minStartDate = $project->tasks()->min('start_date');
        if ($minStartDate) {
            $data['start_date'] = $minStartDate;
        }

        //set end date
        $maxEndDate = $project->tasks()->max('end_date');
        $maxStartDate = $project->tasks()->max('start_date');

        $chooseEndDate = $maxEndDate;
        if ($maxEndDate && $maxStartDate) {
            $chooseEndDate = Carbon::parse($maxEndDate)->gt(Carbon::parse($maxStartDate)) ? $maxEndDate : $maxStartDate;
        } elseif (empty($maxEndDate)) {
            $chooseEndDate = $maxStartDate;
        }
        if ($chooseEndDate) {
            $data['end_date'] = $chooseEndDate;
        }

        // update
        if ($data) {
            $project->update($data);
        }

        return $project;
    }

    /**
     * Delete project
     *
     *
     * @return mixed
     */
    public function deleteProject($companyID, $projectID)
    {
        $project = Project::where('id', $projectID)->where('company_id', $companyID)->firstOrFail();
        // Check permission - edit project
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $project);

        return $project->delete();
    }

    /**
     * Get project by id
     *
     * @return mixed
     */
    public function getDetailProject($companyID, $projectID)
    {
        // Check permission
        $this->authService->checkBelongToCompany($companyID);

        // Get detail project by id
        return Project::where('id', $projectID)->with('tasks')->where('company_id', $companyID)->firstOrFail();
    }

    /**
     * Delete task of project
     *
     *
     * @return mixed
     */
    public function deleteTaskOfProject($companyID, $projectID, $taskID)
    {
        $project = Project::withCount('tasks')->where('id', $projectID)->where('company_id', $companyID)->firstOrFail();
        // Check permission - edit project
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $project);

        // The project must have at least 1 task
        abort_if($project->tasks_count <= 1, 500, 'The project must have at least 1 task!');

        // Delete task
        $deleted = Event::projectTask()
            ->where('company_id', $companyID)
            ->where('project_id', $projectID)
            ->where('id', $taskID)
            ->delete();

        // Update start_date and end date of a project
        if ($deleted) {
            $this->updateStartAndEndDateOfProject($project);
        }

        return $deleted;
    }

    /**
     * Create task of project
     *
     *
     * @return mixed
     */
    public function createTaskOfProject($companyID, $projectID, array $data)
    {
        $project = Project::where('id', $projectID)->where('company_id', $companyID)->firstOrFail();
        // Check permission - edit project
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $project);

        $user = $this->authService->getUserAuth();
        // Add Tasks to project
        $data['company_id'] = $companyID;
        $data['creator_id'] = $user->id;
        $data['type'] = config('constant.event.type.project_task');
        $data['project_id'] = $project->id;

        // Create task
        $task = Event::create($data);

        // add task employee
        $taskEmployees = [];
        $arEmployee = array_unique($data['employees']);
        foreach ($arEmployee as $employeeID) {
            $taskEmployees[$employeeID] = ['status' => config('constant.event.status.waiting'), 'contact_id' => CompanyEmployee::find($employeeID)->contact_id];
        }
        $task->employees()->attach($taskEmployees);

        // Update start_date and end date of a project
        if ($task) {
            $this->updateStartAndEndDateOfProject($project);
        }

        $listContacts = optional($task->contactNoDenies())->get();
        if (! empty($listContacts) && count($listContacts)) {
            foreach ($listContacts as $item) {
                $user = $item->userContact;
                if ($user) {
                    $lang = optional($user?->setting)->language_code;
                    $user->notify(new NotifyMyTask($task, config('constant.event.type.project_task'), TaskAndProjectAction::PROJECT_CREATE, $lang));
                }
            }
        }

        return $task;
    }

    /**
     * Edit task of project
     *
     *
     * @return mixed
     */
    public function updateTaskOfProject($companyID, $projectID, $taskID, array $data)
    {
        $project = Project::where('id', $projectID)->where('company_id', $companyID)->firstOrFail();
        // Check permission - edit project
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $project);

        // Update task
        $task = Event::projectTask()
            ->where('company_id', $companyID)
            ->where('project_id', $projectID)
            ->where('id', $taskID)->firstOrFail();
        $task->update($data);

        // Update task employee
        $arEmployeeStatus = [];
        if ($task->employees) {
            foreach ($task->employees as $employee) {
                $arEmployeeStatus[$employee->id] = $employee->pivot->status; // Hold employee status
            }
        }

        $taskEmployees = [];
        $arEmployee = array_unique($data['employees']);
        foreach ($arEmployee as $employeeID) {
            $taskEmployees[$employeeID] = ['status' => isset($arEmployeeStatus[$employeeID]) && $arEmployeeStatus[$employeeID] != config('constant.event.status.deny') ? $arEmployeeStatus[$employeeID] : config('constant.event.status.waiting'), 'contact_id' => CompanyEmployee::find($employeeID)->contact_id];
        }

        $task->employees()->sync($taskEmployees);

        // Update start_date and end date of a project
        $this->updateStartAndEndDateOfProject($project);

        $listContacts = optional($task->contactNoDenies())->get();

        if (! empty($listContacts) && count($listContacts)) {
            foreach ($listContacts as $item) {
                $user = $item->userContact;
                if ($user) {
                    $lang = optional($user?->setting)->language_code;
                    $user->notify(new NotifyMyTask($task, config('constant.event.type.project_task'), TaskAndProjectAction::PROJECT_EDIT, $lang));
                }
            }
        }

        return Event::withCount('contactDenies')->find($taskID); // need call again - because need load employees again.
    }

    /**
     * Get projects list by status
     *
     * @param  string  $status  = open|progress|done
     * @return mixed
     */
    public function getProjectByStatus($companyID = null, $status = StatusEvent::OPEN, $isUnion = false, $type = null)
    {
        // Check permission
        if (! $isUnion) {
            $this->authService->checkBelongToCompany($companyID);
        }

        $nowDate = Carbon::now()->format('Y-m-d');

        $projects = Project::select('projects.*', \DB::raw("'PROJECT' AS type"))->latest();

        switch ($status) {
            case StatusEvent::DONE:
                // project is done.
                $projects = $projects->whereDate('end_date', '<', $nowDate);
                break;
            case StatusEvent::PROGRESS:
                // project is in-progress.
                $projects = $projects->whereDate('end_date', '>=', $nowDate)->whereDate('start_date', '<=', $nowDate);
                break;
            default:
                // project is open (not running)
                $projects = $projects->whereDate('start_date', '>', $nowDate);
        }
        if ($isUnion && $type == UserType::BUSINESS) {
            $user = $this->authService->getUserAuth();

            return $projects->select('id', 'title', \DB::raw("'PROJECT' AS type"), 'start_date', 'end_date', 'company_id', 'creator_id')->with(['tasks', 'tasks.contacts', 'tasks.contactConfirms', 'company', 'teams', 'teams.contact', 'creator'])->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)->orWhere(function ($subQuery) use ($user) {
                    $subQuery->whereHas('tasks.contactConfirms', function ($query) use ($user) {
                        $query->where('user_contact_id', $user->id);
                    })->orWhereHas('employeeResponsible.contact', function ($query) use ($user) {
                        $query->where('user_contact_id', $user->id);
                    });
                });
            });
        }
        if ($isUnion && $type == UserType::PERSONAL) {
            $user = $this->authService->getUserAuth();

            return $projects->select('id', 'title', \DB::raw("'PROJECT' AS type"), 'start_date', 'end_date', 'company_id', 'creator_id')->with(['tasks', 'tasks.contacts', 'tasks.contactConfirms', 'company', 'teams', 'teams.contact', 'creator'])->where(function ($query) use ($user) {
                $query->whereHas('tasks.contactConfirms', function ($subQuery) use ($user) {
                    $subQuery->where('user_contact_id', $user->id);
                });
            });
        }

        return $projects->where(
            'company_id',
            $companyID
        )->paginate();
    }

    /*
     * PERSONAL APP
     */

    /**
     * Query common - get project by employee of user login in Personal app
     */
    private function queryGetProjectOfEmployeeInPersonalApp(&$projects, $myCompanies, $arrMyEmployeeId)
    {
        $projects = $projects->whereIn('company_id', $myCompanies) // belongs to my companies
            ->where(function (Builder $query) use (
                $arrMyEmployeeId
            ) {
                // i am a employee responsible or belongs to project's team
                $query->whereIn('employee_responsible_id', $arrMyEmployeeId)
                    ->orWhereHas('teams', function (Builder $query) use ($arrMyEmployeeId) {
                        $query->whereIn('employee_id', $arrMyEmployeeId);
                    });
            });
    }

    /**
     * Get project by status of employee
     *
     * @param  string  $status  = open|progress|done
     * @return mixed
     */
    public function getProjectByStatusOfEmployee(string $status = 'open')
    {
        // Get my companies that i accepted
        $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
            config('constant.event.status.confirm'),
            false
        );
        if (! $myEmployeeCompanies || $myEmployeeCompanies->count() == 0) {
            return [];
        }

        // Prepare
        $nowDate = Carbon::now()->format('Y-m-d');

        // Get projects
        $projects = Project::with('employeeResponsible', 'employeeResponsible.contact', 'employeeResponsible.contact.userContact', 'teams', 'teams.contact', 'teams.contact.userContact', 'company')->latest();
        $this->queryGetProjectOfEmployeeInPersonalApp(
            $projects,
            $myEmployeeCompanies->pluck('company_id'),
            $myEmployeeCompanies->pluck('id')
        );

        switch ($status) {
            case 'done':
                // project is done.
                $projects = $projects->whereDate('end_date', '<', $nowDate);
                break;
            case 'progress':
                // project is in-progress.
                $projects = $projects->whereDate('end_date', '>=', $nowDate)->whereDate('start_date', '<=', $nowDate);
                break;
            default:
                // project is open (not running)
                $projects = $projects->whereDate('start_date', '>', $nowDate);
        }

        return $projects->paginate();
    }

    /**
     * Get project detail by employee - user login with personal app
     *
     * @return |null
     */
    public function getDetailProjectByEmployee($projectID)
    {
        // Get my companies that i accepted
        $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
            config('constant.event.status.confirm'),
            false
        );
        if (! $myEmployeeCompanies || $myEmployeeCompanies->count() == 0) {
            return null;
        }

        $projects = Project::where('id', $projectID);
        $this->queryGetProjectOfEmployeeInPersonalApp(
            $projects,
            $myEmployeeCompanies->pluck('company_id'),
            $myEmployeeCompanies->pluck('id')
        );

        // Get detail project by id
        return $projects->firstOrFail();
    }

    /**
     * Leave the project - Leave the position of the person responsible or leave the team
     *
     * @return null
     */
    public function leaveProjectByEmployee($projectID)
    {
        $user = $this->authService->getUserAuth();

        // Get my companies that i accepted
        $myEmployeeCompanies = $this->companyService->getCompanyHadInvitedToMe(
            config('constant.event.status.confirm'),
            false
        );
        if (! $myEmployeeCompanies || $myEmployeeCompanies->count() == 0) {
            return null;
        }

        $projects = Project::where('id', $projectID);
        $this->queryGetProjectOfEmployeeInPersonalApp(
            $projects,
            $myEmployeeCompanies->pluck('company_id'),
            $myEmployeeCompanies->pluck('id')
        );

        // Get detail project by id
        $project = $projects->firstOrFail();
        $arMyEmployeeID = $myEmployeeCompanies->pluck('id')->toArray();

        // get list employee name
        $employees_name = $myEmployeeCompanies->pluck('contact.userContact.name')->toArray();
        // if item in array null then unset this item
        foreach ($employees_name as $index => $item) {
            if (! $item) {
                unset($employees_name[$index]);
            }
        }

        // Leave the position of the person responsible or leave the team
        // - Leave the position of the person responsible
        if (in_array($project->employee_responsible_id, $arMyEmployeeID)) {
            $project->employee_responsible_id = null;
            $project->save();

            // Notify to person responsible
            $creator = $project->creator;
            if ($creator) {
                $lang = optional($creator->setting)->language_code;
                $creator->notify(new NotifyLeaveProject($project, 'responsible', $user, $lang));
            }
        }
        // - leave the team
        if ($project->teams->count() > 0) {
            foreach ($project->teams as $team) {
                if (in_array($team->id, $arMyEmployeeID)) {
                    $project->teams()->detach($team->id);
                }
            }
            // get employee responsible project
            $responsible = optional(optional($project->employeeResponsible)->contact)->userContact;
            // if employee responsible exists then send notify to employee
            if ($responsible) {
                $employees_name = implode(', ', $employees_name);
                $lang = optional($responsible->setting)->language_code;
                $responsible->notify(new NotifyLeaveProject($project, 'employee', $user, $employees_name, $lang));
            }
        }
    }

    public function addRoomChat($id, $room_id)
    {
        $project = Project::find($id);
        if ($room_id) {
            if (! $project) {
                abort(404, __('project.project_not_exists'));
            }
            $project->update([
                'room_chat_id' => $room_id,
            ]);
        }

        return $project;
    }
}
