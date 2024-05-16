<?php

namespace App\Services;

use App\Store;

/**
 * StoreService
 *
 * Class FcmService
 */
class StoreService
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function list(string $search = null, ?int $limit = 0)
    {
        $company = $this->authService->getCompany();
        if (! $company) {
            abort(422, __('roster.employee_not_belong_to_permission_roster'));
        }
        $stores = Store::query()->where('company_id', $company->id)->latest();
        $stores->when($search, fn ($query) => $query->where('name', 'LIKE', "%{$search}%"));
        if ($limit) {
            return $stores->paginate($limit);
        }

        return $stores->get();
    }

    public function store(array $data)
    {
        $company = $this->authService->getCompany();
        $user = $this->authService->getUserAuth();
        if (! $company) {
            abort(422, __('roster.employee_not_belong_to_permission_roster'));
        }

        return Store::create(array_merge($data, [
            'creator_id' => $user->id,
            'company_id' => $company->id,
        ]));
    }

    public function update(int $id, array $data)
    {
        $store = $this->findById($id);
        $store->update($data);

        return $store;
    }

    public function findById(int $id)
    {
        $company = $this->authService->getCompany();
        if (! $company) {
            abort(422, __('roster.employee_not_belong_to_permission_roster'));
        }

        return Store::where([
            'id' => $id,
            'company_id' => $company->id,
        ])->firstOrFail();
    }

    public function delete(int $id)
    {
        $store = $this->findById($id);

        return $store->delete();
    }
}
