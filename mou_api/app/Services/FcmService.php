<?php

namespace App\Services;

use App\UserDeviceFcm;

/**
 * Firebase FCM service
 *
 * Class FcmService
 */
class FcmService
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Save FCM token in DB
     *
     * @param  bool  $isBusinessApp
     */
    public function saveToken(array $data, $isBusinessApp = true)
    {
        $user = $this->authService->getUserAuth();
        // check if exist
        $fcmToken = UserDeviceFcm::where('token', $data['token'])->first();
        if ($fcmToken) {
            if ($fcmToken->user_id != $user->id) {
                $fcmToken->update([
                    'user_id' => $user->id,
                    'device' => $data['device'],
                    'app' => $isBusinessApp ? UserDeviceFcm::BUSINESS_APP : UserDeviceFcm::PERSONAL_APP,
                ]);
            }
        } else {
            $data['app'] = $isBusinessApp ? UserDeviceFcm::BUSINESS_APP : UserDeviceFcm::PERSONAL_APP;
            $user->deviceFCMs()->create($data);
        }
    }

    /**
     * Delete FCM token in DB
     */
    public function deleteToken($token)
    {
        $user = $this->authService->getUserAuth();
        UserDeviceFcm::where('token', $token)->where('user_id', $user->id)->delete();
    }
}
