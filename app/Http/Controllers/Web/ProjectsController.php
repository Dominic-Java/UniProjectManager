<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Projects\ProjectsService;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function __construct(private ProjectsService $service) {}

    public function index()
    {
        return view('projects.index', $this->service->getIndexData());
    }

    public function create()
    {
        return view('projects.create', ['title' => 'Creeaza proiect']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $result = $this->service->createProject($validated);

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('projects.index')->with('success', $result['message']);
    }
}
