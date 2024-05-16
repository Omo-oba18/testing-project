<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['company_id', 'creator_id', 'title', 'description', 'client', 'employee_responsible_id', 'start_date', 'end_date', 'room_chat_id'];

    /*
     * RELATION SHIP
     */
    public function employeeResponsible()
    {
        return $this->belongsTo('App\CompanyEmployee', 'employee_responsible_id')->with('contact');
    }

    public function teams()
    {
        return $this->belongsToMany('App\CompanyEmployee', 'project_teams', 'project_id', 'employee_id');
    }

    public function tasks()
    {
        return $this->hasMany('App\Event')->withCount('contactNoDenies');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }
}
