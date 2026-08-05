<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\SLAController;
use App\Http\Controllers\Api\V1\UlasanController;
use App\Http\Requests\SlaRequest;
use App\Http\Requests\Ulasan\UlasanRequest;

class TestApi extends Command
{
    protected $signature = 'test:api';
    protected $description = 'Test SLA and Ulasan';

    public function handle()
    {
        $this->info("--- Testing SLAController@index ---");
        try {
            $controller = app()->make(SLAController::class);
            $request = SlaRequest::create('/api/v1/sla', 'GET');
            $request->setContainer(app());
            $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
            $request->validateResolved();
            $response = $controller->index($request);
            $this->info("Status: " . $response->getStatusCode());
            $this->line("Content: " . substr($response->getContent(), 0, 500));
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
        }

        $this->info("\n--- Testing SLAController@samples ---");
        try {
            $controller = app()->make(SLAController::class);
            $request = \App\Http\Requests\SlaSampleRequest::create('/api/v1/sla/samples?kategori=tercepat', 'GET');
            $request->setContainer(app());
            $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
            $request->validateResolved();
            $response = $controller->samples($request);
            $this->info("Status: " . $response->getStatusCode());
            $this->line("Content: " . substr($response->getContent(), 0, 500));
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
        }

        $this->info("\n--- Testing UlasanController@kpi ---");
        try {
            $controller = app()->make(UlasanController::class);
            $request = UlasanRequest::create('/api/v1/ulasan/kpi', 'GET');
            $request->setContainer(app());
            $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
            $request->validateResolved();
            $response = $controller->kpi($request);
            $this->info("Status: " . $response->getStatusCode());
            $this->line("Content: " . substr($response->getContent(), 0, 500));
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
        }
    }
}
