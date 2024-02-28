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
        return ILLRequestController::getFormView();
    }

    public function store(Request $request) {
        // FIXME: this request needs to have its fields validated
        $illRequest = ILLRequest::create($request->all());
        $illRequest->save();

        return redirect('/show/' . $illRequest->id);
    }

    public function show(string $id) {
        $illRequest = ILLRequest::findOrFail($id);

        $libraryId = $illRequest->library_id;
        $libraryName = ILLRequestController::getLibraryName($libraryId);

        return view('submission')->with('illRequest', $illRequest)
                                 ->with('libraryName', $libraryName);
    }

    public function destroy(string $id) {
        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->delete();
        return redirect('/create')->with('status', 'Last submission deleted!');
    }

    public function edit(string $id) {
        $illRequest = ILLRequest::findOrFail($id);
        $libraryId = $illRequest->library_id;
        $libraryName = IllRequestController::getLibraryName($libraryId);
        return ILLRequestController::getFormView($illRequest, $libraryName);
    }

    public function update(Request $request, string $id) {
        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->update($request->all());
        return redirect('/show/' . $id);
    }

    private function getFormView(ILLRequest $illRequest = null, string $libraryName = null) {
        return view('form')->with('actions', ILLRequest::ACTIONS)
                           ->with('vccBorrowerTypes', ILLRequest::VCC_BORROWER_TYPES)
                           ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
                           ->with('resources', ILLRequest::RESOURCES)
                           ->with('illRequest', $illRequest)
                           ->with('libraryName', $libraryName);
    }

    private function getLibraryName($libraryId) {
        if (!$libraryId) return null;

        $request = Request::create('/libraries/' . $libraryId, 'GET');
        $response = Route::dispatch($request);
        return $response->getData()->name;
    }
}
