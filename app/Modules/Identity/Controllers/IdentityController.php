<?php

namespace App\Modules\Identity\Controllers;

use HieuDev92264\LaravelModules\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class IdentityController extends Controller
{

    use ApiResponse;
    public function login(Request $request) {}

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        return $this->success(null, 'Logged out successfully', 200);
    }
    public function me() {}
}
