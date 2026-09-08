<?php

namespace App\Modules\Identity\Controllers;

use HieuDev92264\LaravelModules\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    public function login(Request $request): JsonResponse {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('login');

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';
        $credentials = [$fieldType => $loginInput, 'password' => $request->password];

        if(!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->error(null, 'Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        return $this->success(null, 'Logged out successfully', 200);
    }

    public function me() {
        $user = Auth::guard('api')->user();
        return $this->success($user, 'User retrieved successfully', 200);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return $this->apiResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ], 'Success', Response::HTTP_OK);
    }
}
