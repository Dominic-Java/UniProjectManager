<?php

namespace App\Services\Home;

use App\Repositories\Home\HomeRepository;

class HomeService
{
    public function __construct(private HomeRepository $repo) {}

    public function getHomeData(): array
    {
        $stats = $this->repo->getStats();

        return [
            'title' => 'UniProjectManager',
            'subtitle' => 'Dashboard - management proiecte studentesti',
            'quick_actions' => [
                ['label' => 'Creeaza proiect', 'href' => '/projects/create'],
                ['label' => 'Vezi proiecte', 'href' => '/projects'],
                ['label' => 'Echipe', 'href' => '/teams'],
                ['label' => 'Livrabile', 'href' => '/deliverables'],
            ],
            'stats' => $stats,
            'announcements' => [
                'Adauga primul proiect si defineste etapele.',
                'Creeaza echipe si asociaza studentii.',
                'Incarca livrabile si ofera feedback.',
            ],
        ];
    }
}
