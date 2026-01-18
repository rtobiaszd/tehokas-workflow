<?php

namespace App\Http\Controllers;

use App\Models\WorkflowLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view-logs');

        $logs = WorkflowLog::query()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Logs/Index', [
            'logs' => $logs,
        ]);
    }
}
