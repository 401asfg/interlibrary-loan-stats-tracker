<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ILLRequest;

class ILLRequestController extends Controller
{
    public function index() {
        return view("form")->with('actions', ILLRequest::ACTIONS)
                           ->with('requestorTypes', ILLRequest::REQUESTOR_TYPES)
                           ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
                           ->with('resources', ILLRequest::RESOURCES);
    }

    public function store(Request $request) {
        $fields = $request->all();
        ILLRequest::create($fields)->save();
    }
}
