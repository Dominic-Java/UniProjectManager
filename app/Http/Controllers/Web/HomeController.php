<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Home\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private HomeService $homeService) {}

    public function index()
    {
        $user = auth()->user();

        if ($user && $user->hasRole('profesor')) {
            return view('home.index', $this->homeService->getHomeData());
        }

        return view('home.student', $this->homeService->getStudentHomeData());
    }

    public function landing(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $variant = (string) $request->query('v', '2');
        $view = $variant === '2' ? 'home.landing_v2' : 'home.landing';

        return view($view, [
            'title' => 'UniProjectManager',
            'landing_variant' => $variant,
        ]);
    }
}
