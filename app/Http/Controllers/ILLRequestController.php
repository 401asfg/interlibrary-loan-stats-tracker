<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ILLRequest;
use Validator;

class ILLRequestController extends Controller
{
    public function index() {
        return view('form')->with('actions', ILLRequest::ACTIONS)
                           ->with('requestorTypes', ILLRequest::REQUESTOR_TYPES)
                           ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
                           ->with('resources', ILLRequest::RESOURCES);
    }

    public function store(Request $request) {
        $fields = $request->all();

        $validator = Validator::make($fields, [
            'requestDate' => 'required',
            'resource' => 'required',
            'action' => 'required',
            'requestorType' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('/')->withErrors($validator)
                                ->withInput();
        }

        $illRequest = ILLRequest::create($fields);
        $illRequest->save();

        return view('submission')->with('illRequest', $illRequest);
    }
}
