<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //        $this->middleware('guest');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param $type
     * @return \App\User
     */
    protected function create($request)
    {
        $user = null;

        \DB::transaction(function () use ($request, &$user) {
            $data = $request->all();
            if ($request->hasFile('avatar')) {
                $data['avatar'] = User::uploadOrigin($request->file('avatar'), User::$subFolder);
            }
            $data['password'] = Hash::make(Str::random(20));
            $user = User::create($data);
        });

        event(new Registered($user));

        //trim phone: user middleware: TrimPhoneNumber
        //        $data["phone_number"] = ltrim($data["phone_number"] ?? "", "0"); //remove first zero
        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     *
     * @return UserResource|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function register(RegisterUserRequest $request)
    {
        $user = $this->create($request);

        if ($request->expectsJson()) {
            UserResource::withoutWrapping();

            return new UserResource($user);
        }
        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
