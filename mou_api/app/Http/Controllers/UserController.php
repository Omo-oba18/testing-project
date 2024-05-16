<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Helpers\Country;
use App\Helpers\Util;
use App\Http\Requests\ChangePhoneRequest;
use App\Http\Requests\FcmTokenRequest;
use App\Http\Resources\SearchUserResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\FcmService;
use App\User;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Storage;

class UserController extends Controller
{
    private $fcmService;

    public function __construct(AuthService $authService, FcmService $fcmService)
    {
        parent::__construct($authService);

        $this->fcmService = $fcmService;
    }

    /**
     * Me information
     *
     *
     * @return UserResource
     */
    public function me(Request $request)
    {
        UserResource::withoutWrapping();

        return new UserResource($request->user()->user);
    }

    /**
     * Update profile
     *
     *
     * @return UserResource
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function updateMe(Request $request)
    {
        $user = \Auth::user()->user;

        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'birthday' => ['required', 'date_format:Y-m-d'],
            'gender' => ['required', Rule::in(config('constant.gender'))],
            'country_code' => ['required', Rule::in(array_keys(Country::getCountries()))],
            'city' => ['required', 'string', 'max:255'],
        ]);
        $data = $request->only(['name', 'email', 'birthday', 'gender', 'country_code', 'city']);
        $user->update($data);

        UserResource::withoutWrapping();

        return new UserResource($user);
    }

    public function updateMeAvatar(Request $request)
    {
        $user = \Auth::user()->user;

        $this->validate($request, [
            'avatar' => ['required', 'image', 'mimes:jpeg,bmp,png,jpg,gif', 'max:'.User::AVATAR_MAXSIZE],
        ]);
        \DB::transaction(function () use ($request, &$user) {
            if ($request->hasFile('avatar')) {
                //delete old avatar
                User::deleteImage($user->avatar);
                //store new avatar
                $avatar = User::uploadOrigin($request->file('avatar'), User::$subFolder);
                $user->update(['avatar' => $avatar]);
            }
        });
        UserResource::withoutWrapping();

        return new UserResource($user);
    }

    /**
     * Check User with phone number exist
     * Use middleware existUserPhone
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function existPhone()
    {
        return response()->json(['message' => 'ok']);
    }

    /**
     * Search user by name
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function search(Request $request)
    {
        //        $user = User::where(\DB::raw("CONCAT(`dial_code`, `phone_number`)"), $phone);
        $keyword = trim($request->get('q'));
        if (! empty($keyword)) {
            $keyword = Util::escape_like($keyword);
            $users = User::where('name', 'like', "%$keyword%");
            //            $users = User::where(function(Builder $query) use ($keyword){
            //                $query->where('name', 'like', "%$keyword%")
            //                      ->orWhere(\DB::raw("CONCAT(`dial_code`, `phone_number`)"), $keyword)
            //                      ->orWhere('phone_number', $keyword)
            //                      ->orWhere('email', $keyword);
            //            });
            if (\Auth::check()) {
                $user = \Auth::user()->user;
                $users = $users->where('id', '!=', $user->id)
                    ->whereNotIn('id', function ($query) use ($user) {
                        $query->select('user_contact_id')
                            ->from(with(new Contact())->getTable())
                            ->where('user_id', $user->id)
                            ->whereNotNull('user_contact_id');
                    });
            }
            $users = $users->take(20)->get();
        }

        SearchUserResource::withoutWrapping();

        return SearchUserResource::collection($users ?? []);
    }

    /**
     * Update setting
     *
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setting(Request $request)
    {
        $user = \Auth::user()->user;

        $this->validate($request, [
            'busy_mode' => ['nullable', 'boolean'],
            'language_code' => ['nullable', Rule::in(config('constant.languages'))],
        ]);
        $data = $request->all();
        $user->setting()->updateOrCreate(['user_id' => $user->id], $data);

        return response()->json(['message' => 'ok']);
    }

    /**
     * Import Facebook
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function connectFacebook(Request $request)
    {
        $this->validate($request, [
            'facebook_id' => ['required'],
            'friends' => ['nullable', 'array'],
            'friends.*' => ['required'],
        ]);
        $user = \Auth::user()->user;
        $user->update($request->only('facebook_id'));
        $friends = $request->get('friends');
        if (! empty($friends)) {
            $userContactSearchs = User::where('id', '!=', $user->id)->whereIn('facebook_id', $friends)->orderBy('name')->get();
        }
        SearchUserResource::withoutWrapping();

        return SearchUserResource::collection($userContactSearchs ?? []);
    }

    /**
     * Import facebook contacts
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function importFriendsFacebook(Request $request)
    {
        $this->validate($request, [
            'friends' => ['required', 'array'],
            'friends.*' => ['required'],
        ]);
        $user = \Auth::user()->user;
        $friends = $request->get('friends');
        if (! empty($friends)) {
            $userContactSearchs = User::select('id')->where('id', '!=', $user->id)->whereDoesntHave('linkContacts', function (Builder $query) use ($user) {
                $query->where('user_id', '=', $user->id);
            })->whereIn('facebook_id', $friends)->get();
            if ($userContactSearchs) {
                $data = [];
                foreach ($userContactSearchs as $u) {
                    $data[] = [
                        'user_contact_id' => $u->id,
                    ];
                }
                $user->contacts()->createMany($data);
            }
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * Save user token of user's device
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveFCMToken(FcmTokenRequest $request)
    {
        $this->fcmService->saveToken($request->validated(), false);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Save fcm token of user's device - business app
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveFCMTokenBusinessApp(FcmTokenRequest $request)
    {
        $this->fcmService->saveToken($request->validated(), true);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Save user token of user's device
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroyFCMToken($token)
    {
        $this->fcmService->deleteToken($token);

        return response()->json(['message' => 'OK']);
    }

    public function getAvatar($id)
    {
        $user = User::select('avatar')->where('connectycube_id', $id)->first();
        if (! $user) {
            return 'User not found!';
        }
        $path = public_path(Storage::url($user->avatar));
        if (file_exists($path)) {
            return response()->file($path);
        }

        return "File: $path doesn\'t exists";
    }

    /**
     * Change phone number by user
     */
    public function changePhone(ChangePhoneRequest $request)
    {
        $this->authService->changePhoneNumber($request->validated());

        return response()->json(['message' => __('change-phone.phone_number_updated')]);
    }

    public function destroy()
    {
        try {
            DB::beginTransaction();
            $user = auth()->user()->user;
            $company = $user->company;
            $user->delete();
            DB::commit();
            Util::file_delete([$user->avatar, $company?->logo ?? '']);

            return response()->noContent();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
