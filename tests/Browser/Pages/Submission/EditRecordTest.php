<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use App\Models\ILLRequest;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

const LIBRARY_NAME = 'University of British Columbia';
const LIBRARY_ID = 58;
const UNFULFILLED_REASON_DESCRIPTION = 'reason';
const RESOURCE_DESCRIPTION = 'resource';
const VCC_BORROWER_NOTES = 'notes';

class EditRecordTest extends DuskTestCase
{
    // FIXME: make overwritten fields null on submit (not really necessary)

    public function testEditOverwritesUnfulfilledReason(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => UNFULFILLED_REASON_DESCRIPTION,
            'resource' => RESOURCE_DESCRIPTION,
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => LIBRARY_ID,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'vcc_borrower_notes' => VCC_BORROWER_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('/show/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->assertValue('@request_date', Carbon::today()->toDateString())
                ->assertVisible('@unfulfilled_reason_other')
                ->assertValue('@unfulfilled_reason_description', UNFULFILLED_REASON_DESCRIPTION)
                ->assertChecked('@resource_other')
                ->assertValue('@resource_description', RESOURCE_DESCRIPTION)
                ->assertChecked('@action_borrow')
                ->assertValue('@searchable_select_input', LIBRARY_NAME)
                ->assertChecked('@vcc_borrower_type_student')
                ->assertValue('@vcc_borrower_notes', VCC_BORROWER_NOTES)
                ->click('@fulfilled')
                ->click('@resource_book')
                ->click('@submit')
                ->waitFor('@submission_title')
                ->assertSee('Request was Fulfilled')
                ->assertMissing('@unfulfilled_reason')
                ->assertSee(ILLRequest::RESOURCES['book'])
                ->assertSee(ILLRequest::ACTIONS['borrow'])
                ->assertSee(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertSee(VCC_BORROWER_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('true', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(ILLRequest::RESOURCES['book'], $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['borrow'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['student'], $illRequest->vcc_borrower_type);
        $this->assertEquals(VCC_BORROWER_NOTES, $illRequest->vcc_borrower_notes);
    }

    public function testEditOverwritesLibrary(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => UNFULFILLED_REASON_DESCRIPTION,
            'resource' => RESOURCE_DESCRIPTION,
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => LIBRARY_ID,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'vcc_borrower_notes' => VCC_BORROWER_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('/show/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->assertValue('@request_date', Carbon::today()->toDateString())
                ->assertVisible('@unfulfilled_reason_other')
                ->assertValue('@unfulfilled_reason_description', UNFULFILLED_REASON_DESCRIPTION)
                ->assertChecked('@resource_other')
                ->assertValue('@resource_description', RESOURCE_DESCRIPTION)
                ->assertChecked('@action_borrow')
                ->assertValue('@searchable_select_input', LIBRARY_NAME)
                ->assertChecked('@vcc_borrower_type_student')
                ->assertValue('@vcc_borrower_notes', VCC_BORROWER_NOTES)
                ->click('@resource_book')
                ->click('@action_ship-to-me')
                ->click('@submit')
                ->waitFor('@submission_title')
                ->assertSee('Request was not Fulfilled')
                ->assertVisible('@unfulfilled_reason')
                ->assertSee(ILLRequest::RESOURCES['book'])
                ->assertSee(ILLRequest::ACTIONS['ship-to-me'])
                ->assertMissing(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertSee(VCC_BORROWER_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('false', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(ILLRequest::RESOURCES['book'], $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['ship-to-me'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['student'], $illRequest->vcc_borrower_type);
        $this->assertEquals(VCC_BORROWER_NOTES, $illRequest->vcc_borrower_notes);
    }

    public function testEditOverwritesVccBorrowerNotes(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => UNFULFILLED_REASON_DESCRIPTION,
            'resource' => RESOURCE_DESCRIPTION,
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => LIBRARY_ID,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'vcc_borrower_notes' => VCC_BORROWER_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('/show/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->assertValue('@request_date', Carbon::today()->toDateString())
                ->assertVisible('@unfulfilled_reason_other')
                ->assertValue('@unfulfilled_reason_description', UNFULFILLED_REASON_DESCRIPTION)
                ->assertChecked('@resource_other')
                ->assertValue('@resource_description', RESOURCE_DESCRIPTION)
                ->assertChecked('@action_borrow')
                ->assertValue('@searchable_select_input', LIBRARY_NAME)
                ->assertChecked('@vcc_borrower_type_student')
                ->assertValue('@vcc_borrower_notes', VCC_BORROWER_NOTES)
                ->click('@resource_book')
                ->click('@action_lend')
                ->click('@submit')
                ->waitFor('@submission_title')
                ->assertSee('Request was not Fulfilled')
                ->assertVisible('@unfulfilled_reason')
                ->assertSee(ILLRequest::RESOURCES['book'])
                ->assertSee(ILLRequest::ACTIONS['lend'])
                ->assertSee(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['library'])
                ->assertMissing(VCC_BORROWER_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('false', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(ILLRequest::RESOURCES['book'], $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['lend'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['library'], $illRequest->vcc_borrower_type);
        $this->assertEquals(VCC_BORROWER_NOTES, $illRequest->vcc_borrower_notes);
    }
}
