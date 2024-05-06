<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Tests\DuskTestCase;
use Carbon\Carbon;
use App\Models\ILLRequest;
use Laravel\Dusk\Browser;

class FormFieldStatesBrowser extends Browser
{
    public function assertDefaultPreFulfilled()
    {
        return $this->assertSee('Request Fulfilled')
            ->assertValue('@request_date', Carbon::today()->toDateString());
    }

    public function assertDefaultPostRequestFulfilledSections()
    {
        return $this->assertSee('Request Info')
            ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['book'])
            ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['ea'])
            ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['book-chapter'])
            ->assertRadioNotSelected('resource', 'Other')
            ->assertMissing('@resource_description')
            ->assertRadioNotSelected('action', ILLRequest::ACTIONS['borrow'])
            ->assertRadioNotSelected('action', ILLRequest::ACTIONS['lend'])
            ->assertRadioNotSelected('action', ILLRequest::ACTIONS['ship-to-me'])
            ->assertMissing('@action_description')
            ->assertMissing('Parties Involved');
    }

    public function assertDefaultPage()
    {
        return $this->assertVisible('@form_title')
            ->assertDefaultPreFulfilled()
            ->assertMissing('@unfulfilled_reason')
            ->assertDefaultPostRequestFulfilledSections();
    }

    public function assertNonOtherUnfulfilledReasonOptionsNotSelected()
    {
        return $this->assertRadioNotSelected('unfulfilled_reason', ILLRequest::UNFULFILLED_REASONS['unavailable'])
            ->assertRadioNotSelected('unfulfilled_reason', ILLRequest::UNFULFILLED_REASONS['google-scholar'])
            ->assertRadioNotSelected('unfulfilled_reason', ILLRequest::UNFULFILLED_REASONS['other-language'])
            ->assertRadioNotSelected('unfulfilled_reason', ILLRequest::UNFULFILLED_REASONS['not-needed-after-date'])
            ->assertRadioNotSelected('unfulfilled_reason', ILLRequest::UNFULFILLED_REASONS['fulfilled-from-collection']);
    }

    public function assertNonOtherResourceOptionsNotSelected()
    {
        return $this->assertRadioNotSelected('resource', ILLRequest::RESOURCES['book'])
            ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['ea'])
            ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['book-chapter']);
    }

    public function assertOtherDescriptionMissing($selectorName)
    {
        return $this->assertRadioNotSelected($selectorName, 'Other')
            ->assertMissing('@' . $selectorName . '_description');
    }

    public function assertOtherDescriptionVisible($selectorName)
    {
        return $this->assertRadioSelected($selectorName, 'Other')
            ->assertValue('@' . $selectorName . '_description', '');
    }

    public function assertDynamicSelectorWithOtherHasCorrectBehavior($selectorName, $nonOtherRadioId, $assertNonOtherOptionsNotSelected)
    {
        return $this->click('@' . $selectorName . '_other')
            ->$assertNonOtherOptionsNotSelected()
                ->assertOtherDescriptionVisible($selectorName)
                ->click('@' . $selectorName . '_' . $nonOtherRadioId)
                ->assertOtherDescriptionMissing($selectorName);
    }
}

class FormFieldStatesTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        return new FormFieldStatesBrowser($driver);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->visit('ill-requests/create');
        });
    }

    public function testInitialVisibility(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->assertDefaultPage();
        });
    }

    public function testDifferentDate(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->type('@request_date', '01-02-1970')
                ->assertValue('@request_date', '1970-01-02');
        });
    }

    public function testUnfulfilled(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->click('@fulfilled')
                ->assertDefaultPreFulfilled()
                ->assertNonOtherUnfulfilledReasonOptionsNotSelected()
                ->assertOtherDescriptionMissing('unfulfilled_reason')
                ->assertDefaultPostRequestFulfilledSections()
                ->click('@fulfilled')
                ->assertDefaultPage();
        });
    }

    public function testOtherUnfulfilledReason(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->click('@fulfilled')
                ->assertDynamicSelectorWithOtherHasCorrectBehavior('unfulfilled_reason', 'google-scholar', 'assertNonOtherUnfulfilledReasonOptionsNotSelected')
                ->click('@unfulfilled_reason_other')
                ->assertOtherDescriptionVisible('unfulfilled_reason')
                ->click('@fulfilled')
                ->assertMissing('@unfulfilled_reason');
        });
    }

    public function testOtherResource(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->assertDynamicSelectorWithOtherHasCorrectBehavior('resource', 'book', 'assertNonOtherResourceOptionsNotSelected');
        });
    }

    public function testBookChapterOrEaResource(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->click('@resource_ea')
                ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['book'])
                ->assertRadioSelected('resource', ILLRequest::RESOURCES['ea'])
                ->assertRadioNotSelected('resource', ILLRequest::RESOURCES['book-chapter'])
                ->assertRadioNotSelected('resource', 'Other')
                ->assertRadioNotSelected('action', ILLRequest::ACTIONS['borrow'])
                ->assertRadioNotSelected('action', ILLRequest::ACTIONS['lend'])
                ->assertMissing('@action_ship-to-me');
        });
    }

    public function testBorrowAction(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->click('@action_borrow')
                ->assertSee('Parties Involved')
                ->assertSee('Lending Library')
                ->assertMissing('Borrowing Library')
                ->assertValue('@searchable_select_input', '')
                ->assertSee('VCC Borrower')
                ->assertRadioNotSelected('vcc_borrower_type', ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertRadioNotSelected('vcc_borrower_type', ILLRequest::VCC_BORROWER_TYPES['employee']);
        });
    }

    public function testLendAction(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->click('@action_lend')
                ->assertSee('Parties Involved')
                ->assertMissing('Lending Library')
                ->assertSee('Borrowing Library')
                ->assertValue('@searchable_select_input', '')
                ->assertMissing('VCC Borrower')
                ->assertMissing('@vcc_borrower_type_student')
                ->assertMissing('@vcc_borrower_type_employee');
        });
    }

    public function testShipToMeAction(): void
    {
        $this->browse(function (FormFieldStatesBrowser $browser) {
            $browser->click('@action_ship-to-me')
                ->assertMissing('Parties Involved')
                ->assertMissing('Lending Library')
                ->assertMissing('Borrowing Library')
                ->assertMissing('@searchable_select_input')
                ->assertSee('VCC Borrower')
                ->assertRadioNotSelected('vcc_borrower_type', ILLRequest::VCC_BORROWER_TYPES['student'])
                ->assertRadioNotSelected('vcc_borrower_type', ILLRequest::VCC_BORROWER_TYPES['employee']);
        });
    }
}
