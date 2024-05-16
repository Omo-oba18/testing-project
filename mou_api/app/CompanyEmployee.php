<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyEmployee extends Model
{
    protected $table = 'company_employees';

    protected $fillable = ['contact_id', 'company_id', 'role_name', 'permission_access_business', 'permission_add_task', 'permission_add_project', 'permission_add_employee', 'employee_confirm', 'creator_id', 'permission_add_roster'];

    public function getEmployeeName()
    {
        return $this->contact->name ?? optional($this->contact->userContact)->name;
    }

    public function getEmployeeAvatar()
    {
        return $this->contact->avatar ?? optional($this->contact->userContact)->avatar;
    }

    /**
     * ---------------------------
     * SCOPE
     * ---------------------------
     */
    /**
     * Employee is confirmed
     *
     * @return mixed
     */
    public function scopeEmployeeConfirmed($query)
    {
        return $query->where('employee_confirm', config('constant.event.status.confirm'));
    }

    /**
     * Employee is waiting confirm
     *
     * @return mixed
     */
    public function scopeEmployeeWaitingConfirm($query)
    {
        return $query->where('employee_confirm', config('constant.event.status.waiting'));
    }

    /**
     * Employee is denied
     *
     * @return mixed
     */
    public function scopeEmployeeDenied($query)
    {
        return $query->where('employee_confirm', config('constant.event.status.deny'));
    }

    /**
     * Relationship
     */
    public function contact()
    {
        return $this->belongsTo('App\Contact');
    }

    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id');
    }
}
