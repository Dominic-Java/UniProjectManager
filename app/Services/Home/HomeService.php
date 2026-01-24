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
            'subtitle' => 'Dashboard — management proiecte studențești',
            'quick_actions' => [
                ['label' => 'Creează proiect', 'href' => '/projects/create'],
                ['label' => 'Vezi proiecte', 'href' => '/projects'],
                ['label' => 'Echipe', 'href' => '/teams'],
                ['label' => 'Livrabile', 'href' => '/deliverables'],
            ],
            'stats' => $stats,
            'announcements' => [
                'Adaugă primul proiect și definește etapele.',
                'Creează echipe și asociază studenții.',
                'Încarcă livrabile și oferă feedback.',
            ],
        ];
    }
}
