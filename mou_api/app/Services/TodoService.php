<?php

namespace App\Services;

use App\Contact;
use App\Events\TodoCreated;
use App\Notifications\SmsUseApp;
use App\Todo;
use App\User;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class TodoService
{
    /**
     * Create Todo
     */
    public function create(User $user, array $input): Todo
    {
        try {
            DB::beginTransaction();
            $todo = $user->todos()->create($input);
            if (isset($input['contact_ids']) && (! isset($input['parent_id']) || ! $input['parent_id'])) {
                $todo->contacts()->attach($input['contact_ids']);
                event(new TodoCreated($todo));
                /* $contacts = Contact::whereIn('id', $input['contact_ids'])->get();
                foreach ($contacts as $contact) {
                    $contact->notify(new SmsUseApp($contact->name ?? '', $user->name, optional($user->setting)->language_code ?? 'en', '?page=home'));
                } */
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return $todo;
    }

    /**
     * Find by id
     */
    public function findById($id): ?Todo
    {
        $user = auth('api')->user()->user;

        return Todo::with(['parent', 'contacts', 'creator', 'children' => function ($query) use ($user) {
            $query->where('creator_id', $user->id)->orWhereHas('contacts', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            })->orWhereHas('parent.contacts', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            });
        }])->find($id);
    }

    /**
     * Update Todo
     */
    public function update(Todo $todo, array $input): Todo
    {
        try {
            DB::beginTransaction();
            $todo->update($input);
            if (isset($input['contact_ids'])) {
                $todo->contacts()->sync($input['contact_ids']);
            } else {
                $todo->contacts()->detach();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return $todo;
    }

    /**
     * List Todo
     */
    public function getListByUser(User $user, array $params): Collection
    {
        $query = Todo::query()->with(['parent', 'contacts', 'creator', 'children'])->notDone()->where(function ($query) use ($user) {
            $query->where('creator_id', $user->id)->orWhereHas('contacts', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            });
        });
        if (isset($params['type'])) {
            $query = $query->where('type', $params['type']);
        }

        return $query->orderBy('order')->get();
    }

    public function updateCompletedTodo(Todo $todo): Todo
    {
        $todo->update([
            'done_time' => now(),
        ]);

        return $todo;
    }

    public function delete(Todo $todo): bool
    {
        try {
            DB::beginTransaction();
            $todo->children()->delete();
            if ($todo->parent) {
                $todo->parent()->touch();
            }
            $todo->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * List Children Todo
     */
    public function getListChildren(User $user, $id): Collection
    {
        return Todo::query()->with(['parent', 'parent.contacts', 'contacts', 'creator', 'children'])->notDone()->where('parent_id', $id)->where(function ($query) use ($user) {
            $query->where('creator_id', $user->id)->orWhereHas('parent.contacts', function ($subQuery) use ($user) {
                $subQuery->where('user_contact_id', $user->id);
            });
        })->orderBy('order')->get();
    }

    public function toggleOverlineTodo(Todo $todo): Todo
    {
        $todo->update([
            'overline' => ! $todo->overline,
        ]);

        return $todo;
    }

    /**
     * Update order
     */
    public function updateOrder(array $input): void
    {
        foreach ($input['ids'] as $key => $id) {
            Todo::where('id', $id)->update(['order' => $input['orders'][$key]]);
        }
    }
}
