<?php

namespace App\Modules\Organization\Controllers;

use App\Modules\Organization\Interfaces\OrganizationServiceInterface;
use App\Modules\Organization\Models\Organization;
use HieuDev92264\LaravelModules\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrganizationController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly OrganizationServiceInterface $organizationService)
    {
        $this->middleware('auth:api');
    }

    public function dashboard(Request $request, Organization $organization): JsonResponse
    {
        $dashboard = $this->organizationService->getDashboardByUser(
            $request->user()->id,
            $organization,
        );

        return $this->success($dashboard, 'Dashboard retrieved successfully');
    }
}
