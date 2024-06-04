<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;
use GuzzleHttp\Client;

class CustomerController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'project' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $data["role"] = "user";
        $data["count_message"] = 0;
        $data["limit_message"] = 200;

        $customer = Customer::create($data);

        $payload = [
            'sub' => $customer->id,
            'name' => $customer->name,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addYear(1)->timestamp
        ];

        $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

        return response()->json([
            'success' => true,
            'message' => 'User Registered',
            'data' => 'Bearer ' . $token
        ], 200);
    }
}
