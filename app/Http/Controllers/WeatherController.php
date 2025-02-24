<?php

namespace App\Http\Controllers;

use App\Location;
use App\SysInfo;
use App\CloudData;
use App\WeatherCondition;
use App\WeatherData;
use App\WindData;

use App\Http\Helpers\Data;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;

class WeatherController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $token = $request->header('X-Prodia-Token');

            if (empty($token)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $get = new Data;
            $userData = $get->getDataUser($token);

            if (empty($userData->data)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = User::where('id', $userData->data->sub)->first();

            if (empty($user)) {
                return response()->json([
                    'data' => null,
                    'message' => "Data user tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            // Cek token expiration date
            if (Carbon::now()->timestamp > $userData->data->exp) {
                return response()->json([
                    'data' => null,
                    'message' => "Waktu akses sudah habis. Silahkan login kembali."
                ], Response::HTTP_BAD_REQUEST);
            }

            // Simpan lokasi jika belum ada
            $weatherData = Location::with([
                'weatherConditions.weatherData',
                'weatherConditions.windData',
                'weatherConditions.cloudData',
                'weatherConditions.sysInfo'
            ])->get();

            return response()->json([
                'data' => $weatherData,
                'message' => 'Berhasil menyimpan data cuaca.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            if (strpos($e->getMessage(), "SQLSTATE") !== false) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Kesalahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getLocations(Request $request)
    {
        try {
            $token = $request->header('X-Prodia-Token');

            if (empty($token)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $get = new Data;
            $userData = $get->getDataUser($token);

            if (empty($userData->data)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = User::where('id', $userData->data->sub)->first();

            if (empty($user)) {
                return response()->json([
                    'data' => null,
                    'message' => "Data user tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            // Cek token expiration date
            if (Carbon::now()->timestamp > $userData->data->exp) {
                return response()->json([
                    'data' => null,
                    'message' => "Waktu akses sudah habis. Silahkan login kembali."
                ], Response::HTTP_BAD_REQUEST);
            }

            $weatherData = Location::get();

            return response()->json([
                'data' => $weatherData,
                'message' => 'Berhasil mengambil data cuaca.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            if (strpos($e->getMessage(), "SQLSTATE") !== false) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Kesalahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getLocationDetail($id, Request $request)
    {
        try {
            $token = $request->header('X-Prodia-Token');

            if (empty($token)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $get = new Data;
            $userData = $get->getDataUser($token);

            if (empty($userData->data)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = User::where('id', $userData->data->sub)->first();

            if (empty($user)) {
                return response()->json([
                    'data' => null,
                    'message' => "Data user tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            // Cek token expiration date
            if (Carbon::now()->timestamp > $userData->data->exp) {
                return response()->json([
                    'data' => null,
                    'message' => "Waktu akses sudah habis. Silahkan login kembali."
                ], Response::HTTP_BAD_REQUEST);
            }

            $weatherData = Location::with([
                'weatherConditions' => function ($query) {
                    $query->orderBy('id', 'desc');
                },
                'weatherConditions.weatherData',
                'weatherConditions.windData',
                'weatherConditions.cloudData',
                'weatherConditions.sysInfo'
            ])->where('id', $id)->first();

            return response()->json([
                'data' => $weatherData,
                'message' => 'Berhasil mengambil data cuaca.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            if (strpos($e->getMessage(), "SQLSTATE") !== false) {
                return response()->json([
                    'data' => null,
                    'message' => "Terjadi Kesalahan, silahkan coba kembali"
                ], Response::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function insert(Request $request)
    {
        $data = $request->all();

        DB::beginTransaction();

        try {
            $token = $request->header('X-Prodia-Token');

            if (empty($token)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $get = new Data;
            $userData = $get->getDataUser($token);

            if (empty($userData->data)) {
                return response()->json([
                    'data' => null,
                    'message' => "Anda tidak memiliki akses."
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = User::where('id', $userData->data->sub)->first();

            if (empty($user)) {
                return response()->json([
                    'data' => null,
                    'message' => "Data user tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            // Cek token expiration date
            if (Carbon::now()->timestamp > $userData->data->exp) {
                return response()->json([
                    'data' => null,
                    'message' => "Waktu akses sudah habis. Silahkan login kembali."
                ], Response::HTTP_BAD_REQUEST);
            }

            // Simpan lokasi jika belum ada
            $location = Location::firstOrCreate([
                'name' => $data['name'],
                'country' => $data['sys']['country'],
                'longitude' => $data['coord']['lon'],
                'latitude' => $data['coord']['lat'],
            ]);

            // Simpan kondisi cuaca
            if (!empty($data['weather'])) {
                foreach ($data['weather'] as $weather) {
                    $weatherCondition = WeatherCondition::create([
                        'location_id' => $location->id,
                        'main' => $weather['main'],
                        'description' => $weather['description'],
                        'icon' => $weather['icon'],
                    ]);

                    // Simpan data cuaca utama
                    WeatherData::create([
                        'weather_condition_id' => $weatherCondition->id,
                        'temperature' => $data['main']['temp'],
                        'feels_like' => $data['main']['feels_like'],
                        'temp_min' => $data['main']['temp_min'],
                        'temp_max' => $data['main']['temp_max'],
                        'pressure' => $data['main']['pressure'],
                        'humidity' => $data['main']['humidity'],
                        'visibility' => $data['visibility'],
                        'timestamp' => Carbon::createFromTimestamp($data['dt']),
                    ]);

                    // Simpan data angin
                    WindData::create([
                        'weather_condition_id' => $weatherCondition->id,
                        'speed' => $data['wind']['speed'],
                        'direction' => $data['wind']['deg'],
                    ]);

                    // Simpan data awan
                    CloudData::create([
                        'weather_condition_id' => $weatherCondition->id,
                        'cloudiness' => $data['clouds']['all'],
                    ]);

                    // Simpan informasi sistem (sunrise, sunset, timezone)
                    SysInfo::create([
                        'weather_condition_id' => $weatherCondition->id,
                        'sunrise' => Carbon::createFromTimestamp($data['sys']['sunrise']),
                        'sunset' => Carbon::createFromTimestamp($data['sys']['sunset']),
                        'timezone_offset' => $data['timezone'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'data' => true,
                'message' => 'Berhasil menyimpan data cuaca.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            // if (strpos($e->getMessage(), "SQLSTATE") !== false) {
            //     return response()->json([
            //         'data' => null,
            //         'message' => "Terjadi Kesalahan, silahkan coba kembali"
            //     ], Response::HTTP_BAD_REQUEST);
            // }

            return response()->json([
                'data' => null,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
