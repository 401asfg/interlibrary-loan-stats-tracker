<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use App\Models\ILLRequest;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DeleteRecordTest extends DuskTestCase
{
    public function testDeleteLastAddedRecord(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'vcc_borrower_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@delete');
        });

        $this->assertNull(ILLRequest::find(1));
    }
}
