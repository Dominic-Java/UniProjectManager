<?php

namespace App\Services\Home;

use App\Repositories\Home\HomeRepository;
use Illuminate\Support\Facades\Auth;

class HomeService
{
    public function __construct(private HomeRepository $repo) {}

    public function getHomeData(): array
    {
        $stats = $this->repo->getStats();
        $user = Auth::user();

        $quickActions = [
            ['label' => 'Vezi proiecte', 'href' => '/projects'],
            ['label' => 'Echipe', 'href' => '/teams'],
            ['label' => 'Livrabile', 'href' => '/deliverables'],
        ];

        if ($user && $user->hasRole('admin', 'profesor')) {
            array_unshift($quickActions, ['label' => 'Creeaza proiect', 'href' => '/projects/create']);
        }

        return [
            'title' => 'UniProjectManager',
            'subtitle' => 'Dashboard - management proiecte studentesti',
            'quick_actions' => $quickActions,
            'stats' => $stats,
            'announcements' => [
                'Adauga primul proiect si defineste etapele.',
                'Creeaza echipe si asociaza studentii.',
                'Incarca livrabile si ofera feedback.',
            ],
        ];
    }

    public function getStudentHomeData(): array
    {
        return [
            'title' => 'Student Dashboard',
            'subtitle' => 'Bun venit in spatiul tau de lucru',
            'highlights' => [
                'Urmeaza livrabilele din proiecte.',
                'Colaboreaza cu echipa ta.',
                'Vezi feedbackul profesorilor.',
            ],
            'actions' => [
                ['label' => 'Vezi proiecte', 'href' => '/projects'],
                ['label' => 'Echipele mele', 'href' => '/teams'],
                ['label' => 'Livrabile', 'href' => '/deliverables'],
            ],
        ];
    }
}
