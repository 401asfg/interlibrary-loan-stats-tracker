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
const REQUESTOR_NOTES = 'notes';

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
            'requestor_notes' => REQUESTOR_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('ill-requests/1')
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
                ->assertValue('@requestor_notes', REQUESTOR_NOTES)
                ->click('@fulfilled')
                ->click('@resource_book')
                ->click('@submit')
                ->waitForText('Edit Successful!')
                ->assertSee('Request was Fulfilled')
                ->assertMissing('@unfulfilled_reason')
                ->assertSee(ILLRequest::RESOURCES['book'])
                ->assertSee(ILLRequest::ACTIONS['borrow'])
                ->assertSee(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertSee(REQUESTOR_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('true', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(ILLRequest::RESOURCES['book'], $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['borrow'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['student'], $illRequest->vcc_borrower_type);
        $this->assertEquals(REQUESTOR_NOTES, $illRequest->requestor_notes);
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
            'requestor_notes' => REQUESTOR_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('ill-requests/1')
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
                ->assertValue('@requestor_notes', REQUESTOR_NOTES)
                ->click('@resource_book')
                ->click('@action_ship-to-me')
                ->click('@submit')
                ->waitForText('Edit Successful!')
                ->assertSee('Request was not Fulfilled')
                ->assertVisible('@unfulfilled_reason')
                ->assertSee(ILLRequest::RESOURCES['book'])
                ->assertSee(ILLRequest::ACTIONS['ship-to-me'])
                ->assertMissing(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertSee(REQUESTOR_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('false', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(ILLRequest::RESOURCES['book'], $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['ship-to-me'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['student'], $illRequest->vcc_borrower_type);
        $this->assertEquals(REQUESTOR_NOTES, $illRequest->requestor_notes);
    }

    public function testEditOverwritesRequestorNotes(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => UNFULFILLED_REASON_DESCRIPTION,
            'resource' => RESOURCE_DESCRIPTION,
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => LIBRARY_ID,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => REQUESTOR_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('ill-requests/1')
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
                ->assertValue('@requestor_notes', REQUESTOR_NOTES)
                ->click('@resource_book')
                ->click('@action_lend')
                ->click('@submit')
                ->waitForText('Edit Successful!')
                ->assertSee('Request was not Fulfilled')
                ->assertVisible('@unfulfilled_reason')
                ->assertSee(ILLRequest::RESOURCES['book'])
                ->assertSee(ILLRequest::ACTIONS['lend'])
                ->assertSee(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['library'])
                ->assertMissing(REQUESTOR_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('false', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(ILLRequest::RESOURCES['book'], $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['lend'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['library'], $illRequest->vcc_borrower_type);
        $this->assertEquals(REQUESTOR_NOTES, $illRequest->requestor_notes);
    }

    public function testUnfulfilledReasonOtherDescriptionDoesntContainNonOther(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
            'resource' => RESOURCE_DESCRIPTION,
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => LIBRARY_ID,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => REQUESTOR_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->assertValue('@request_date', Carbon::today()->toDateString())
                ->assertVisible('@unfulfilled_reason_google-scholar')
                ->assertVisible('@unfulfilled_reason_other')
                ->assertMissing('@unfulfilled_reason_description')
                ->click('@unfulfilled_reason_other')
                ->assertValue('@unfulfilled_reason_description', '')
                ->type('@unfulfilled_reason_description', UNFULFILLED_REASON_DESCRIPTION)
                ->assertValue('@unfulfilled_reason_description', UNFULFILLED_REASON_DESCRIPTION)
                ->assertChecked('@resource_other')
                ->assertValue('@resource_description', RESOURCE_DESCRIPTION)
                ->assertChecked('@action_borrow')
                ->assertValue('@searchable_select_input', LIBRARY_NAME)
                ->assertChecked('@vcc_borrower_type_student')
                ->assertValue('@requestor_notes', REQUESTOR_NOTES)
                ->click('@submit')
                ->waitForText('Edit Successful!')
                ->assertSee('Request was not Fulfilled')
                ->assertSee(UNFULFILLED_REASON_DESCRIPTION)
                ->assertSee(RESOURCE_DESCRIPTION)
                ->assertSee(ILLRequest::ACTIONS['borrow'])
                ->assertSee(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertSee(REQUESTOR_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('false', $illRequest->fulfilled);
        $this->assertEquals(UNFULFILLED_REASON_DESCRIPTION, $illRequest->unfulfilled_reason);
        $this->assertEquals(RESOURCE_DESCRIPTION, $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['borrow'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['student'], $illRequest->vcc_borrower_type);
        $this->assertEquals(REQUESTOR_NOTES, $illRequest->requestor_notes);
    }

    public function testResourceOtherDescriptionDoesntContainNonOther(): void
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => LIBRARY_ID,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => REQUESTOR_NOTES
        ]);

        $illRequest->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->assertValue('@request_date', Carbon::today()->toDateString())
                ->assertVisible('@unfulfilled_reason_google-scholar')
                ->assertVisible('@unfulfilled_reason_other')
                ->assertMissing('@unfulfilled_reason_description')
                ->assertChecked('@resource_book')
                ->click('@resource_other')
                ->assertValue('@resource_description', '')
                ->type('@resource_description', RESOURCE_DESCRIPTION)
                ->assertValue('@resource_description', RESOURCE_DESCRIPTION)
                ->assertChecked('@action_borrow')
                ->assertValue('@searchable_select_input', LIBRARY_NAME)
                ->assertChecked('@vcc_borrower_type_student')
                ->assertValue('@requestor_notes', REQUESTOR_NOTES)
                ->click('@submit')
                ->waitForText('Edit Successful!')
                ->assertSee('Request was not Fulfilled')
                ->assertSee(ILLRequest::UNFULFILLED_REASONS['google-scholar'])
                ->assertSee(RESOURCE_DESCRIPTION)
                ->assertSee(ILLRequest::ACTIONS['borrow'])
                ->assertSee(LIBRARY_NAME)
                ->assertSee(ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertSee(REQUESTOR_NOTES);
        });

        $illRequest = ILLRequest::find(1);

        $this->assertEquals(Carbon::today()->toDateString(), $illRequest->request_date);
        $this->assertEquals('false', $illRequest->fulfilled);
        $this->assertEquals(ILLRequest::UNFULFILLED_REASONS['google-scholar'], $illRequest->unfulfilled_reason);
        $this->assertEquals(RESOURCE_DESCRIPTION, $illRequest->resource);
        $this->assertEquals(ILLRequest::ACTIONS['borrow'], $illRequest->action);
        $this->assertEquals(LIBRARY_ID, $illRequest->library_id);
        $this->assertEquals(ILLRequest::VCC_BORROWER_TYPES['student'], $illRequest->vcc_borrower_type);
        $this->assertEquals(REQUESTOR_NOTES, $illRequest->requestor_notes);
    }
}
