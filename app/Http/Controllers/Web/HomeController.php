<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Home\HomeService;

class HomeController extends Controller
{
    public function __construct(private HomeService $homeService) {}

    public function index()
    {
        $user = auth()->user();

        if ($user && ($user->hasRole('profesor') || $user->isAdmin())) {
            return view('home.index', $this->homeService->getHomeData());
        }

        return view('home.student', $this->homeService->getStudentHomeData());
    }

    public function landing()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('home.landing_v2', [
            'title' => 'UniProjectManager',
        ]);
    }
}
