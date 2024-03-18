<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use App\Models\ILLRequest;
use Tests\DuskTestCase;

class Browser extends \Laravel\Dusk\Browser
{
    public function fillOutForm()
    {
        return $this->click('@resource_book')
            ->click('@action_borrow')
            ->type('@searchable_select_input', 'british')
            ->waitFor('@searchable_select_result_0')
            ->click('@searchable_select_result_0')
            ->click('@vcc_borrower_type_student')
            ->type('@vcc_borrower_notes', 'test notes');
    }

    public function assertHasStatusOnHidding($clearingSelector, $clearedSelector, $assertStatus)
    {
        return $this->click($clearingSelector)
                    ->click('@submit')
            ->$assertStatus($clearedSelector);
    }

    public function assertHiddenRadioHasStatusOnRehidding($clearingSelector, $clearedSelector, $radioSlug, $assertStatus)
    {
        return $this->click($clearingSelector)
            ->click($clearedSelector . '_' . $radioSlug)
            ->assertHasStatusOnHidding($clearingSelector, $clearedSelector, $assertStatus);
    }

    public function assertOtherDescriptionHasStatusOnHidding($hiddingSelector, $clearedSelector, $assertStatus)
    {
        return $this->click($clearedSelector . '_other')
            ->type($clearedSelector . '_description', 'test value')
            ->assertHasStatusOnHidding($hiddingSelector, $clearedSelector, $assertStatus);
    }

    public function assertOtherDescriptionInHiddenFieldHasStatusOnHidding($revealingSelector, $hiddingSelector, $clearedSelector, $assertStatus)
    {
        return $this->click($revealingSelector)
            ->assertOtherDescriptionHasStatusOnHidding($hiddingSelector, $clearedSelector, $assertStatus);
    }

    public function assertOtherDescriptionHasStatusOnNonOtherSelected($clearedSelector, $nonOtherRadioName, $assertStatus)
    {
        return $this->assertOtherDescriptionHasStatusOnHidding($clearedSelector . '_' . $nonOtherRadioName, $clearedSelector, $assertStatus);
    }

    public function assertSelectorClearsOption($clearingSelector, $optionSelector)
    {
        return $this->click($clearingSelector)
            ->click($optionSelector)
            ->click('@submit')
            ->assertMissing('@submission_title');
    }

    public function waitForShowPage()
    {
        return $this->waitFor('@submission_title');
    }

    public function assertShowPageSelectorVisible($selector)
    {
        return $this->waitForShowPage()
            ->assertVisible($selector);
    }

    public function assertShowPageSelectorMissing($selector)
    {
        return $this->waitForShowPage()
            ->assertMissing($selector);
    }

    public function assertShowPageHasLibraryVCCBorrowerType($selector)
    {
        return $this->waitForShowPage()
            ->assertSee('Library');
    }
}

class FormDataClearingTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        $browser = new Browser($driver);
        return $browser->visit('/');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->browse(function (Browser $browser) {
            $browser->fillOutForm();
        });
    }

    public function testUnfulfilledReasonClearsOnFulfilled(): void
    {
        $this->assertHiddenRadioHasStatusOnRehidding('@fulfilled', 'unavailable', null, 'unfulfilled_reason', 'assertShowPageSelectorMissing');
    }

    public function testOtherUnfulfilledReasonClearsOnNonOther(): void
    {
        $this->assertOtherDescriptionInHiddenFieldHasStatusOnHidding('@fulfilled', '@unfulfilled_reason_unavailable', ILLRequest::UNFULFILLED_REASONS['unavailable'], 'unfulfilled_reason', 'assertShowPageSelectorVisible');
    }

    public function testOtherUnfulfilledReasonClearsOnFulfilled(): void
    {
        $this->assertOtherDescriptionInHiddenFieldHasStatusOnHidding('@fulfilled', '@fulfilled', null, 'unfulfilled_reason', 'assertShowPageSelectorMissing');
    }

    public function testOtherResourceClearsOnNonOther(): void
    {
        $resourceSlug = 'book';
        $this->assertOtherDescriptionHasStatusOnNonOtherSelected($resourceSlug, ILLRequest::RESOURCES[$resourceSlug], 'resource', 'assertShowPageSelectorVisible');
    }

    public function testShipToMeActionClearsOnBookChapterOrEaResource(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->assertSelectorClearsOption('@action_ship-to-me', '@resource_ea')
                ->click('@resource_book')
                ->assertSelectorClearsOption('@action_ship-to-me', '@resource_book-chapter');
        });
    }

    public function testLibraryIdClearsOnShipToMeAction(): void
    {
        $this->assertHasStatusOnHidding('@action_ship-to-me', '@library', null, 'library_id', 'assertShowPageSelectorMissing');
    }

    public function testVccBorrowerFieldsChangesToLibraryOnLendAction(): void
    {
        $this->assertHasStatusOnHidding('@action_lend', '@vcc_borrower_type', 'library', 'vcc_borrower_type', 'assertShowPageHasLibraryVCCBorrowerType');
    }

    private function assertNewDBEntryPropertyHas($expectedValue, $propertyName)
    {
        $this->assertEquals($expectedValue, ILLRequest::find(1)->{$propertyName});
    }

    private function assertHiddenRadioHasStatusOnRehidding($clearingSelector, $radioSlug, $expectedClearedValue, $clearedName, $assertStatus)
    {
        $this->browse(function (Browser $browser) use ($clearingSelector, $clearedName, $radioSlug, $assertStatus) {
            $browser->assertHiddenRadioHasStatusOnRehidding($clearingSelector, '@' . $clearedName, $radioSlug, $assertStatus);
        });

        $this->assertNewDBEntryPropertyHas($expectedClearedValue, $clearedName);
    }

    private function assertOtherDescriptionInHiddenFieldHasStatusOnHidding($revealingSelector, $hiddingSelector, $expectedClearedValue, $clearedName, $assertStatus)
    {
        $this->browse(function (Browser $browser) use ($revealingSelector, $hiddingSelector, $clearedName, $assertStatus) {
            $browser->assertOtherDescriptionInHiddenFieldHasStatusOnHidding($revealingSelector, $hiddingSelector, '@' . $clearedName, $assertStatus);
        });

        $this->assertNewDBEntryPropertyHas($expectedClearedValue, $clearedName);
    }

    private function assertOtherDescriptionHasStatusOnNonOtherSelected($nonOtherRadioName, $expectedClearedValue, $clearedName, $assertStatus)
    {
        $this->browse(function (Browser $browser) use ($clearedName, $nonOtherRadioName, $assertStatus) {
            $browser->assertOtherDescriptionHasStatusOnNonOtherSelected('@' . $clearedName, $nonOtherRadioName, $assertStatus);
        });

        $this->assertNewDBEntryPropertyHas($expectedClearedValue, $clearedName);
    }

    private function assertHasStatusOnHidding($clearingSelector, $clearedSelector, $dbExpectedValue, $dbPropertyName, $assertStatus)
    {
        $this->browse(function (Browser $browser) use ($clearingSelector, $clearedSelector, $assertStatus) {
            $browser->assertHasStatusOnHidding($clearingSelector, $clearedSelector, $assertStatus);
        });

        $this->assertNewDBEntryPropertyHas($dbExpectedValue, $dbPropertyName);
    }
}
