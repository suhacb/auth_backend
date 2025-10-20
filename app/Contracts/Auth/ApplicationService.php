<?php

namespace App\Contracts\Auth;

use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;

interface ApplicationService
{
    /**
     * Get all applications.
     *
     * @return Collection<Application>
     *
     * @throws Throwable  If the database query fails
     */
    public function index(): Collection;

    /**
     * Create a new application.
     *
     * @param  array  $data
     * @return Application
     *
     * @throws \Illuminate\Validation\ValidationException  If validation fails
     * @throws Throwable  If the database insert fails
     */
    public function store(array $data): Application;

    /**
     * Get a single application.
     *
     * @param  Application  $application
     * @return Application
     *
     * @throws ModelNotFoundException  If the application does not exist
     * @throws Throwable  If the database query fails
     */
    public function show(Application $application): Application;

    /**
     * Update an existing application.
     *
     * @param  Application  $application
     * @param  array  $data
     * @return Application
     *
     * @throws \Illuminate\Validation\ValidationException  If validation fails
     * @throws ModelNotFoundException  If the application does not exist
     * @throws Throwable  If the database update fails
     */
    public function update(Application $application, array $data): Application;

    /**
     * Delete an application (soft delete).
     *
     * @param  Application  $application
     * @return void
     *
     * @throws ModelNotFoundException  If the application does not exist
     * @throws Throwable  If the database delete fails
     */
    public function delete(Application $application): void;
}