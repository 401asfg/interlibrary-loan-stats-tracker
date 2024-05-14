<?php

/*
 * Author: Michael Allan
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Library;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string'
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $data = Library::select('id', 'name')
            ->where('name', 'LIKE', '%' . $validator->validated()['query'] . '%')
            ->orderBy('id')
            ->get();

        return response()->json($data);
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric'
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $name = Library::select('name')
            ->where('id', '=', $validator->validated()['id'])
            ->get()
            ->first();

        return response()->json($name);
    }
}
