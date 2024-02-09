<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ILLRequest;

class ILLRequestController extends Controller
{
    public function index() {
        return view('form')->with('actions', ILLRequest::ACTIONS)
                           ->with('requestorTypes', ILLRequest::REQUESTOR_TYPES)
                           ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
                           ->with('resources', ILLRequest::RESOURCES);
    }

    public function store(Request $request) {
        // FIXME: does this request need to have its fields validated?
        $illRequest = ILLRequest::create($request->all());
        $illRequest->save();
        return view('submission')->with('illRequest', $illRequest);
    }

    public function destroy($id) {
        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->delete();
        return redirect('/')->with('status', 'Last submission deleted!');
    }
}
