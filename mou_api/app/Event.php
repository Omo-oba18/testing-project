<?php

namespace App;

use App\Enums\EventType;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    //use for repeat and alarm fields
    const CHARACTER_SPECIAL = ';';

    protected $perPage = 20;

    //
    protected $fillable = ['title', 'start_date', 'end_date', 'comment', 'repeat', 'alarm', 'place', 'busy_mode', 'creator_id', 'chat', 'type', 'company_id', 'project_id', 'done_time', 'room_chat_id', 'store_id', 'show_end_date'];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            //TODO: tmp
            //            $model->creator_id = User::first()->id;
            $authService = new AuthService();
            $model->creator_id = $authService->getUserAuth()->id;
        });
    }

    /**
     * ---------------------------
     * SCOPE
     * ---------------------------
     */
    /**
     * Get events in the future
     *
     * @return mixed
     */
    public function scopeInFuture($query)
    {
        return $query->where('start_date', '>=', date('Y-m-d H:i:00'));
    }

    public function scopeInFutureOnlyDate($query)
    {
        return $query->where('start_date', '>=', date('Y-m-d 00:00:00'));
    }

    /*
     * Event type
     */
    public function isEvent()
    {
        return $this->type == EventType::EVENT->value;
    }

    public function isProjectTask()
    {
        return $this->type == EventType::PROJECT_TASK->value;
    }

    public function isTask()
    {
        return $this->type == EventType::TASK->value;
    }

    public function scopeEvent($query)
    {
        return $query->where('type', EventType::EVENT->value);
    }

    public function scopeProjectTask($query)
    {
        return $query->where('type', EventType::PROJECT_TASK->value);
    }

    public function scopeTask($query)
    {
        return $query->where('type', EventType::TASK->value);
    }

    public function scopeEventType($query, $param)
    {
        return $query->whereIn('type', $param);
    }

    public static function queryAlarm()
    {
        $user = \Auth::user()->user;

        return Event::event()->orderBy('start_date')
            ->where(function (Builder $query) {
                $query->where('start_date', '>=', date('Y-m-d H:i:00'))
                    ->orWhereNull('end_date')
                    ->orWhere('end_date', '>=', date('Y-m-d H:i:00'));
            })
            ->where(function (Builder $query) use ($user) {
                //event minh tao hoac minh dong y tham gia
                $query->where('creator_id', $user->id)
                    ->orWhereHas('contactConfirms', function (Builder $query) use ($user) {
                        // Event mình đã đồng ý tham gia
                        $query->where('user_contact_id', $user->id);
                    });
            })
            ->whereNotNull('alarm')->where('busy_mode', '!=', 1);
    }

    /**
     * ---------------------------
     * Relationship
     * ---------------------------
     */
    public function contacts()
    {
        return $this->belongsToMany('App\Contact', 'event_contact')->withTimestamps()->withPivot('status');
    }

    public function contactNoDenies()
    {
        return $this->contacts()->wherePivot('status', '!=', config('constant.event.status.deny'));
    }

    public function contactWaitings()
    {
        return $this->contacts()->wherePivot('status', config('constant.event.status.waiting'));
    }

    public function contactConfirms()
    {
        return $this->contacts()->wherePivot('status', config('constant.event.status.confirm'));
    }

    public function contactDenies()
    {
        return $this->contacts()->wherePivot('status', config('constant.event.status.deny'));
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /*
     * RELATION SHIP - Business
     */
    public function employees()
    {
        return $this->belongsToMany('App\CompanyEmployee', 'event_contact', 'event_id', 'employee_id')->withTimestamps()->withPivot('status');
    }

    public function employeeNoDenies()
    {
        return $this->employees()->wherePivot('status', '!=', config('constant.event.status.deny'));
    }

    public function employeeWaitings()
    {
        return $this->employees()->wherePivot('status', config('constant.event.status.waiting'));
    }

    public function employeeConfirms()
    {
        return $this->employees()->wherePivot('status', config('constant.event.status.confirm'));
    }

    public function employeeDenies()
    {
        return $this->employees()->wherePivot('status', config('constant.event.status.deny'));
    }

    /**
     * Get status of project task
     *
     * @return string
     */
    public function getProjectTaskStatus()
    {
        $nowDate = Carbon::now()->startOfDay();
        $startDate = Carbon::parse($this->start_date)->startOfDay();

        // Done task
        if (! empty($this->done_time)) {
            if (Carbon::parse($this->done_time)->gt(Carbon::parse($this->end_date)->endOfDay())) {
                // not done
                return 'NOT_DONE'; //Delay
            }

            return 'DONE';
        }

        // IF Start date greater than now
        if ($startDate->gt($nowDate)) {
            return 'WAITING';
        }
        // IF Empty end date
        if (empty($this->end_date)) {
            // IF Start date less than now
            if ($startDate->lt($nowDate)) {
                // not done
                return 'NOT_DONE';
            }
        } else {
            $endDate = Carbon::parse($this->end_date)->startOfDay();
            // IF End date less than now
            if ($endDate->lt($nowDate)) {
                // not done
                return 'NOT_DONE';
            }
        }

        return 'IN_PROGRESS';
    }

    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
