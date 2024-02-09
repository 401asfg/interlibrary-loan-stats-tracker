<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Library;

class LibraryController extends Controller
{
    public function index(Request $request) {
        $data = [];

        if ($request->has('q')) {
            $data = Library::select('id', 'name')
                            ->where('name', 'LIKE', '%' . $request->q . '%')
                            ->get();
        }

        // FIXME: does the data have to be validate before sending?
        // FIXME: does the data have to be in a json reponse?
        return response()->json($data);
    }
}
