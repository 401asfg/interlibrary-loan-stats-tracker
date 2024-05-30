<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use App\Models\ILLRequest;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SubmitAnotherBrowser extends Browser
{
    public function fillOutForm($resourceSlug)
    {
        return $this->click('@resource_' . $resourceSlug)
            ->click('@action_borrow')
            ->type('@searchable_select_input', 'british')
            ->waitFor('@searchable_select_result_0', 10)
            ->click('@searchable_select_result_0')
            ->click('@vcc_borrower_type_student');
    }

    public function submit($titleSelector)
    {
        return $this->click('@submit')
            ->waitFor($titleSelector);
    }

    public function edit()
    {
        return $this->click('@edit')
            ->waitFor('@form_title');
    }

    public function delete()
    {
        return $this->click('@delete')
            ->waitFor('@form_title');
    }

    public function submitFilledOutForm($resourceSlug)
    {
        return $this->fillOutForm($resourceSlug)
            ->submit('@submission_title');
    }

    public function assertResourcActionBorrowerType($resource, $action, $borrowerType)
    {
        return $this->assertSee($resource)
            ->assertSee($action)
            ->assertSee($borrowerType);
    }

    public function assertSubmissionStatus($fulfilled, $unfulfilledReason, $resource, $action, $vccBorrowerType, $requestorNotes, $libraryName)
    {
        $browser = $fulfilled ? $this->assertVisible('@fulfilled') : $this->assertVisible('@unfulfilled');
        $browser = $unfulfilledReason ? $browser->assertVisible('@unfulfilled_reason') : $browser->assertMissing('@unfulfilled_reason');
        $browser = $browser->assertResourcActionBorrowerType($resource, $action, $vccBorrowerType);
        $browser = $libraryName ? $browser->assertSee($libraryName) : $browser->assertDontSee($libraryName);
        $browser = $requestorNotes ? $browser->assertVisible('@requestor_notes') : $browser->assertMissing('@requestor_notes');
        return $browser;
    }

    public function assertSubmissionResourceStatus($resource)
    {
        return $this->assertSubmissionStatus(true, null, $resource, 'Borrow', 'Student', null, 'University of British Columbia');
    }
}

class SubmitAnotherTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        return new SubmitAnotherBrowser($driver);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->visit('ill-requests/create')
                ->waitFor('@fulfilled');
        });
    }
    public function testSubmitAnotherAfterSubmit(): void
    {
        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submitFilledOutForm('book')
                ->assertSee('Submission Successful!')
                ->assertSubmissionResourceStatus(ILLRequest::RESOURCES['book']);
        });

        $this->assertDatabaseResourceStatus(1, 1, ILLRequest::RESOURCES['book']);

        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submit('@form_title')
                ->submitFilledOutForm('ea')
                ->assertSee('Submission Successful!')
                ->assertSubmissionResourceStatus(ILLRequest::RESOURCES['ea']);
        });

        $this->assertDatabaseResourceStatus(2, 2, ILLRequest::RESOURCES['ea']);
    }

    public function testSubmitAnotherAfterEdit(): void
    {
        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submitFilledOutForm('book')
                ->edit()
                ->click('@resource_ea')
                ->submit('@edit')
                ->assertDontSee('Submission Successful!')
                ->assertSubmissionResourceStatus(ILLRequest::RESOURCES['ea']);
        });

        $this->assertDatabaseResourceStatus(1, 1, ILLRequest::RESOURCES['ea']);

        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submit('@form_title')
                ->submitFilledOutForm('book-chapter')
                ->assertSee('Submission Successful!')
                ->assertSubmissionResourceStatus(ILLRequest::RESOURCES['book-chapter']);
        });

        $this->assertDatabaseResourceStatus(2, 2, ILLRequest::RESOURCES['book-chapter']);
    }

    public function testSubmitAnotherAfterDelete(): void
    {
        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submitFilledOutForm('book')
                ->delete();
        });

        $this->assertEquals(0, ILLRequest::count());

        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submitFilledOutForm('ea')
                ->assertSee('Submission Successful!')
                ->assertSubmissionResourceStatus(ILLRequest::RESOURCES['ea']);
        });

        $this->assertDatabaseResourceStatus(1, 2, ILLRequest::RESOURCES['ea']);

        $this->browse(function (SubmitAnotherBrowser $browser) {
            $browser->submit('@form_title')
                ->submitFilledOutForm('book-chapter')
                ->assertSee('Submission Successful!')
                ->assertSubmissionResourceStatus(ILLRequest::RESOURCES['book-chapter']);
        });

        $this->assertDatabaseResourceStatus(2, 3, ILLRequest::RESOURCES['book-chapter']);
    }

    private function assertDatabaseStatus($count, $id, $fulfilled, $unfulfilledReason, $resource, $action, $vccBorrowerType, $requestorNotes, $libraryId)
    {
        $this->assertEquals($count, ILLRequest::count());
        $illRequest = ILLRequest::find($id);

        $this->assertEquals($fulfilled ? 'true' : 'false', $illRequest->fulfilled);
        $this->assertEquals($unfulfilledReason, $illRequest->unfulfilled_reason);
        $this->assertEquals($resource, $illRequest->resource);
        $this->assertEquals($action, $illRequest->action);
        $this->assertEquals($libraryId, $illRequest->library_id);
        $this->assertEquals($vccBorrowerType, $illRequest->vcc_borrower_type);
        $this->assertEquals($requestorNotes, $illRequest->requestor_notes);
    }

    private function assertDatabaseResourceStatus($count, $id, $resource)
    {
        return $this->assertDatabaseStatus($count, $id, 'true', null, $resource, 'Borrow', 'Student', null, 58);
    }
}
