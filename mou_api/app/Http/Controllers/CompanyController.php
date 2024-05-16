<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyEmployeeRequest;
use App\Http\Requests\RegisterCompanyRequest;
use App\Http\Resources\CompanyEmployeeResource;
use App\Http\Resources\CompanyOfEmployeeResource;
use App\Http\Resources\UserCompanyResource;
use App\Services\AuthService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    private $companyService;

    public function __construct(AuthService $authService, CompanyService $companyService)
    {
        parent::__construct($authService);
        $this->companyService = $companyService;
    }

    /**
     * Handle a registration company request for the application.
     *
     *
     * @return UserCompanyResource
     */
    public function registerCompany(RegisterCompanyRequest $request)
    {
        $this->companyService->registerCompany($request);

        UserCompanyResource::withoutWrapping();

        return new UserCompanyResource($this->getUserAuth());
    }

    /**
     * Me information
     *
     *
     * @return UserCompanyResource
     */
    public function me(Request $request)
    {
        $company = $this->authService->getCompany();
        abort_if(! $company, 500, 'You are not registered company!');
        UserCompanyResource::withoutWrapping();

        return new UserCompanyResource($this->getUserAuth());
    }

    /**
     * Update company profile
     *
     *
     * @return UserCompanyResource
     */
    public function updateCompanyProfile(RegisterCompanyRequest $request)
    {
        $user = $this->companyService->updateCompanyProfile($request->validated());

        UserCompanyResource::withoutWrapping();

        return new UserCompanyResource($user);
    }

    /**
     * Update company logo
     *
     *
     * @return UserCompanyResource
     */
    public function updateCompanyLogo(RegisterCompanyRequest $request)
    {
        $user = $this->companyService->updateCompanyLogo($request->file('logo'));

        UserCompanyResource::withoutWrapping();

        return new UserCompanyResource($user);
    }

    /**
     * Check User with phone number exist
     * Use middleware existUserPhone
     *
     * @return JsonResponse
     */
    public function existPhone()
    {
        $msg = 'NO_REGISTER_COMPANY';
        $company = $this->authService->getCompany();
        if ($company) {
            $msg = 'OK';
            // If employee of a company
            $user = $this->getUserAuth();
            if ($company->creator_id != $user->id) {
                // Check permission login in business app
                $permissionCompany = $this->authService->getPermissionCompany($company->id);
                if (! $permissionCompany->permission_access_business) {
                    $msg = 'NOT_PERMISSION';
                }
            }
        }

        return response()->json(['message' => $msg]);
    }

    /*
     * EMPLOYEE OF COMPANY
     */
    /**
     * Employee of Company
     *
     *
     * @return JsonResponse
     */
    public function addEmployee($companyID, CompanyEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = $this->companyService->addEmployeeIntoCompany($companyID, $data);
        if (! $employee) {
            return response()->json([
                'message' => __('employee.add_employee_error'),
            ], 500);
        }

        return response()->json(new CompanyEmployeeResource($employee), 200);
    }

    /**
     * Edit employee
     *
     * @return JsonResponse
     */
    public function updateEmployee($companyID, $employeeID, CompanyEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = $this->companyService->editEmployeeOfCompany($companyID, $employeeID, $data);

        return response()->json(new CompanyEmployeeResource($employee), 200);
    }

    /**
     * Delete employee
     *
     *
     * @return JsonResponse
     */
    public function destroyEmployee($companyID, $employeeID)
    {
        $deleted = $this->companyService->deleteEmployeeOfCompany($companyID, $employeeID);
        abort_if(! $deleted, 500, 'Employee does not exist!');

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Get list employees of company
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function employees($companyID)
    {
        return CompanyEmployeeResource::collection($this->companyService->getEmployeesOfCompany($companyID));
    }

    /**
     * Company list had invited to me
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function invitedToMe()
    {
        $status = [config('constant.event.status.waiting'), config('constant.event.status.confirm')];
        $company = $this->companyService->getCompanyHadInvitedToMe($status);

        return CompanyOfEmployeeResource::collection($company ? $company : []);
    }

    /**
     * Accept company's invitation
     *
     * @return JsonResponse
     */
    public function acceptInvitedToMe($companyId)
    {
        if ($this->companyService->acceptOrDenyCompanyInvitedToMe($companyId, true)) {
            return response()->json(['message' => 'OK'], 200);
        }

        return response()->json(['message' => 'Failed action!'], 500);
    }

    /**
     * Deny company's invitation
     *
     * @return JsonResponse
     */
    public function denyInvitedToMe($companyId)
    {
        if ($this->companyService->acceptOrDenyCompanyInvitedToMe($companyId, false)) {
            return response()->json(['message' => 'OK'], 200);
        }

        return response()->json(['message' => 'Failed action!'], 500);
    }

    public function workingDays(Request $request)
    {
        $data = $request->validate([
            'working_days' => 'required|array',
        ]);
        $user = $this->companyService->updateCompanyProfile($data);

        return new UserCompanyResource($user);
    }
}
