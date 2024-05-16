<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class BusinessService
{
    public function getAll($data): LengthAwarePaginator
    {
        $currentPage = Paginator::resolveCurrentPage();
        $pagedData = $data->forPage($currentPage, 10)->values();

        return new LengthAwarePaginator(
            $pagedData,
            $data->count(),
            10,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }
}
