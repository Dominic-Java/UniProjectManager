<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeliverablesController extends Controller
{
    public function index()
    {
        $rows = [];
        $columns = [];
        $tableExists = Schema::hasTable('deliverables');

        if ($tableExists) {
            $columns = Schema::getColumnListing('deliverables');
            $rows = DB::table('deliverables')->limit(20)->get()->toArray();
        }

        return view('deliverables.index', [
            'title' => 'Livrabile',
            'table_exists' => $tableExists,
            'columns' => $columns,
            'rows' => $rows,
        ]);
    }
}
