<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexDashboardRequest;
use App\Http\Resources\Dashboard\DashboardResource;
use App\Services\DashboardService;

final class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {
    }

    public function index(
        IndexDashboardRequest $request
    ): DashboardResource {
        return new DashboardResource(
            $this->service->getDashboard(
                $request->validated()
            )
        );
    }
}