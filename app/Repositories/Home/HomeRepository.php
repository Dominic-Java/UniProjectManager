<?php

declare(strict_types=1);

namespace App\Repositories\Home;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class HomeRepository
{
    /**
     * Returneaza statistici de baza pentru pagina Home.
     * Verifica existenta tabelelor pentru a evita erori la prima rulare.
     */
    public function getStats(): array
    {
        return [
            'projects' => $this->countIfExists('projects'),
            'teams' => $this->countIfExists('teams'),
            'deliverables' => $this->countIfExists('deliverables'),
        ];
    }

    /**
     * Numara inregistrarile dintr-un tabel doar daca acesta exista.
     */
    private function countIfExists(string $table): int
    {
        return Schema::hasTable($table)
            ? (int) DB::table($table)->count()
            : 0;
    }
}
