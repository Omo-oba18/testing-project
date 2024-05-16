<?php

namespace App\Http\Controllers;

use App\Enums\StatusEvent;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectTaskRequest;
use App\Http\Resources\ProjectOfEmployeeResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectTaskResource;
use App\Services\AuthService;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private $projectService;

    /**
     * Initial service in controller
     * ProjectController constructor.
     */
    public function __construct(AuthService $authService, ProjectService $projectService)
    {
        parent::__construct($authService);
        $this->projectService = $projectService;
    }

    /**
     * Add project
     *
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function store($companyID, ProjectRequest $request)
    {
        $project = $this->projectService->createProject($companyID, $request->validated());

        return response()->json(new ProjectResource($project), 200);
    }

    /**
     * Update project
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($companyID, $projectID, ProjectRequest $request)
    {
        $project = $this->projectService->updateProject($companyID, $projectID, $request->validated());

        return response()->json(new ProjectResource($project), 200);
    }

    /**
     * Delete project
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($companyID, $projectID)
    {
        $deleted = $this->projectService->deleteProject($companyID, $projectID);
        abort_if(! $deleted, 500, 'Project does not exist!');

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Get detail project by id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($companyID, $projectID)
    {
        $project = $this->projectService->getDetailProject($companyID, $projectID);
        $project->contact_denies_count = $project->tasks->sum('contact_denies_count');

        return response()->json(new ProjectResource($project), 200);
    }

    /**
     * Delete task of project
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyTask($companyID, $projectID, $taskID)
    {
        $deleted = $this->projectService->deleteTaskOfProject($companyID, $projectID, $taskID);
        abort_if(! $deleted, 500, 'Task does not exist!');

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Create task of project
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTask($companyID, $projectID, ProjectTaskRequest $request)
    {
        $task = $this->projectService->createTaskOfProject($companyID, $projectID, $request->validated());

        return response()->json(new ProjectTaskResource($task), 200);
    }

    /**
     * Edit task of project
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTask($companyID, $projectID, $taskID, ProjectTaskRequest $request)
    {
        $task = $this->projectService->updateTaskOfProject($companyID, $projectID, $taskID, $request->validated());

        return response()->json(new ProjectTaskResource($task), 200);
    }

    /**
     * Get task and project list by status of company
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    private function getProjectByStatus($companyID, $status)
    {
        $projects = $this->projectService->getProjectByStatus($companyID, $status);

        return ProjectResource::collection($projects);
    }

    /**
     * Open list (task and projects)
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProjectOpen($companyID)
    {
        return $this->getProjectByStatus($companyID, StatusEvent::OPEN);
    }

    /**
     * In-progress list (task and projects)
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProjectInProgress($companyID)
    {
        return $this->getProjectByStatus($companyID, StatusEvent::PROGRESS);
    }

    /**
     * Done list (task and projects)
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProjectDone($companyID)
    {
        return $this->getProjectByStatus($companyID, StatusEvent::DONE);
    }

    /*
     * PERSONAL APP
     */
    /**
     * Get projects open of employee
     *
     * @param $companyID
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProjectOpenOfEmployee()
    {
        $taskAndProjects = $this->projectService->getProjectByStatusOfEmployee('open');

        return ProjectOfEmployeeResource::collection($taskAndProjects);
    }

    /**
     * Get projects progress of employee
     *
     * @param $companyID
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProjectInProgressOfEmployee()
    {
        $taskAndProjects = $this->projectService->getProjectByStatusOfEmployee('progress');

        return ProjectOfEmployeeResource::collection($taskAndProjects);
    }

    /**
     * Get projects done of employee
     *
     * @param $companyID
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getProjectDoneOfEmployee()
    {
        $taskAndProjects = $this->projectService->getProjectByStatusOfEmployee('done');

        return ProjectOfEmployeeResource::collection($taskAndProjects);
    }

    /**
     * Get detail project - by employee
     *
     * @param $companyID
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailProjectByEmployee($projectID)
    {
        $project = $this->projectService->getDetailProjectByEmployee($projectID);

        return response()->json(new ProjectResource($project), 200);
    }

    /**
     * Leave the project by Employee
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaveProjectByEmployee($projectID)
    {
        $this->projectService->leaveProjectByEmployee($projectID);

        return response()->json(['message' => 'ok'], 200);
    }

    public function updateRoomChat($projectID, Request $request)
    {
        $request->validate([
            'room_chat_id' => 'nullable|string',
        ]);

        $this->projectService->addRoomChat($projectID, $request->room_chat_id);
    }
}
