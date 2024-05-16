<?php

namespace App\Firebase;

use Kreait\Firebase\JWT\IdTokenVerifier;

class Guard
{
    protected $verifier;

    public function __construct(IdTokenVerifier $verifier)
    {
        $this->verifier = $verifier;
    }

    public function user($request)
    {
        $token = $request->bearerToken();
        try {
            $token = $this->verifier->verifyIdToken($token);

            return new UserFirebase($token->payload());
        } catch (\Exception $e) {
            return;
        }
    }
}
