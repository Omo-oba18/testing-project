<?php

namespace App\Http\Controllers;

use App\Enums\TodoType;
use App\Http\Requests\OrderTodoRequest;
use App\Http\Requests\TodoRequest;
use App\Http\Resources\TodoResource;
use App\Services\TodoService;
use App\Todo;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;

class TodoController extends Controller
{
    use ApiResponder;

    public function __construct(protected TodoService $todoService)
    {
    }

    public function index(): JsonResponse
    {
        $user = auth('api')->user()->user;
        $params = [
            'type' => request('type', null),
        ];

        $todos = $this->todoService->getListByUser($user, $params);

        return $this->responseSuccess(TodoResource::collection($todos), __('todo.response_success'));
    }

    /**
     * Store Todo
     */
    public function store(TodoRequest $request): JsonResponse
    {
        $input = $request->validated();
        if (isset($input['parent_id']) && $input['parent_id'] && isset($input['type']) && $input['type'] == TodoType::GROUP) {
            return $this->respondErrorWithNoData(__('todo.create_error'));
        }
        $user = auth('api')->user()->user;
        $todo = $this->todoService->create($user, $input);
        $todo->load(['parent', 'children', 'contacts', 'creator']);

        return $this->responseSuccess(TodoResource::make($todo), __('todo.response_success'));
    }

    /**
     * Update Todo
     */
    public function update($id, TodoRequest $request): JsonResponse
    {
        $input = $request->validated();
        $user = auth('api')->user()->user;
        $todo = $this->todoService->findById($id);
        if (empty($todo)) {
            return $this->respondErrorWithNoData(__('todo.todo_not_exist'));
        }
        if (! $user->can('update', $todo)) {
            return $this->respondErrorWithNoData(__('todo.dont_have_permission'));
        }

        $this->todoService->update($todo, $input);
        $todo->load(['parent', 'children', 'contacts']);

        return $this->responseSuccess(TodoResource::make($todo), __('todo.response_success'));
    }

    /**
     * Delete Todo
     */
    public function destroy($id): JsonResponse
    {
        $todo = $this->todoService->findById($id);
        $user = auth('api')->user()->user;
        if (empty($todo)) {
            return $this->respondErrorWithNoData(__('todo.todo_not_exist'));
        }
        if (! $user->can('update', $todo)) {
            return $this->respondErrorWithNoData(__('todo.dont_have_permission'));
        }
        $this->todoService->delete($todo);

        return $this->responseSuccessWithMessage(__('todo.response_success'));
    }

    /**
     * Completed Todo
     */
    public function completedTodo($id): JsonResponse
    {
        $todo = $this->todoService->findById($id);
        $user = auth('api')->user()->user;
        if (empty($todo)) {
            return $this->respondErrorWithNoData(__('todo.todo_not_exist'));
        }
        if (! $user->can('update', $todo)) {
            return $this->respondErrorWithNoData(__('todo.dont_have_permission'));
        }
        if ($todo->type == TodoType::GROUP) {
            return $this->respondErrorWithNoData(__('todo.type_error'));
        }
        $this->todoService->updateCompletedTodo($todo);

        return $this->responseSuccess(TodoResource::make($todo), __('todo.response_success'));
    }

    /**
     * Detail Todo
     */
    public function show($id): JsonResponse
    {
        $todo = $this->todoService->findById($id);

        if (empty($todo)) {
            return $this->respondErrorWithNoData(__('todo.todo_not_exist'));
        }

        return $this->responseSuccess(TodoResource::make($todo), __('todo.response_success'));
    }

    /**
     * Get todo children
     */
    public function getChildren($id): JsonResponse
    {
        $user = auth('api')->user()->user;
        $todos = $this->todoService->getListChildren($user, $id);

        return $this->responseSuccess(TodoResource::collection($todos), __('todo.response_success'));
    }

    /**
     * overline Todo
     */
    public function overlineTodo($id): JsonResponse
    {
        $todo = $this->todoService->findById($id);
        $user = auth('api')->user()->user;
        if (empty($todo)) {
            return $this->respondErrorWithNoData(__('todo.todo_not_exist'));
        }
        if (! $user->can('update', $todo)) {
            return $this->respondErrorWithNoData(__('todo.dont_have_permission'));
        }
        if ($todo->type == TodoType::GROUP) {
            return $this->respondErrorWithNoData(__('todo.type_error'));
        }
        $this->todoService->toggleOverlineTodo($todo);

        return $this->responseSuccess(TodoResource::make($todo), __('todo.response_success'));
    }

    /**
     * Order Todo
     */
    public function orderTodo(OrderTodoRequest $request): JsonResponse
    {
        $input = $request->validated();
        $this->todoService->updateOrder($input);

        return $this->responseSuccessWithMessage(__('todo.response_success'));
    }
}
