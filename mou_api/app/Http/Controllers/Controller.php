<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    protected function getUserAuth()
    {
        return $this->authService->getUserAuth();
    }

    protected function connectycube($id)
    {
        return $this->authService->addConnect($id);
    }
}
