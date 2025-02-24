<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Type;
use Exception;

use App\OTP;
use App\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/[A-Z]/|regex:/[0-9]/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }
        
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => false,
            ]);

            if (!$user) {
                return response()->json([
                    'data' => null,
                    'message' => "Gagal menyimpan data user. Silahkan coba lagi."
                ], Response::HTTP_BAD_REQUEST);
            }

            $otpCode = rand(100000, 999999);
            OTP::where('user_id', $user->id)->delete();

            $otp = OTP::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'expired_at' => Carbon::now()->addMinutes(5),
            ]);

            if (!$otp) {
                return response()->json([
                    'data' => null,
                    'message' => "Gagal membuat kode OTP. Silahkan coba lagi."
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();

            Mail::to($user->email)->send(new OtpMail($otpCode));

            $result = (object) [
                "userId" => $user->id,
                "email" => $user->email
            ];

            return response()->json([
                'data' => $result,
                'message' => 'Berhasil menyimpan data user. Silahkan verifikasi email anda.'
            ], Response::HTTP_OK);

        }
        catch(Exception $e) {
            if (strpos($e->getMessage(), "SQLSTATE")) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Keslahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
            'otpCode' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        $userId = $request->userId;
        $otpCode = $request->otpCode;

        try{
            $otp = OTP::where('user_id', $userId)->first();

            if (!$otp) {
                return response()->json([
                    'data' => null,
                    'message' => "Otp tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($otp->otp_code != $otpCode) {
                return response()->json([
                    'data' => null,
                    'message' => "Otp tidak sesuai."
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($otp->expired_at < Carbon::now()) {
                return response()->json([
                    'data' => null,
                    'message' => "Otp sudah tidak berlaku. Silahkan lakukan kirim ulang OTP."
                ], Response::HTTP_BAD_REQUEST);
            }

            $now = Carbon::now();

            $user = $otp->user;
            $user->is_active = true;
            $user->email_verified_at = $now;
            $user->last_login_at = $now;
            $user->save();

            $otp->delete();

            $payload = [
                'sub' => $user->id,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->addDay()->timestamp,
            ];
    
            // Buat token menggunakan key rahasia dari .env
            $jwtSecret = env('JWT_SECRET');
            $token = JWT::encode($payload, $jwtSecret, 'HS256');

            $result = (object) [
                "email" => $user->email,
                "accessToken" => $token
            ];

            return response()->json([
                'data' => $result,
                'message' => 'Verifikasi Berhasil.'
            ], Response::HTTP_OK);
        }
        catch(Exception $e) {
            if (strpos($e->getMessage(), "SQLSTATE")) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Keslahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        $userId = $request->userId;

        try {
            $user = User::where('id', $userId)->first();

            if (!$user) {
                return response()->json([
                    'data' => null,
                    'message' => "User tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            $otpUser = OTP::where('user_id', $user->id)->first();

            if (!empty($otpUser) && $otpUser->expired_at > Carbon::now()) {

                return response()->json([
                    'data' => null,
                    'message' => "Pengiriman ulang OTP tidak diizinkan. Silahkan coba kembali dalam ".max(Carbon::now()->diffInMinutes($otpUser->expired_at, false), 0)." menit"
                ], Response::HTTP_BAD_REQUEST);
            }

            $otpCode = rand(100000, 999999);
            OTP::where('user_id', $user->id)->delete();

            $otp = OTP::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'expired_at' => Carbon::now()->addMinutes(5),
            ]);

            if (!$otp) {
                return response()->json([
                    'data' => null,
                    'message' => "Gagal membuat kode OTP. Silahkan coba lagi."
                ], Response::HTTP_BAD_REQUEST);
            }

            Mail::to($user->email)->send(new OtpMail($otpCode));

            return response()->json([
                'data' => true,
                'message' => 'Berhasil mengirim ulang kode OTP. Silahkan periksa dan verifikasi email anda.'
            ], Response::HTTP_OK);
        }
        catch(Exception $e) {
            if (strpos($e->getMessage(), "SQLSTATE")) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Keslahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $userEmail = $request->email;
        $userPass = $request->password;

        try {
            $user = User::where('email', $userEmail)->first();

            if (!$user) {
                return response()->json([
                    'data' => null,
                    'message' => "User tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            if (!Hash::check($userPass, $user->password)) {
                return response()->json([
                    'data' => null,
                    'message' => "Email / Sandi tidak sesuai."
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($user->is_active == Type::USER_INACTIVE && is_null($user->email_verified_at)) {
                $otpUser = OTP::where('user_id', $user->id)->first();

                $resMsg = "Email belum diverifikasi. Silahkan verifikasi email anda.";

                if ($otpUser->expired_at < Carbon::now()) {
                    $resMsg = "Email belum diverifikasi dan OTP sudah kadaluarsa. Silahkan kirim ulang OTP.";
                }

                $result = (object) [
                    "status" => Type::USER_INACTIVE,
                    "userId" => $user->id,
                    "email" => $user->email
                ];

                return response()->json([
                    'data' => $result,
                    'message' => $resMsg
                ], Response::HTTP_OK);
                
            }

            if ($user->is_active == Type::USER_INACTIVE) {
                return response()->json([
                    'data' => null,
                    'message' => "Akun anda telah dinonaktifkan."
                ], Response::HTTP_BAD_REQUEST);
            }

            User::where('email', $userEmail)->update([
                "last_login_at" => Carbon::now()
            ]);

            $payload = [
                'sub' => $user->id,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->addDay()->timestamp,
            ];
    
            // Buat token menggunakan key rahasia dari .env
            $jwtSecret = env('JWT_SECRET');
            $token = JWT::encode($payload, $jwtSecret, 'HS256');

            $result = (object) [
                "status" => Type::USER_ACTIVE,
                "email" => $user->email,
                "accessToken" => $token
            ];

            return response()->json([
                'data' => $result,
                'message' => 'Login Berhasil.'
            ], Response::HTTP_OK);

        }
        catch(Exception $e) {
            if (strpos($e->getMessage(), "SQLSTATE")) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Keslahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }
            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
