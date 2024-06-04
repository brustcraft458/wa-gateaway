<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Customer;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SystemMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $jwt = $request->bearerToken();

        if ($jwt == 'null' || $jwt == '') {
            return response()->json([
                'success' => false,
                'message' => 'Error Token',
            ], 401);
        }

        $jwtDecoded = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $customer = Customer::find($jwtDecoded->sub);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found',
            ], 401);
        }

        if ($customer->role != 'system') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Role',
            ], 401);
        }

        return $next($request);
    }
}
