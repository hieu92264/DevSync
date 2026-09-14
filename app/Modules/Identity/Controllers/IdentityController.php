<?php

namespace App\Modules\Identity\Controllers;

use App\Modules\Identity\Interfaces\IdentityServiceInterface;
use App\Shared\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly IdentityServiceInterface $identityService)
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('login');

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';
        $credentials = [$fieldType => $loginInput, 'password' => $request->password];

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return $this->error(null, 'Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        Auth::guard('api')->user()?->updateQuietly(['last_login_at' => now()]);

        return $this->respondWithToken($token);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return $this->success(null, 'Logged out successfully', 200);
    }

    public function me(): JsonResponse
    {
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
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ], 'Success', Response::HTTP_OK);
    }

    public function organizations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->identityService->getOrganizationByUser($userId);

        return $this->success($data, 'Organizations retrieved successfully', 200);
    }
}
