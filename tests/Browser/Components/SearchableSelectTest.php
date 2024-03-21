<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\ILLRequest;

class Browser extends \Laravel\Dusk\Browser
{
    const SUBMISSION_PAGE_TITLE = 'Submission Successful!';

    public function fillOutForm()
    {
        return $this->click('@resource_book')
            ->click('@action_lend');
    }

    public function clickSearchBar()
    {
        return $this->click('@searchable_select_input');
    }

    public function typeInSearchbar($query)
    {
        return $this->clickSearchBar()
            ->type('@searchable_select_input', $query);
    }

    public function clickOffSearchBar()
    {
        return $this->click('@fulfilled');
    }

    public function clickDropdownResult($index)
    {
        return $this->assertDropdownHasResults()
            ->click('@searchable_select_result_' . $index);
    }

    public function clickDropdownNoResult()
    {
        return $this->assertDropdownHasNoResults()
            ->click('@searchable_select_no_results');
    }

    public function typeAndClickDropdownResult($query, $index)
    {
        return $this->typeInSearchbar($query)
            ->clickDropdownResult($index);
    }

    public function assertDropdownMissing()
    {
        return $this->assertMissing('@searchable_select_no_results')
            ->assertMissing('@searchable_select_result_0');
    }

    public function assertDropdownHasResults()
    {
        return $this->waitFor('@searchable_select_result_0', 10);
    }

    public function assertDropdownResult($index, $result)
    {
        return $this->assertDropdownHasResults()
            ->assertSeeIn('@searchable_select_result_' . $index, $result);
    }

    public function assertDropdownHasNoResults()
    {
        return $this->waitFor('@searchable_select_no_results', 10);
    }

    public function assertSearchbarHasValue($value)
    {
        return $this->assertValue('@searchable_select_input', $value);
    }

    public function assertSearchbarHasNoValue()
    {
        return $this->assertValue('@searchable_select_input', '');
    }

    public function assertNoSearchbarValueAndDropdownMissing()
    {
        return $this->assertSearchbarHasNoValue()
            ->assertDropdownMissing();
    }

    public function assertSearchbarHasValueAndDropdownMissing($value)
    {
        return $this->assertSearchbarHasValue($value)
            ->assertDropdownMissing();
    }

    public function assertSearchbarHasValueAndDropdownResults($value)
    {
        return $this->assertSearchbarHasValue($value)
            ->assertDropdownHasResults();
    }

    public function assertSearchbarHasValueAndNoDropdownResults($value)
    {
        return $this->assertSearchbarHasValue($value)
            ->assertDropdownHasNoResults();
    }

    public function assertNoSearchbarValueAndDropdownMissingWithSubmit($test)
    {
        return $this->assertNoSearchbarValueAndDropdownMissing()
            ->assertSubmitFails($test);
    }

    public function assertSearchbarHasValueAndDropdownMissingWithSubmit($value, $test, $libraryName, $libraryId)
    {
        return $this->assertSearchbarHasValueAndDropdownMissing($value)
            ->assertSubmitSuccessful($test, $libraryName, $libraryId);
    }

    public function assertSearchbarHasValueAndDropdownResultsWithSubmit($value, $test, $libraryName, $libraryId)
    {
        return $this->assertSearchbarHasValueAndDropdownResults($value)
            ->assertSubmitSuccessful($test, $libraryName, $libraryId);
    }

    public function assertSearchbarHasValueAndNoDropdownResultsWithSubmit($value, $test)
    {
        return $this->assertSearchbarHasValueAndNoDropdownResults($value)
            ->assertSubmitFails($test);
    }

    public function submit()
    {
        return $this->click('@submit');
    }

    public function assertSubmitFails($test)
    {
        $browser = $this->submit()
            ->assertDontSee($this::SUBMISSION_PAGE_TITLE);

        $illRequest = ILLRequest::find(1);
        $test->assertNull($illRequest);

        return $browser;
    }

    public function assertSubmitSuccessful($test, $libraryName, $libraryId)
    {
        $browser = $this->submit()
            ->waitForText($this::SUBMISSION_PAGE_TITLE)
            ->assertSeeIn('@library_name', $libraryName);

        $illRequest = ILLRequest::find(1);
        $test->assertEquals($libraryId, $illRequest->library_id);

        return $browser;
    }
}

