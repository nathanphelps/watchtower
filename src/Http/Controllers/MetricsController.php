<?php

namespace NathanPhelps\Watchtower\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use NathanPhelps\Watchtower\Services\MetricsCollector;

class MetricsController extends Controller
{
    public function __construct(
        protected MetricsCollector $metricsCollector
    ) {}

    /**
     * Display the metrics dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('watchtower::Metrics', [
            'stats' => $this->metricsCollector->getStats(),
            'hourlyThroughput' => $this->metricsCollector->getHourlyThroughput(),
            'queueDepths' => $this->metricsCollector->getQueueDepths(),
            'averageDurations' => $this->metricsCollector->getAverageDurations(),
        ]);
    }
}
