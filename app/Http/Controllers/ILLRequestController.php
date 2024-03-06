<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ILLRequest;

class ILLRequestController extends Controller
{
    public function index()
    {
        return redirect('/create');
    }

    public function create()
    {
        return ILLRequestController::getFormView();
    }

    public function store(Request $request)
    {
        $validator = ILLRequestController::makeILLRequestFieldsValidator($request->all());

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $illRequest = ILLRequest::create($validator->validated());
        $illRequest->save();
        return redirect('/show/' . $illRequest->id);
    }

    public function show(string $id)
    {
        $illRequest = ILLRequest::findOrFail($id);
        $libraryName = $illRequest->getLibraryName();

        return view('submission')->with('illRequest', $illRequest)
            ->with('libraryName', $libraryName);
    }

    public function destroy(string $id)
    {
        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->delete();
        return redirect('/create')->with('status', 'Last submission deleted!');
    }

    public function edit(string $id)
    {
        $illRequest = ILLRequest::findOrFail($id);
        $libraryName = $illRequest->getLibraryName();
        return ILLRequestController::getFormView($illRequest, $libraryName);
    }

    public function update(Request $request, string $id)
    {
        $validator = ILLRequestController::makeILLRequestFieldsValidator($request->all());

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $illRequest = ILLRequest::findOrFail($id);
        $illRequest->update($validator->validated());
        return redirect('/show/' . $id);
    }

    private static function getFormView(ILLRequest $illRequest = null, string $libraryName = null)
    {
        return view('form')->with('actions', ILLRequest::ACTIONS)
            ->with('vccBorrowerTypes', ILLRequest::VCC_BORROWER_TYPES)
            ->with('unfulfilledReasons', ILLRequest::UNFULFILLED_REASONS)
            ->with('resources', ILLRequest::RESOURCES)
            ->with('illRequest', $illRequest)
            ->with('libraryName', $libraryName);
    }

    private static function makeILLRequestFieldsValidator($fields)
    {
        return Validator::make($fields, [
            'request_date' => 'required|date',
            'fulfilled' => 'required|in:true,false',
            'unfulfilled_reason' => 'nullable|string',
            'resource' => 'required|string',
            'action' => 'required|in:' . implode(',', ILLRequest::ACTIONS),
            'library_id' => 'nullable|exists:libraries,id',
            'vcc_borrower_type' => 'required|in:' . implode(',', ILLRequest::VCC_BORROWER_TYPES),
            'vcc_borrower_notes' => 'nullable|string'
        ]);
    }
}
