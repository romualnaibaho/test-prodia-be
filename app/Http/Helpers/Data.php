<?php

namespace App\Http\Helpers;

use Exception;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Data {
    public function getDataUser($token) {
        $jwtSecret = env('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

            return (object) [
                "data" => $decoded,
            ];
        } catch (Exception $e) {
            return (object) [
                "data" => null,
            ];
        }
    }
}