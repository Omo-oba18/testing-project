<?php

namespace App\Services;

use App\Enums\EmployeePermissionColumn;
use App\Enums\RosterAction;
use App\Enums\StatusEvent;
use App\Enums\UserType;
use App\Jobs\SendRosterJob;
use App\Notifications\NotifyAddRoster;
use App\Notifications\NotifyRosterEmployeeAction;
use App\Roster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterService
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get list rosters function
     *
     * @param  array  $params
     * @return \App\Roster
     */
    public function getRoster($params = [])
    {
        $select = '*';

        if (isset($params['select'])) {
            $select = $params['select'];
        }

        $data = Roster::select($select);

        if (isset($params['where'])) {
            $data = $data->where($params['where']);
        }

        if (isset($params['closure'])) {
            $data = $data->where($params['closure']);
        }

        if (isset($params['with'])) {
            $data = $data->with($params['with']);
        }

        if (isset($params['order'])) {
            $data = $data->orderBy($params['order'], $params['by']);
        } else {
            $data = $data->orderBy('start_time', 'asc')->orderBy('end_time', 'asc');
        }

        if (isset($params['paginate'])) {
            $data = $data->paginate($params['paginate']);
        } else {
            $data = $data->get();
        }

        return $data;
    }

    /**
     * Create roster
     *
     * @param  array  $data
     * @return App\Roster
     */
    public function createRoster($data)
    {

        $company = $this->authService->getCompany();
        if (! $company) {
            abort(422, __('roster.employee_not_belong_to_permission_roster'));
        }

        $this->authService->checkPermissionAddInCompany($company->id, EmployeePermissionColumn::ADD_ROSTER);

        $roster = Roster::create($data);

        // send notify to employee when business add roster
        $employeeContact = $roster->employee->contact;
        if ($employeeContact->userContact) {
            $lang = optional($employeeContact->userContact?->setting)->language_code;
            $employeeContact->userContact->notify(new NotifyAddRoster($roster, $roster->creator->name, $lang));
        } else {
            $employeeContact->notify(new NotifyAddRoster($roster, $roster->creator->name));
        }

        return $roster;
    }

    /**
     * Update roster
     *
     * @param  array  $data
     * @param  int  $id - id roster
     * @return Roster
     */
    public function updateRoster($data, $id)
    {
        $roster = Roster::with('employee')->findOrFail($id);

        $company = $this->authService->getCompany();
        abort_if(! $company, 422, __('roster.employee_not_belong_to_permission_roster'));
        $this->authService->checkPermissionEditAndDeleteInCompany($company->id, $roster);
        $data['status'] = $roster->status == config('constant.event.status.deny') ? config('constant.event.status.waiting') : $roster->status;
        $roster->update($data);
        SendRosterJob::dispatch($roster, RosterAction::EDIT);

        return $roster;
    }

    /**
     * Delete roster
     *
     * @param  int  $id - id roster
     * @return bool
     */
    public function deleteRoster($id)
    {
        $roster = Roster::findOrFail($id);

        $company = $this->authService->getCompany();
        abort_if(! $company, 422, __('roster.employee_not_belong_to_permission_roster'));
        $this->authService->checkPermissionEditAndDeleteInCompany($company->id, $roster);

        return $roster->delete();
    }

    /**
     * Find roster by id
     *
     * @param  int  $creator_id - id creator
     * @param  int  $id - id roster
     * @return \App\Roster
     */
    public function findRosterByCreator($creator_id, $id)
    {
        return Roster::with('employee')->where('creator_id', $creator_id)->findOrFail($id);
    }

    public function findById($id)
    {
        return Roster::with('employee')->findOrFail($id);
    }

    /**
     * Employee accept or decline request add roster by business
     *
     * @param  int  $id
     * @param  int  $user_id
     * @param  Enum  $action - Y is confirm | N is deny
     * @param bool
     */
    public function employeeActionRoster($id, $user_id, $action)
    {
        $user = $this->authService->getUserAuth();
        $roster = $this->queryRosterByUser($user_id)->findOrFail($id);

        $roster->update(['status' => $action]);
        $roster->creator->notify(new NotifyRosterEmployeeAction($roster, $roster->employee->getEmployeeName(), $user));

        return $roster;
    }

    /**
     * Query roster by employee id
     *
     * @param  int  $user_id
     * @param  string  $status: default null
     * @return App\Roster
     */
    public function queryRosterByEmployee($user_id, $status = null)
    {
        $query = Roster::with(['employee', 'creator'])->whereHas('employee.contact', function ($q) use ($user_id) {
            $q->where('user_contact_id', $user_id);
        });
        if ($status) {
            return $query->where('status', $status);
        }

        return $query->where('status', '<>', config('constant.event.status.deny'));
    }

    /**
     * Query roster by user id
     *
     * @param  int  $user_id
     * @param  string  $status: default null
     * @return App\Roster
     */
    public function queryRosterByUser($user_id, $status = null)
    {
        $query = Roster::with(['employee', 'creator'])
            ->where(function ($query) use ($user_id) {
                $query->orWhere('rosters.creator_id', $user_id)
                    ->orWhereHas('employee.contact', function ($q) use ($user_id) {
                        $q->where('user_contact_id', $user_id);
                    });
            });
        if ($status) {
            return $query->where('status', $status);
        }

        return $query->where('status', '<>', config('constant.event.status.deny'));
    }

    /**
     * Get status roster by date
     *
     * @param  int  $creator_id
     * @param  Date  $start_date
     * @param  Date  $end_date
     * @return array
     */
    public function getStatusRoster($startDate, $endDate)
    {

        $company = $this->authService->getCompany();
        if (! $company) {
            return [];
        }
        $companyEmployeeIds = $company->employees->pluck('id');

        $rosters = Roster::where('status', '<>', config('constant.event.status.deny'))
            ->whereIn('company_employee_id', $companyEmployeeIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($query) use ($startDate, $endDate) {
                    $query->whereDate('start_time', '<=', $startDate)
                        ->whereDate('end_time', '>=', $endDate);
                })
                    ->orWhereBetween('start_time', [$startDate, $endDate])
                    ->orWhereBetween('end_time', [$startDate, $endDate]);
            })->get();

        if (! count($rosters)) {
            return [];
        }

        // loop startDate to endDate
        $begin = new \DateTime($startDate);
        $end = new \DateTime(date('Y-m-d', strtotime($endDate.'+1 days')));
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);

        $data = [];
        foreach ($period as $dt) {
            $day = $dt->format('Y-m-d');

            $countConfirm = $rosters->where('status', config('constant.event.status.confirm'))
                ->filter(function ($item) use ($day) {
                    return date('Y-m-d', strtotime($item->start_time)) <= $day && date('Y-m-d', strtotime($item->end_time)) >= $day;
                })->count();
            $countWait = $rosters->where('status', config('constant.event.status.waiting'))
                ->filter(function ($item) use ($day) {
                    return date('Y-m-d', strtotime($item->start_time)) <= $day && date('Y-m-d', strtotime($item->end_time)) >= $day;
                })->count();

            if ($countConfirm || $countWait) {
                $data[$day] = $countWait;
            }
        }

        return $data;
    }

    /**
     * get list rosters by date
     *
     * @param  Date  $date
     * @return \App\Roster
     */
    public function rostersByDate($date)
    {
        $company = $this->authService->getCompany();

        return Roster::with(['employee', 'employee.contact', 'employee.contact.userContact'])
            ->whereDate('start_time', '<=', $date)
            ->whereDate('end_time', '>=', $date)
            ->where('status', '<>', config('constant.event.status.deny'))
            ->whereHas('employee', function ($query) use ($company) {
                return $query->where('company_id', $company->id);
            })->paginate(10);
    }

    public function getRosterByStatus($status, $type = null)
    {

        $user = $this->authService->getUserAuth();
        $nowDate = Carbon::now()->format('Y-m-d');

        if ($type == UserType::BUSINESS) {
            $rosters = $this->queryRosterByUser($user->id, config('constant.event.status.confirm'));
        } else {
            $rosters = Roster::with(['employee', 'creator'])->where(function ($q) use ($user) {
                $q->whereHas('employee.contact', function ($query) use ($user) {
                    $query->where('user_contact_id', $user->id);
                })->orWhere('creator_id', $user->id);
            })->where('status', config('constant.event.status.confirm'));
        }
        $rosters = $rosters->select(
            DB::raw("rosters.id as id, NULL as title, start_time as start_date, end_time as end_date,'ROSTER' as type"),
            'company_employee_id',
            'creator_id',
            'store_id'
        )->with(['creator', 'creator.company', 'employee', 'employee.contact', 'store']);
        switch ($status) {
            case StatusEvent::DONE:
                // project is done.
                $rosters = $rosters->whereDate('end_time', '<', $nowDate);
                break;
            case StatusEvent::PROGRESS:
                // project is in-progress.
                $rosters = $rosters->whereDate('end_time', '>=', $nowDate)->whereDate('start_time', '<=', $nowDate);
                break;
            default:
                // project is open (not running)
                $rosters = $rosters->whereDate('start_time', '>', $nowDate);
        }

        return $rosters;
    }
}
