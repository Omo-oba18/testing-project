<?php

namespace App\Http\Controllers;

use App\Enums\StatusEvent;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskAndProjectResource;
use App\Http\Resources\TaskResource;
use App\Services\AuthService;
use App\Services\TaskService;

class TaskController extends Controller
{
    private $taskService;

    /**
     * Initial service in controller
     * ProjectController constructor.
     */
    public function __construct(AuthService $authService, TaskService $taskService)
    {
        parent::__construct($authService);
        $this->taskService = $taskService;
    }

    /**
     * Add Task
     *
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function store($companyID, TaskRequest $request)
    {
        $task = $this->taskService->createTask($companyID, $request->validated());

        return response()->json(new TaskResource($task), 200);
    }

    /**
     * Edit task
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($companyID, $taskID, TaskRequest $request)
    {
        $task = $this->taskService->updateTask($companyID, $taskID, $request->validated());

        return response()->json(new TaskResource($task), 200);
    }

    /**
     * Delete task
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($companyID, $taskID)
    {
        $deleted = $this->taskService->deleteTask($companyID, $taskID);
        abort_if(! $deleted, 500, 'Task does not exist!');

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Get detail task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($companyID, $taskID)
    {
        $task = $this->taskService->getDetail($companyID, $taskID);

        return response()->json(new TaskResource($task), 200);
    }

    /**
     * Get task and project list by status of company
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    private function getTaskByStatus($companyID, $status)
    {
        $tasks = $this->taskService->getTaskByStatus($companyID, $status);

        return TaskAndProjectResource::collection($tasks);
    }

    /**
     * Open list task
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getTaskOpen($companyID)
    {
        return $this->getTaskByStatus($companyID, StatusEvent::OPEN);
    }

    /**
     * In-progress list task
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getTaskInProgress($companyID)
    {
        return $this->getTaskByStatus($companyID, StatusEvent::PROGRESS);
    }

    /**
     * Done list task
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getTaskDone($companyID)
    {
        return $this->getTaskByStatus($companyID, StatusEvent::DONE);
    }
}
