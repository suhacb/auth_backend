<?php

namespace App\Services\Auth;

use App\Contracts\Auth\ApplicationService as ApplicationServiceContract;
use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;

class ApplicationService implements ApplicationServiceContract
{
    public function __construct(public ?Application $application = null)
    {
        if (!$application) {
            $this->application = new Application();
        }
    }

    public function index(): Collection
    {
        return $this->application->get();
    }

    public function store(array $data): Application
    {
        return Application::create($data);
    }

    public function show (Application $application): Application
    {

    }

    public function update(Application $application, array $data): Application
    {
        return $application->update($data)->fresh();
    }

    public function delete(Application $application): void {

    }
}