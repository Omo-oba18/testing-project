<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_employee_id', 'creator_id', 'status', 'start_time', 'end_time', 'store_id'];

    /**
     * Get the creator that owns the Roster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    /**
     * Get the employee that owns the Roster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(CompanyEmployee::class, 'company_employee_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
