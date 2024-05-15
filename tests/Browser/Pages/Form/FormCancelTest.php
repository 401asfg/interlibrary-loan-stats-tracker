<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use App\Models\ILLRequest;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Carbon\Carbon;

class FormCancelTestBrowser extends Browser
{
    public function fillout()
    {
        $this->click('@resource_book')
            ->click('@action_borrow')
            ->type('@requestor_notes', 'notes')
            ->click('@searchable_select_input')
            ->type('@searchable_select_input', 'british')
            ->click('@searchable_select_result_0')
            ->click('@vcc_borrower_type_student');
    }
}

class FormCancelTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        return new FormCancelTestBrowser($driver);
    }

    public function testCancelFromIndex()
    {
        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->click('@submit')
                ->waitFor('@form_title')
                ->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelFromIndexFilledOut()
    {
        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->click('@submit')
                ->waitFor('@form_title')
                ->fillout();

            $browser->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelAfterSubmittingAnother()
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@submit')
                ->waitFor('@form_title')
                ->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelAfterSubmittingAnotherFillout()
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@submit')
                ->waitFor('@form_title')
                ->fillout();

            $browser->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelAfterClickingEdit()
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelAfterClickingEditFillout()
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@edit')
                ->waitFor('@form_title')
                ->fillout();

            $browser->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelAfterClickingDelete()
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@delete')
                ->waitFor('@form_title')
                ->click('@cancel')
                ->assertVisible('@index_title');
        });
    }

    public function testCancelAfterClickingDeleteFillout()
    {
        $illRequest = ILLRequest::create([
            'request_date' => Carbon::today()->toDateString(),
            'fulfilled' => 'false',
            'unfulfilled_reason' => 'reason',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 58,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'notes'
        ]);

        $illRequest->save();

        $this->browse(function (FormCancelTestBrowser $browser) {
            $browser->visit('ill-requests/1')
                ->click('@delete')
                ->waitFor('@form_title')
                ->fillout();

            $browser->click('@cancel')
                ->assertVisible('@index_title');
        });
    }
}
