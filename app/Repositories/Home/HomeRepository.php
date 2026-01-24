<?php

declare(strict_types=1);

namespace App\Repositories\Home;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class HomeRepository
{
    /**
     * Returnează statistici de bază pentru pagina Home.
     * Verifică existența tabelelor pentru a evita erori la prima rulare.
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
     * Numără înregistrările dintr-un tabel doar dacă acesta există.
     */
    private function countIfExists(string $table): int
    {
        return Schema::hasTable($table)
            ? (int) DB::table($table)->count()
            : 0;
    }
}
