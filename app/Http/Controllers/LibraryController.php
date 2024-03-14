<?php

/*
 * Author: Michael Allan
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Library;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $data = [];

        if ($request->has('query')) {
            $data = Library::select('id', 'name')
                ->where('name', 'LIKE', '%' . $request->input('query') . '%')
                ->get();
        }

        return response()->json($data);
    }

    public function show($id)
    {
        $name = Library::select('name')
            ->where('id', '=', $id)
            ->get()
            ->first();

        return response()->json($name);
    }
}
