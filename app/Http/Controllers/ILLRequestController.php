<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\ILLRequest;

class ILLRequestController extends Controller
{
    public function index() {
        return redirect('/create');
    }

    public function create() {
        return view('form')->with('actions', ILLRequest::ACTIONS)
                            ->with('vccBorrowerTypes', ILLRequest::VCC_BORROWER_TYPES)
                            ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
                            ->with('resources', ILLRequest::RESOURCES);
    }

    public function store(Request $request) {
        // FIXME: this request needs to have its fields validated
        $illRequest = ILLRequest::create($request->all());
        $illRequest->save();

        return redirect('/show/' . $illRequest->id);
    }

    public function show($id) {
        $illRequest = ILLRequest::findOrFail($id);

        $libraryId = $illRequest->library_id;
        $libraryName = null;

        if ($libraryId) {
            $request = Request::create('/libraries/show/' . $libraryId, 'GET');
            $response = Route::dispatch($request);
            $libraryName = $response->getData()->name;
        }

        return view('submission')->with('illRequest', $illRequest)
                                 ->with('libraryName', $libraryName);
    }

    public function destroy($id) {
        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->delete();
        return redirect('/create')->with('status', 'Last submission deleted!');
    }
}
