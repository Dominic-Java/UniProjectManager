<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamsController extends Controller
{
    public function index()
    {
        $teams = [];
        $columns = [];
        $tableExists = Schema::hasTable('teams');

        if ($tableExists) {
            $columns = Schema::getColumnListing('teams');
            $teams = DB::table('teams')->limit(20)->get()->toArray();
        }

        return view('teams.index', [
            'title' => 'Echipe',
            'table_exists' => $tableExists,
            'columns' => $columns,
            'rows' => $teams,
        ]);
    }
}
