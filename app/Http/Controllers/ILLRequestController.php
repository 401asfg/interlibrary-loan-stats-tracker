<?php

/*
 * Author: Michael Allan
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ILLRequest;
use Illuminate\Support\Facades\DB;

class ILLRequestController extends Controller
{
    public function index(Request $request)
    {
        $records = [];

        if ($request->has('date')) {
            $records = ILLRequest::select(
                DB::raw('created_at AS "Created At"'),
                DB::raw('request_date AS "Request Date"'),
                DB::raw('fulfilled AS "Fulfilled"'),
                DB::raw('unfulfilled_reason AS "Unfulfilled Reason"'),
                DB::raw('resource AS "Resource"'),
                DB::raw('action AS "Action"'),
                DB::raw('vcc_borrower_type AS "VCC Borrower Type"'),
                DB::raw('vcc_borrower_notes AS "VCC Borrower Notes"'),
                DB::raw('libraries.name AS "Library Name"')
            )
                ->leftJoin('libraries', 'ill_requests.library_id', '=', 'libraries.id')
                ->where('created_at', 'LIKE', $request->input('date') . ' __:__:__')
                ->orderBy('created_at')
                ->get();
        }

        return response()->json($records);
    }

    public function create()
    {
        return ILLRequestController::getFormView();
    }

    public function records()
    {
        return view('records');
    }

    public function totals()
    {
        return view('totals');
    }

    public function store(Request $request)
    {
        $validator = ILLRequestController::makeILLRequestFieldsValidator($request->all());

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $illRequest = ILLRequest::create($validator->validated());
        $illRequest->save();
        return redirect('ill-requests/' . $illRequest->id);
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
        return redirect('ill-requests/create')->with('status', 'Last submission deleted!');
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
        return redirect('ill-requests/' . $id);
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
