<?php

namespace App\Http\Controllers;

use App\Enums\BusinessType;
use App\Enums\UserType;
use App\Http\Requests\BusinessAndPersonalRequest;
use App\Http\Resources\BusinessAndPersonalResource;
use App\Services\BusinessService;
use App\Services\ProjectService;
use App\Services\RosterService;
use App\Services\TaskService;

class BusinessAndPersonalController extends Controller
{
    public function __construct(protected ProjectService $projectService, protected TaskService $taskService, protected BusinessService $businessService, protected RosterService $rosterService)
    {
    }

    /**
     * Get list by Business
     */
    public function getListByBusiness(BusinessAndPersonalRequest $request)
    {
        $params = $request->validated();
        $tasks = $this->taskService->getTaskByStatus(null, $params['status'], true, UserType::BUSINESS);
        $projects = $this->taskService->getTaskByStatus(null, $params['status'], true, UserType::BUSINESS, BusinessType::PROJECT_TASK);
        $rosters = $this->rosterService->getRosterByStatus($params['status'], UserType::BUSINESS);
        if (isset($params['type'])) {
            $dataUnion = $tasks->eventType($params['type'])->get();
            $dataUnion = in_array(BusinessType::PROJECT_TASK, $params['type']) ? $dataUnion->merge($projects->get()) : $dataUnion;
            $dataUnion = in_array(BusinessType::ROSTER, $params['type']) ? $dataUnion->merge($rosters->get()) : $dataUnion;
        } else {
            $dataUnion = $tasks->get()->merge($projects->get())->merge($rosters->get());
        }
        $data = $this->businessService->getAll($dataUnion);

        return BusinessAndPersonalResource::collection($data);
    }

    /**
     * Get list by Personal
     */
    public function getListByPersonal(BusinessAndPersonalRequest $request)
    {
        $params = $request->validated();
        $tasks = $this->taskService->getTaskByStatus(null, $params['status'], true, UserType::PERSONAL)->orderBy('end_date', 'desc');
        $projects = $this->taskService->getTaskByStatus(null, $params['status'], true, UserType::PERSONAL, BusinessType::PROJECT_TASK)->orderBy('end_date', 'desc');
        $rosters = $this->rosterService->getRosterByStatus($params['status'], UserType::PERSONAL)->orderBy('end_date', 'desc');

        if (isset($params['type'])) {
            $dataUnion = $tasks->eventType($params['type'])->get();
            $dataUnion = in_array(BusinessType::PROJECT_TASK, $params['type']) ? $dataUnion->merge($projects->get()) : $dataUnion;
            $dataUnion = in_array(BusinessType::ROSTER, $params['type']) ? $dataUnion->merge($rosters->get()) : $dataUnion;
        } else {
            $dataUnion = $tasks->get()->merge($projects->get())->merge($rosters->get());
        }
        $data = $this->businessService->getAll($dataUnion);

        return BusinessAndPersonalResource::collection($data);
    }
}
