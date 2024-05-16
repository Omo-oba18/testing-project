<?php

namespace App\Http\Controllers\Auth;

use App\Enums\NotifySendTo;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyMailRequest;
use App\Providers\RouteServiceProvider;
use App\Services\AuthService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    protected $authService;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('guest')->except('logout');
        $this->authService = $authService;
    }

    /**
     * A function send mail verify before change phone number
     *
     * @return JsonResponse
     */
    public function sendVerifyEmailChangePhone(VerifyMailRequest $request)
    {
        // send mail verify
        $sendTo = new NotifySendTo((int) $request->get('send_to', NotifySendTo::PERSONAL));
        $this->authService->sendVerifyEmailChangePhone($request->email, $sendTo);

        return response()->json(['message' => __('change-phone.email_verify_send_to_you')]);
    }
}
