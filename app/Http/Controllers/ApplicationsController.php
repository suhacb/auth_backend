<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ApplicationRequest;
use App\Services\Auth\ApplicationService;

class ApplicationsController extends Controller
{
    // public function __construct(private ?ApplicationService $applicationService = null) {
    //     if (!$applicationService) {
    //         $this->applicationService = new ApplicationService();
    //     }
    // }

    public function index(): JsonResponse
    {
        // return response()->json($this->applicationService->index(), 200);
        return response()->json(Application::get(), 200);
    }

    public function store(ApplicationRequest $request): JsonResponse
    {
        // return response()->json($this->applicationService->store($request->validated()), 201);
        return response()->json(Application::create($request->validated()), 201);
    }

    public function update(Application $application, ApplicationRequest $request): JsonResponse
    {
        $application->update($request->validated());
        return response()->json($application->fresh(), 200);
    }

    public function delete(Application $application): JsonResponse
    {
        $application->delete();
        return response()->json(null, 204);
    }
}
