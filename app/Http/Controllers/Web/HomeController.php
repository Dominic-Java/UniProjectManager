<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Home\HomeService;

class HomeController extends Controller
{
    public function __construct(private HomeService $homeService) {}

    public function index()
    {
        return view('home.index', $this->homeService->getHomeData());
    }
}
