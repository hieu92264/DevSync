<?php

namespace App\Modules\Identity\Controllers;

use App\Modules\Authorization\Services\AuthorizationServiceInterface;
use App\Modules\Authorization\Services\RequestContext;
use App\Modules\Identity\Interfaces\IdentityServiceInterface;
use HieuDev92264\LaravelModules\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly IdentityServiceInterface $identityService,
        protected readonly AuthorizationServiceInterface $authorizationService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['nullable', 'email', 'required_without:login'],
            'login' => ['nullable', 'string', 'required_without:email'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('email', $request->input('login'));

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';
        $credentials = [$fieldType => $loginInput, 'password' => $request->password, 'is_active' => true];

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return $this->error(null, 'Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        Auth::guard('api')->user()?->updateQuietly(['last_login_at' => now()]);

        return $this->respondWithToken($token, $request->user() ?? Auth::guard('api')->user());
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return $this->success(null, 'Logged out successfully', 200);
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return $this->success($user->only(['id', 'user_name', 'email']), 'User retrieved successfully', 200);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    protected function respondWithToken(string $token, $user = null): JsonResponse
    {
        return $this->apiResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user?->only(['id', 'user_name', 'email']),
        ], 'Success', Response::HTTP_OK);
    }

    public function organizations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->identityService->getOrganizationByUser($userId);

        return $this->success($data, 'Organizations retrieved successfully', 200);
    }

    public function projects(Request $request): JsonResponse
    {
        $organization = app(RequestContext::class)->organization;

        return $this->success(
            $this->identityService->getProjectsByUser($request->user()->id, $organization->id),
            'Projects retrieved successfully',
        );
    }

    public function context(Request $request): JsonResponse
    {
        $context = app(RequestContext::class);

        return $this->success([
            'organization' => $context->organization->only(['id', 'code', 'name']),
            'project' => $context->project->only(['id', 'code', 'name']),
            'membership' => $context->projectMember?->only(['id', 'team_type']),
            'roles' => $this->authorizationService->projectRoleCodes($context),
            'permissions' => $this->authorizationService->permissionCodes($request->user(), $context),
        ], 'Context retrieved successfully');
    }
}
