<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use App\Models\ILLRequest;
use Tests\DuskTestCase;

const UNFULFILLED_REASON_DESCRIPTION = 'unfulfilled reason description';
const RESOURCE_DESCRIPTION = 'resource description';
const VCC_BORROWER_NOTES = 'borrower notes';
const LIBRARY_NAME = 'University of British Columbia';
const LIBRARY_ID = 58;

class Browser extends \Laravel\Dusk\Browser
{
    public function fillOutForm()
    {
        return $this->click('@resource_book')
            ->click('@action_borrow')
            ->type('@searchable_select_input', 'british')
            ->waitFor('@searchable_select_result_0', 10)
            ->click('@searchable_select_result_0')
            ->click('@vcc_borrower_type_student');
    }

    public function submit()
    {
        return $this->click('@submit')
            ->waitFor('@submission_title');
    }

    public function clickUnfulfilledReason($reason)
    {
        return $this->click('@fulfilled')
            ->click('@unfulfilled_reason_' . $reason);
    }

    public function clickUnfulfilledReasonUnavailable()
    {
        return $this->clickUnfulfilledReason('unavailable');
    }

    public function typeUnfulfilledReasonOther()
    {
        return $this->clickUnfulfilledReason('other')
            ->type('@unfulfilled_reason_description', UNFULFILLED_REASON_DESCRIPTION);
    }

    public function typeVCCBorrowerNotes()
    {
        return $this->type('@vcc_borrower_notes', VCC_BORROWER_NOTES);
    }

    public function selectOtherResource()
    {
        return $this->click('@resource_other')
            ->type('@resource_description', RESOURCE_DESCRIPTION);
    }
    public function selectLendAction()
    {
        return $this->click('@action_lend');
    }

    public function selectShipToMeAction()
    {
        return $this->click('@action_ship-to-me');
    }

    public function assertResourcActionBorrowerType($resource, $action, $borrowerType)
    {
        return $this->assertSee($resource)
            ->assertSee($action)
            ->assertSee($borrowerType);
    }

    public function assertStatus($fulfilled, $unfulfilledReason, $resource, $action, $vccBorrowerType, $vccBorrowerNotes, $libraryName)
    {
        $browser = $fulfilled ? $this->assertVisible('@fulfilled') : $this->assertVisible('@unfulfilled');
        $browser = $unfulfilledReason ? $browser->assertVisible('@unfulfilled_reason') : $browser->assertMissing('@unfulfilled_reason');
        $browser = $browser->assertResourcActionBorrowerType($resource, $action, $vccBorrowerType);
        $browser = $libraryName ? $browser->assertSee($libraryName) : $browser->assertDontSee($libraryName);
        $browser = $vccBorrowerNotes ? $browser->assertVisible('@vcc_borrower_notes') : $browser->assertMissing('@vcc_borrower_notes');
        return $browser;
    }
}

class FormDataSubmissionTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        $browser = new Browser($driver);
        return $browser->visit('ill-requests/create');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->browse(function (Browser $browser) {
            $browser->fillOutForm();
        });
    }

    public function testFulfilledBorrowBookNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->submit(),
                true,
                null,
                'book',
                'borrow',
                'student',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testFulfilledBorrowBookWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeVCCBorrowerNotes()
                    ->submit(),
                true,
                null,
                'book',
                'borrow',
                'student',
                VCC_BORROWER_NOTES,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testFulfilledBorrowOtherNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectOtherResource()
                    ->submit(),
                true,
                null,
                RESOURCE_DESCRIPTION,
                'borrow',
                'student',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testFulfilledBorrowOtherWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectOtherResource()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                true,
                null,
                RESOURCE_DESCRIPTION,
                'borrow',
                'student',
                VCC_BORROWER_NOTES,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testFulfilledLendBook()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectLendAction()
                    ->submit(),
                true,
                null,
                'book',
                'lend',
                'library',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testFulfilledLendOther()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectLendAction()
                    ->selectOtherResource()
                    ->submit(),
                true,
                null,
                RESOURCE_DESCRIPTION,
                'lend',
                'library',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testFulfilledShipToMeBookNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectShipToMeAction()
                    ->submit(),
                true,
                null,
                'book',
                'ship-to-me',
                'student',
                null,
                null,
                null
            );
        });
    }

    public function testFulfilledShipToMeBookWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectShipToMeAction()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                true,
                null,
                'book',
                'ship-to-me',
                'student',
                VCC_BORROWER_NOTES,
                null,
                null
            );
        });
    }

    public function testFulfilledShipToMeOtherNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectShipToMeAction()
                    ->selectOtherResource()
                    ->submit(),
                true,
                null,
                RESOURCE_DESCRIPTION,
                'ship-to-me',
                'student',
                null,
                null,
                null
            );
        });
    }

    public function testFulfilledShipToMeOtherWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->selectShipToMeAction()
                    ->selectOtherResource()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                true,
                null,
                RESOURCE_DESCRIPTION,
                'ship-to-me',
                'student',
                VCC_BORROWER_NOTES,
                null,
                null
            );
        });
    }

    public function testUnfulfilledUnavailableBorrowBookNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->submit(),
                false,
                'unavailable',
                'book',
                'borrow',
                'student',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledUnavailableBorrowBookWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                'unavailable',
                'book',
                'borrow',
                'student',
                VCC_BORROWER_NOTES,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledUnavailableBorrowOtherNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectOtherResource()
                    ->submit(),
                false,
                'unavailable',
                RESOURCE_DESCRIPTION,
                'borrow',
                'student',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledUnavailableBorrowOtherWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectOtherResource()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                'unavailable',
                RESOURCE_DESCRIPTION,
                'borrow',
                'student',
                VCC_BORROWER_NOTES,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledUnavailableLendBook()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectLendAction()
                    ->submit(),
                false,
                'unavailable',
                'book',
                'lend',
                'library',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledUnavailableLendOther()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectLendAction()
                    ->selectOtherResource()
                    ->submit(),
                false,
                'unavailable',
                RESOURCE_DESCRIPTION,
                'lend',
                'library',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledUnavailableShipToMeBookNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectShipToMeAction()
                    ->submit(),
                false,
                'unavailable',
                'book',
                'ship-to-me',
                'student',
                null,
                null,
                null
            );
        });
    }

    public function testUnfulfilledUnavailableShipToMeBookWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectShipToMeAction()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                'unavailable',
                'book',
                'ship-to-me',
                'student',
                VCC_BORROWER_NOTES,
                null,
                null
            );
        });
    }

    public function testUnfulfilledUnavailableShipToMeOtherNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectShipToMeAction()
                    ->selectOtherResource()
                    ->submit(),
                false,
                'unavailable',
                RESOURCE_DESCRIPTION,
                'ship-to-me',
                'student',
                null,
                null,
                null
            );
        });
    }

    public function testUnfulfilledUnavailableShipToMeOtherWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->clickUnfulfilledReasonUnavailable()
                    ->selectShipToMeAction()
                    ->selectOtherResource()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                'unavailable',
                RESOURCE_DESCRIPTION,
                'ship-to-me',
                'student',
                VCC_BORROWER_NOTES,
                null,
                null
            );
        });
    }

    public function testUnfulfilledOtherBorrowBookNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                'book',
                'borrow',
                'student',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledOtherBorrowBookWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                'book',
                'borrow',
                'student',
                VCC_BORROWER_NOTES,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledOtherBorrowOtherNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectOtherResource()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                RESOURCE_DESCRIPTION,
                'borrow',
                'student',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledOtherBorrowOtherWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectOtherResource()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                RESOURCE_DESCRIPTION,
                'borrow',
                'student',
                VCC_BORROWER_NOTES,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledOtherLendBook()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectLendAction()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                'book',
                'lend',
                'library',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledOtherLendOther()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectLendAction()
                    ->selectOtherResource()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                RESOURCE_DESCRIPTION,
                'lend',
                'library',
                null,
                LIBRARY_ID,
                LIBRARY_NAME
            );
        });
    }

    public function testUnfulfilledOtherShipToMeBookNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectShipToMeAction()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                'book',
                'ship-to-me',
                'student',
                null,
                null,
                null
            );
        });
    }

    public function testUnfulfilledOtherShipToMeBookWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectShipToMeAction()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                'book',
                'ship-to-me',
                'student',
                VCC_BORROWER_NOTES,
                null,
                null
            );
        });
    }

    public function testUnfulfilledOtherShipToMeOtherNoNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectShipToMeAction()
                    ->selectOtherResource()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                RESOURCE_DESCRIPTION,
                'ship-to-me',
                'student',
                null,
                null,
                null
            );
        });
    }

    public function testUnfulfilledOtherShipToMeOtherWithNotes()
    {
        $this->browse(function (Browser $browser) {
            $this->assertStatus(
                $browser->typeUnfulfilledReasonOther()
                    ->selectShipToMeAction()
                    ->selectOtherResource()
                    ->typeVCCBorrowerNotes()
                    ->submit(),
                false,
                UNFULFILLED_REASON_DESCRIPTION,
                RESOURCE_DESCRIPTION,
                'ship-to-me',
                'student',
                VCC_BORROWER_NOTES,
                null,
                null
            );
        });
    }

    private function assertDatabaseRow($fulfilled, $unfulfilledReason, $resource, $action, $vccBorrowerType, $vccBorrowerNotes, $libraryId)
    {
        $illRequest = ILLRequest::find(1);

        $this->assertEquals($fulfilled ? 'true' : 'false', $illRequest->fulfilled);
        $this->assertEquals($unfulfilledReason, $illRequest->unfulfilled_reason);
        $this->assertEquals($resource, $illRequest->resource);
        $this->assertEquals($action, $illRequest->action);
        $this->assertEquals($libraryId, $illRequest->library_id);
        $this->assertEquals($vccBorrowerType, $illRequest->vcc_borrower_type);
        $this->assertEquals($vccBorrowerNotes, $illRequest->vcc_borrower_notes);
    }

    private function assertStatus($browser, $fulfilled, $unfulfilledReasonSlug, $resourceSlug, $actionSlug, $vccBorrowerTypeSlug, $vccBorrowerNotes, $libraryId, $libraryName)
    {
        $unfulfilledReason = array_key_exists($unfulfilledReasonSlug, ILLRequest::UNFULFILLED_REASONS) ? ILLRequest::UNFULFILLED_REASONS[$unfulfilledReasonSlug] : $unfulfilledReasonSlug;
        $resource = array_key_exists($resourceSlug, ILLRequest::RESOURCES) ? ILLRequest::RESOURCES[$resourceSlug] : $resourceSlug;
        $action = ILLRequest::ACTIONS[$actionSlug];
        $vccBorrowerType = ILLRequest::VCC_BORROWER_TYPES[$vccBorrowerTypeSlug];

        $browser->assertStatus($fulfilled, $unfulfilledReason, $resource, $action, $vccBorrowerType, $vccBorrowerNotes, $libraryName);
        $this->assertDatabaseRow($fulfilled, $unfulfilledReason, $resource, $action, $vccBorrowerType, $vccBorrowerNotes, $libraryId);
    }
}
