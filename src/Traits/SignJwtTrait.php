<?php

namespace Sak\Core\Traits;

use Firebase\JWT\JWT;

/**
 * Trait SignJwtTrait
 * @package Sak\Core\Traits
 */
trait SignJwtTrait
{

    /** sign a jwt for global service
     * @return string
     */
    public function signGlobal()
    {
        $privateKey = config('sak.global_jwt.private_key');
        $privateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
{$privateKey}
-----END PRIVATE KEY-----
EOD;
        $time = time();
        $payload = [
            "iat"         => $time,
            "exp"         => $time + config('sak.global_jwt.exp'),
            "client_type" => 2,
            "iss"         => config('sak.global_jwt.iss')
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }
}
