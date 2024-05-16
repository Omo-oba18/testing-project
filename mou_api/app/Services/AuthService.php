<?php

namespace App\Services;

use App\Company;
use App\CompanyEmployee;
use App\Enums\EmployeePermission;
use App\Enums\EmployeePermissionColumn;
use App\Enums\NotifySendTo;
use App\Notifications\VerifyEmailPhoneChange;
use App\User;
use App\VerifyEmail;
use Illuminate\Database\Eloquent\Model;

class AuthService
{
    /**
     * Get User by auth
     *
     * @return User
     */
    public function getUserAuth()
    {
        //TODO tmp
        // return User::find(2);//where('id',1)->
        return \Auth::user()->user;
    }

    /*
     * BUSINESS APP
     */
    /**
     * Get My company info by user login
     *
     * @return mixed|null
     */
    public function getCompany()
    {
        $user = $this->getUserAuth();
        // Company of user create
        if ($company = $user->company()->first()) {
            return $company;
        } elseif ($user->linkContacts->count() > 0) {
            //Company of employee (by contact)
            $companyEmployee = CompanyEmployee::employeeConfirmed()->whereIn('contact_id', $user->linkContacts->pluck('id'))->where('permission_access_business', EmployeePermission::ALLOW)->first();
            if ($companyEmployee) {
                return Company::where('id', $companyEmployee->company_id)->firstOrFail();
            }

            return null;
        }

        return null;
    }

    /**
     * Check user had one company (create a compnay or belongs to a company) or not
     *
     * @return bool
     */
    public function checkHadOneCompany($userID)
    {
        $user = User::findOrFail($userID);
        // Company of user create
        if ($company = $user->company()->first()) {
            return true;
        } elseif ($user->linkContacts->count() > 0) {
            //Company of employee (by contact)
            $companyEmployee = CompanyEmployee::whereIn('contact_id', $user->linkContacts->pluck('id'))->first();
            if ($companyEmployee) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Check user auth belongs to company
     */
    public function checkBelongToCompany($companyID)
    {
        $user = $this->getUserAuth();
        // Check company belongs to my creator
        if ($user->company && $user->company->id == $companyID) {
            return;
        } elseif ($user->linkContacts->count() > 0) {
            //Check Company of employee (by contact)
            $isCompanyEmployee = CompanyEmployee::employeeConfirmed()
                ->where('company_id', $companyID)
                ->whereIn('contact_id', $user->linkContacts->pluck('id'));
            if ($isCompanyEmployee->exists()) {
                return;
            }
        }
        abort(500, __('roster.employee_not_belong_to_permission_roster'));
    }

    /**
     * Check permission of user in company: add task/project/employee
     *
     *
     * @return void
     */
    public function checkPermissionAddInCompany($companyID, $checkColumnPermission)
    {
        $user = $this->getUserAuth();
        // Check company belongs to my creator
        if ($user->company && $user->company->id == $companyID) {
            return;
        } elseif ($user->linkContacts->count() > 0) {
            //Check Company of employee (by contact)
            $isCompanyEmployee = CompanyEmployee::employeeConfirmed()
                ->where('company_id', $companyID)
                ->whereIn('contact_id', $user->linkContacts->pluck('id'));
            if ($checkColumnPermission && in_array($checkColumnPermission, EmployeePermissionColumn::getValues())) {
                $isCompanyEmployee = $isCompanyEmployee->where('permission_access_business', EmployeePermission::ALLOW)->where($checkColumnPermission, EmployeePermission::ALLOW);
            }
            if ($isCompanyEmployee->exists()) {
                return;
            }
        }
        $msgPermission = '';
        switch ($checkColumnPermission) {
            case EmployeePermissionColumn::ADD_TASK:
                $msgPermission = __('validation.you_do_not_have_perrmission', ['action' => __('validation.add_task')]);
                break;

            case EmployeePermissionColumn::ADD_PROJECT:
                $msgPermission = __('validation.you_do_not_have_perrmission', ['action' => __('validation.add_project')]);
                break;

            case EmployeePermissionColumn::ADD_EMPLOYEE:
                $msgPermission = __('validation.you_do_not_have_perrmission', ['action' => __('validation.add_employee')]);
                break;

            case EmployeePermissionColumn::ADD_ROSTER:
                $msgPermission = __('validation.you_do_not_have_perrmission', ['action' => __('validation.add_roster')]);
                break;

            default:
                $msgPermission = __('roster.employee_not_belong_to_permission_roster');
                break;

        }
        abort(500, $msgPermission);
    }

    /**
     * Check permission of user in company: update task/project/employee
     */
    public function checkPermissionEditAndDeleteInCompany($companyID, Model $model)
    {
        $user = $this->getUserAuth();
        // Check company belongs to my creator
        if ($user->company && $user->company->id == $companyID) {
            return;
        } elseif ($model->creator_id == $user->id) {
            return;
        }
        abort(500, __('roster.employee_not_belong_to_permission_roster'));
    }

    /**
     * Get permission company by user login
     *
     * @param  User  $user
     * @return bool|CompanyEmployee
     */
    public function getPermissionCompany(int $company_id)
    {
        $user = $this->getUserAuth();
        if ($user->linkContacts->count() > 0) {
            $companyEmployee = CompanyEmployee::employeeConfirmed()->whereIn('contact_id', $user->linkContacts->pluck('id'))->where('company_id', $company_id)->first();
            if ($companyEmployee) {
                return $companyEmployee;
            }
        }

        return false;
    }

    public function addConnect($id)
    {
        $user = $this->getUserAuth();
        $user->connectycube_id = $id;
        $user->save();

        return [
            'status' => 200,
            'message' => __('contact.add_connectype_success'),
        ];
    }

    /**
     * Send mail verify for change phone
     */
    public function sendVerifyEmailChangePhone(string $email, NotifySendTo $sendTo): bool
    {
        // find user by email
        $user = User::firstWhere('email', $email);
        // prepare data for verify
        $token = \Str::uuid();
        $expiredAt = date('Y-m-d H:i:s', strtotime('+'.config('constant.expired_time_email_change_phone').' hours'));
        // update or creare record
        VerifyEmail::updateOrCreate([
            'email' => $email,
        ], [
            'token' => $token,
            'expired_at' => $expiredAt,
        ]);
        // send notify
        $user->notify(new VerifyEmailPhoneChange($token, $sendTo));

        return true;
    }

    /**
     * Change phone number by data
     */
    public function changePhoneNumber(array $data): bool
    {
        $user = User::firstWhere('email', $data['email']);
        // check user exists
        abort_if(! $user, 422, __('change-phone.email_incorrect'));

        $verifyEmail = VerifyEmail::firstWhere('email', $data['email']);
        // check token exists and expired
        abort_if(! $verifyEmail || $verifyEmail->expired_at < date('Y-m-d H:i:s'), 422, __('change-phone.token_expired'));
        \DB::transaction(function () use ($data, $verifyEmail, $user) {
            $user->phone_number = $data['phone_number'];
            $user->dial_code = $data['dial_code'];
            $user->save();
            $verifyEmail->delete();
        });

        return true;
    }
}
