<?php

namespace App\Services;

use App\Company;
use App\CompanyEmployee;
use App\Contact;
use App\Notifications\BusinessEmployeeAcceptOrDeny;
use App\Notifications\NotifyNewEmployee;
use App\User;
use Illuminate\Http\UploadedFile;

class CompanyService
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register company
     */
    public function registerCompany($request)
    {
        $user = $this->authService->getUserAuth();
        if ($user->company) {
            abort(500, 'You are already registered the company!');
        }

        $company = $this->authService->getCompany();
        if ($company) {
            abort(500, 'Not allowed to create because you already belong to another company!');
        }

        $data = $request->all();
        if ($request->hasFile('logo')) {
            try {
                $data['logo'] = Company::uploadOrigin($request->file('logo'), Company::$subFolder);
            } catch (\Exception $exception) {
                abort(500, $exception->getMessage());
            }
        }

        $result = $user->company()->create($data);
        if ($result) {
            $check = optional($user->contacts())->select('id')->where('user_contact_id', $user->id)->first();
            if (! $check) {
                $user->contacts()->create([
                    'user_contact_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Update logo
     *
     *
     * @return mixed
     */
    public function updateCompanyLogo(UploadedFile $logoFile)
    {
        // User login
        $user = $this->authService->getUserAuth();

        // Check permission
        if (! $user->company) {
            abort(500, 'Your company does not exist!');
        }

        //delete old logo
        Company::deleteImage($user->company->logo);
        //store new avatar
        $logo = Company::uploadOrigin($logoFile, Company::$subFolder);
        $user->company->update(['logo' => $logo]);

        return $user;
    }

    /**
     * Update company profile
     *
     *
     * @return User
     */
    public function updateCompanyProfile(array $data)
    {
        // User login
        $user = $this->authService->getUserAuth();

        // Check permission
        if (! $user->company) {
            abort(500, 'Your company does not exist!');
        }

        $user->company->update($data);

        return $user;
    }

    /**
     * Add employee (contact) into company
     *
     *
     * @return mixed
     */
    public function addEmployeeIntoCompany($companyID, array $data)
    {
        $this->authService->checkPermissionAddInCompany($companyID, 'permission_add_task');

        $existsEmployee = $this->checkEmployeeIsExistInCompany($companyID, $data['contact_id']);
        abort_if($existsEmployee, 500, 'Employee was added earlier');

        $creator = $this->authService->getUserAuth();

        $data['company_id'] = $companyID;
        $data['creator_id'] = $creator->id;

        $result = CompanyEmployee::create($data);
        if ($result) {
            $user = $result->contact->userContact;
            if ($user) {
                if (optional($user->setting)->busy_mode != 1) {
                    $lang = optional($user->setting)->language_code;
                    $user->notify(new NotifyNewEmployee($result, $lang));
                }
            } else {
                $lang = optional($result->contact->setting)->language_code;
                $result->contact->notify(new NotifyNewEmployee($result, $lang));
            }

            return $result;
        }

        return false;
    }

    /**
     * Update employee
     *
     *
     * @return mixed
     */
    public function editEmployeeOfCompany($companyID, $employeeID, array $data)
    {
        $employee = CompanyEmployee::where('id', $employeeID)->where('company_id', $companyID)->firstOrFail();
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $employee);

        $employee->update($data);

        return $employee;
    }

    /**
     * Delete employee of company
     *
     *
     * @return mixed
     */
    public function deleteEmployeeOfCompany($companyID, $employeeID)
    {
        // Get employee
        $employee = CompanyEmployee::where('id', $employeeID)->where('company_id', $companyID)->firstOrFail();
        // Check permission
        $this->authService->checkPermissionEditAndDeleteInCompany($companyID, $employee);

        return $employee->delete();
    }

    /**
     * Check Employee (contact) Belongs to Any company
     * OR had a company
     * OR belongs to a Company
     *
     *
     * @return mixed
     */
    private function checkEmployeeBelongsToAnyCompany($contactID)
    {
        $contact = Contact::findOrFail($contactID);
        $isHadOneCompany = $this->authService->checkHadOneCompany($contact->user_contact_id);
        if ($isHadOneCompany) {
            return true;
        }

        return false;
    }

    /**
     * Check Employee (contact) id is exists in company or user is exist in company
     *
     *
     * @return mixed
     */
    public function checkEmployeeIsExistInCompany($companyID, $contactID)
    {
        // 1. Check Employee (contact) id is exists in company
        $arContacts = [$contactID];

        // 2. Check User is exist in company
        // Get user of this contact
        $contact = Contact::findOrFail($contactID);
        if ($contact->userContact) {
            // Get all contact of this user
            $linkContacts = $contact->userContact->linkContacts()->pluck('id');
            $arContacts = array_merge($arContacts, $linkContacts->toArray());
        }

        // 3. Check contact is exist
        return CompanyEmployee::whereIn('contact_id', $arContacts)->where('company_id', $companyID)->exists();
    }

    /**
     * Get list employee of company
     *
     *
     * @return mixed
     */
    public function getEmployeesOfCompany($companyID)
    {
        return CompanyEmployee::where('company_id', $companyID)->with('contact', 'contact.userContact')->latest()->get();
    }

    /*
     * PERSONAL APP
     */
    /**
     * Get company list had invited to me
     *
     * @param  null  $withStatus
     * @param  bool  $isPaginate
     * @return mixed
     */
    public function getCompanyHadInvitedToMe($withStatus, $isPaginate = true)
    {
        $user = $this->authService->getUserAuth();
        if ($user->linkContacts->count() > 0) {
            $query = new CompanyEmployee();
            if ($withStatus && is_array($withStatus)) {
                $query = $query->whereIn('employee_confirm', $withStatus);
            } else {
                switch ($withStatus) {
                    case config('constant.event.status.confirm'):
                        $query = $query->employeeConfirmed();
                        break;
                    case config('constant.event.status.deny'):
                        $query = $query->employeeDenied();
                        break;
                    default:
                        $query = $query->employeeWaitingConfirm();
                }
            }
            $query = $query->with('company')->whereIn('contact_id', $user->linkContacts->pluck('id'))->latest();
            if ($isPaginate) {
                return $query->paginate();
            }

            return $query->get();
        }

        return null;
    }

    /**
     * Employee action - accept or deny company's invitation
     *
     * @param  bool  $isAccept
     * @return bool
     */
    public function acceptOrDenyCompanyInvitedToMe($companyId, $isAccept = true)
    {
        $user = $this->authService->getUserAuth();
        if ($user->linkContacts->count() > 0) {
            $newStatus = $isAccept ? config('constant.event.status.confirm') : config('constant.event.status.deny');
            $employee = CompanyEmployee::employeeWaitingConfirm()
                ->where('company_id', $companyId)
                ->whereIn('contact_id', $user->linkContacts->pluck('id'))->first();
            if ($employee) {
                $employee->update([
                    'employee_confirm' => $newStatus,
                ]);
                if ($employee->creator) {
                    $lang = optional($employee->creator?->setting)->language_code;
                    $employee->creator->notify(new BusinessEmployeeAcceptOrDeny($employee, $user, $lang));
                }

                return true;
            }
        }

        return false;
    }
}
