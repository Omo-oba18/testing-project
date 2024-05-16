<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $employees = [];
        if ($this->employees) {
            foreach ($this->employees as $employee) {
                $employees[] = [
                    'id' => $employee->id,
                    'name' => $employee->getEmployeeName(),
                    'employee_confirm' => $employee->pivot->status,
                ];
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'start_date' => Carbon::parse($this->start_date)->format('Y-m-d'),
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->format('Y-m-d') : null,
            'status' => $this->getProjectTaskStatus(),
            'comment' => $this->comment,
            'employees' => $employees,
        ];
    }
}