class SearchableSelectTest extends DuskTestCase
{
    const FIRST_RESULT_INDEX = 0;
    const FOURTH_RESULT_INDEX = 3;

    const ONE_LETTER_QUERY = 'j';
    const ONE_LETTER_FIRST_RESULT = 'Brock University, James A. Gibson Library';
    const ONE_LETTER_FIRST_ID = 16;
    const ONE_LETTER_FOURTH_RESULT = 'Fort St. John Public Library';
    const ONE_LETTER_FOURTH_ID = 105;

    const MULTIPLE_LETTERS_QUERY = 'Montreal';
    const MULTIPLE_LETTERS_FIRST_RESULT = 'University of Montreal';
    const MULTIPLE_LETTERS_FIRST_ID = 3;
    const MULTIPLE_LETTERS_FOURTH_RESULT = 'Institut universitaire de geriatrie de Montreal, Biblioteque de geriatrie et gerontologie';
    const MULTIPLE_LETTERS_FOURTH_ID = 248;

    const UBC_QUERY = 'british';
    const UBC_FIRST_RESULT = 'University of British Columbia';
    const UBC_FIRST_ID = 58;

    const INVALID_QUERY = 'sdf';

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

    public function testClick()
    {
        $this->browse(function (Browser $browser) {
            $browser->assertNoSearchbarValueAndDropdownMissing()
                ->clickSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testClickOnClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->assertNoSearchbarValueAndDropdownMissing()
                ->clickSearchBar()
                ->assertNoSearchbarValueAndDropdownMissing()
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testClickOnTypeOneLetterClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::ONE_LETTER_QUERY)
                ->assertSearchbarHasValueAndDropdownResults($this::ONE_LETTER_QUERY)
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testClickOnTypeOneLetterSelect()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::ONE_LETTER_QUERY)
                ->clickDropdownResult($this::FIRST_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissingWithSubmit($this::ONE_LETTER_FIRST_RESULT, $this, $this::ONE_LETTER_FIRST_RESULT, $this::ONE_LETTER_FIRST_ID);
        });
    }

    public function testClickOnTypeOneLetterSelectClickOnSelectOther()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::ONE_LETTER_QUERY)
                ->clickDropdownResult($this::FIRST_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissing($this::ONE_LETTER_FIRST_RESULT)
                ->clickSearchBar()
                ->assertSearchbarHasValueAndDropdownResults($this::ONE_LETTER_FIRST_RESULT)
                ->clickDropdownResult($this::FOURTH_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissingWithSubmit($this::ONE_LETTER_FOURTH_RESULT, $this, $this::ONE_LETTER_FOURTH_RESULT, $this::ONE_LETTER_FOURTH_ID);
        });
    }

    public function testClickOnTypeMultipleLettersClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::MULTIPLE_LETTERS_QUERY)
                ->assertSearchbarHasValueAndDropdownResults($this::MULTIPLE_LETTERS_QUERY)
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testClickOnTypeMultipleLettersSelect()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::MULTIPLE_LETTERS_QUERY)
                ->clickDropdownResult($this::FIRST_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissingWithSubmit($this::MULTIPLE_LETTERS_FIRST_RESULT, $this, $this::MULTIPLE_LETTERS_FIRST_RESULT, $this::MULTIPLE_LETTERS_FIRST_ID);
        });
    }

    public function testClickOnTypeMultipleLettersSelectClickOnSelectOther()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::MULTIPLE_LETTERS_QUERY)
                ->clickDropdownResult($this::FIRST_RESULT_INDEX)
                ->assertSearchbarHasValue($this::MULTIPLE_LETTERS_FIRST_RESULT)
                ->clickSearchBar()
                ->clickDropdownResult($this::FOURTH_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissingWithSubmit($this::MULTIPLE_LETTERS_FOURTH_RESULT, $this, $this::MULTIPLE_LETTERS_FOURTH_RESULT, $this::MULTIPLE_LETTERS_FOURTH_ID);
        });
    }

    public function testClickOnTypeFullName()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::MULTIPLE_LETTERS_FIRST_RESULT)
                ->assertSearchbarHasValueAndDropdownResults($this::MULTIPLE_LETTERS_FIRST_RESULT)
                ->assertSubmitFails($this);
        });
    }

    public function testClickOnTypeInvalid()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::INVALID_QUERY)
                ->assertSearchbarHasValueAndNoDropdownResultsWithSubmit($this::INVALID_QUERY, $this);
        });
    }

    public function testClickOnTypeInvalidClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::INVALID_QUERY)
                ->assertSearchbarHasValueAndNoDropdownResults($this::INVALID_QUERY)
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testClickOnTypeInvalidSelect()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeInSearchbar($this::INVALID_QUERY)
                ->assertSearchbarHasValueAndNoDropdownResults($this::INVALID_QUERY)
                ->clickDropdownNoResult()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testSelectTypeNothing()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->clickSearchBar()
                ->assertSearchbarHasValue($this::UBC_FIRST_RESULT)
                ->assertDropdownResult($this::FIRST_RESULT_INDEX, $this::UBC_FIRST_RESULT)
                ->assertSubmitFails($this);
        });
    }

    public function testSelectTypeNothingClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->clickSearchBar()
                ->assertSearchbarHasValue($this::UBC_FIRST_RESULT)
                ->assertDropdownResult($this::FIRST_RESULT_INDEX, $this::UBC_FIRST_RESULT)
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testSelectTypeNothingSelect()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->clickSearchBar()
                ->assertSearchbarHasValue($this::UBC_FIRST_RESULT)
                ->assertDropdownResult($this::FIRST_RESULT_INDEX, $this::UBC_FIRST_RESULT)
                ->clickDropdownResult($this::FIRST_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissingWithSubmit($this::UBC_FIRST_RESULT, $this, $this::UBC_FIRST_RESULT, $this::UBC_FIRST_ID);
        });
    }

    public function testSelectTypeValid()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::MULTIPLE_LETTERS_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::UBC_QUERY)
                ->assertSearchbarHasValue($this::UBC_QUERY)
                ->assertDropdownResult($this::FIRST_RESULT_INDEX, $this::UBC_FIRST_RESULT)
                ->assertSubmitFails($this);
        });
    }

    public function testSelectTypeValidClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::ONE_LETTER_QUERY)
                ->assertSearchbarHasValueAndDropdownResults($this::ONE_LETTER_QUERY)
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testSelectTypeValidSelect()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::ONE_LETTER_QUERY)
                ->assertSearchbarHasValueAndDropdownResults($this::ONE_LETTER_QUERY)
                ->clickDropdownResult($this::FIRST_RESULT_INDEX)
                ->assertSearchbarHasValueAndDropdownMissingWithSubmit($this::ONE_LETTER_FIRST_RESULT, $this, $this::ONE_LETTER_FIRST_RESULT, $this::ONE_LETTER_FIRST_ID);
        });
    }

    public function testSelectTypeInvalid()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::INVALID_QUERY)
                ->assertSearchbarHasValueAndNoDropdownResultsWithSubmit($this::INVALID_QUERY, $this);
        });
    }

    public function testSelectTypeInvalidClickOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::INVALID_QUERY)
                ->assertSearchbarHasValueAndNoDropdownResults($this::INVALID_QUERY)
                ->clickOffSearchBar()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testSelectTypeInvalidSelect()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::INVALID_QUERY)
                ->assertSearchbarHasValueAndNoDropdownResults($this::INVALID_QUERY)
                ->clickDropdownNoResult()
                ->assertNoSearchbarValueAndDropdownMissingWithSubmit($this);
        });
    }

    public function testSelectTypeFullName()
    {
        $this->browse(function (Browser $browser) {
            $browser->typeAndClickDropdownResult($this::UBC_QUERY, $this::FIRST_RESULT_INDEX)
                ->typeInSearchbar($this::MULTIPLE_LETTERS_FIRST_RESULT)
                ->assertSearchbarHasValue($this::MULTIPLE_LETTERS_FIRST_RESULT)
                ->assertDropdownResult($this::FIRST_RESULT_INDEX, $this::MULTIPLE_LETTERS_FIRST_RESULT)
                ->assertSubmitFails($this);
        });
    }
}
