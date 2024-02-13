<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ILLRequest;

class ILLRequestController extends Controller
{
    public function index() {
        $vccBorrowerTypes = ILLRequest::VCC_BORROWER_TYPES;
        unset($vccBorrowerTypes['library']);

        return view('form')->with('actions', ILLRequest::ACTIONS)
                           ->with('vccBorrowerTypes', $vccBorrowerTypes)
                           ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
                           ->with('resources', ILLRequest::RESOURCES);
    }

    public function store(Request $request) {
        $request['library_id'] = null;
        $libraryName = null;

        if ($request['library_data'] !== null) {
            $libraryDataSplit = explode(',', $request['library_data'], 2);
            $request['library_id'] = $libraryDataSplit[0];
            $libraryName = $libraryDataSplit[1];
        }

        // FIXME: does this request need to have its fields validated?
        $illRequest = ILLRequest::create($request->all());
        $illRequest->save();

        return view('submission')->with('illRequest', $illRequest)
                                 ->with('libraryName', $libraryName);
    }

    public function destroy($id) {
        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->delete();
        return redirect('/')->with('status', 'Last submission deleted!');
    }
}
