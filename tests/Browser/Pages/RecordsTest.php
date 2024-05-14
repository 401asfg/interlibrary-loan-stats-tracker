<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Models\ILLRequest;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class RecordsBrowser extends Browser
{
    public function formatDateForAssertion($expectedDate)
    {
        return $expectedDate == "" ? "" : $expectedDate->format('Y-m-d');
    }

    public function assertMultipleDatesOffNoRecords($expectedFromDate)
    {
        $fromDate = $this::formatDateForAssertion($expectedFromDate);

        return $this->assertNotChecked('@multiple_checkbox')
            ->assertSeeIn('@from_date_header', 'Date')
            ->assertDontSeeIn('@from_date_header', 'From')
            ->assertValue('@from_date', $fromDate)
            ->assertMissing('@to_date_header')
            ->assertMissing('@to_date')
            ->assertMissingTable();
    }

    public function assertMultipleDatesOffHasRecords($expectedFromDate, $expectedRecordsFoundNum)
    {
        $fromDate = $this::formatDateForAssertion($expectedFromDate);

        return $this->assertNotChecked('@multiple_checkbox')
            ->assertSeeIn('@from_date_header', 'Date')
            ->assertDontSeeIn('@from_date_header', 'From')
            ->assertValue('@from_date', $fromDate)
            ->assertMissing('@to_date_header')
            ->assertMissing('@to_date')
            ->assertTable($expectedRecordsFoundNum);
    }

    public function assertMultipleDatesOnNoRecords($expectedFromDate, $expectedToDate)
    {
        $fromDate = $this::formatDateForAssertion($expectedFromDate);
        $toDate = $this::formatDateForAssertion($expectedToDate);

        return $this->assertChecked('@multiple_checkbox')
            ->assertSeeIn('@from_date_header', 'From')
            ->assertDontSeeIn('@from_date_header', 'Date')
            ->assertValue('@from_date', $fromDate)
            ->assertSeeIn('@to_date_header', 'To')
            ->assertValue('@to_date', $toDate)
            ->assertMissingTable();
    }

    public function assertMultipleDatesOnHasRecords($expectedFromDate, $expectedToDate, $expectedRecordsFoundNum)
    {
        $fromDate = $this::formatDateForAssertion($expectedFromDate);
        $toDate = $this::formatDateForAssertion($expectedToDate);

        return $this->assertChecked('@multiple_checkbox')
            ->assertSeeIn('@from_date_header', 'From')
            ->assertDontSeeIn('@from_date_header', 'Date')
            ->assertValue('@from_date', $fromDate)
            ->assertSeeIn('@to_date_header', 'To')
            ->assertValue('@to_date', $toDate)
            ->assertTable($expectedRecordsFoundNum);
    }

    public function backAndReturn()
    {
        return $this->click('@back')
            ->waitFor('@index_title')
            ->click('@view_records')
            ->waitFor('@records_title');
    }

    public function assertTable($expectedRecordsFoundNum)
    {
        $createdAtSelectorStart = '@records_table_entry_Created_At_';

        $browser = $this->waitForText('Records Found: ' . $expectedRecordsFoundNum)
            ->assertVisible('@records_table');

        for ($index = 0; $index < $expectedRecordsFoundNum; $index++) {
            $browser = $browser->assertVisible($createdAtSelectorStart . $index);
        }

        return $browser;
    }

    public function assertMissingTable()
    {
        return $this->waitForText('Records Found: 0')
            ->assertMissing('@records_table');
    }

    public function typeFromDate($date)
    {
        if ($date == "")
            return $this->clear('@from_date');

        $date = $date->format('m-d-Y');
        return $this->type('@from_date', $date);
    }

    public function typeToDate($date)
    {
        if ($date == "")
            return $this->clear('@to_date');

        $date = $date->format('m-d-Y');
        return $this->type('@to_date', $date);
    }

    public function clickMultDates()
    {
        return $this->click('@multiple_checkbox');
    }

    private function assertTableValue($selector, $value)
    {
        if ($value == null)
            return $this->assertSeeNothingIn($selector);

        return $this->assertSeeIn($selector, $value);
    }

    private function assertTableRow(int $index, array $expectedRow)
    {
        $browser = $this;

        foreach ($expectedRow as $key => $value) {
            $key = str_replace(' ', '_', $key);
            $selector = '@records_table_entry_' . $key . '_' . $index;

            $browser = $browser->waitFor($selector)
                ->assertTableValue($selector, $value);
        }

        return $browser;
    }

    public function assertSearch($fromDate, $toDate, array $expectedResponse)
    {
        $browser = $this->clickMultDates()
            ->typeFromDate($fromDate)
            ->typeToDate($toDate);

        for ($index = 0; $index < count($expectedResponse); $index++) {
            $row = $expectedResponse[$index];
            $browser = $browser->assertTableRow($index, $row);
        }

        return $browser;
    }
}

class RecordsTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        $browser = new RecordsBrowser($driver);
        return $browser;
    }

    protected function setUp(): void
    {
        parent::setUp();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => true,
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::today()
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => false,
            'unfulfilled_reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::today()
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => true,
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::today()
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => true,
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::yesterday()
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => true,
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => false,
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::yesterday()->subDays(5)
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => false,
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::yesterday()->subDays(6)
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => false,
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->addDays(9)
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => false,
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->addDays(9)
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => false,
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 4,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->addDays(10)
        ])->save();

        $this->browse(function (RecordsBrowser $browser) {
            $browser->visit('ill-requests/records')
                ->waitFor('@records_title');
        });
    }

    public function testInitState(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testBackAndReturn(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->backAndReturn()
                ->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testFilloutEverythingThenBackAndReturn(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this->today())
                ->typeToDate($this->today())
                ->assertTable(3)
                ->backAndReturn()
                ->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testMultOffFromClearThenFromRecordlessDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOffNoRecords($this::tooOldDate());
        });
    }

    public function testMultOffFromClearThenFromRecordsDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::today())
                ->assertMultipleDatesOffHasRecords($this::today(), 3);
        });
    }

    public function testMultOffFromClearThenMultOn(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->assertMultipleDatesOnNoRecords('', '');
        });
    }

    public function testMultOffFromRecordlessDateThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::tooOldDate())
                ->typeFromDate('')
                ->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testMultOffFromRecordlessDateThenFromRecordsDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::tooOldDate())
                ->typeFromDate($this::today())
                ->assertMultipleDatesOffHasRecords($this::today(), 3);
        });
    }

    public function testMultOffFromRecordlessDateThenMultOn(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::tooOldDate())
                ->clickMultDates()
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), '');
        });
    }

    public function testMultOffFromRecordsDateThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::today())
                ->typeFromDate('')
                ->assertMultipleDatesOffHasRecords('', 3);
        });
    }

    public function testMultOffFromRecordsDateThenFromRecordlessDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::today())
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOffNoRecords($this::tooOldDate());
        });
    }

    public function testMultOffFromRecordsDateThenMultOn(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->typeFromDate($this::today())
                ->clickMultDates()
                ->assertMultipleDatesOnHasRecords($this::today(), '', 3);
        });
    }

    public function testMultOnFromClearToClearThenFromRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), '');
        });
    }

    public function testMultOnFromClearToClearThenFromRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->assertMultipleDatesOnNoRecords($this::today(), '');
        });
    }

    public function testMultOnFromClearToClearThenToRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords('', $this::tooOldDate());
        });
    }

    public function testMultOnFromClearToClearThenToRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::today())
                ->assertMultipleDatesOnNoRecords('', $this::today());
        });
    }

    public function testMultOnFromClearToClearThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->clickMultDates()
                ->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testMultOnFromRecordlessToClearThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeFromDate('')
                ->assertMultipleDatesOnNoRecords('', '');
        });
    }

    public function testMultOnFromRecordlessToClearThenFromRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeFromDate($this::today())
                ->assertMultipleDatesOnNoRecords($this::today(), '');
        });
    }

    public function testMultOnFromRecordlessToClearThenToRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordlessToClearThenToRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->assertMultipleDatesOnHasRecords($this::tooOldDate(), $this::today(), 6);
        });
    }

    public function testMultOnFromRecordlessToClearThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->clickMultDates()
                ->assertMultipleDatesOffNoRecords($this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToClearThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeFromDate('')
                ->assertMultipleDatesOnNoRecords('', '');
        });
    }

    public function testMultOnFromRecordsToClearThenFromRecordlesss(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), '');
        });
    }

    public function testMultOnFromRecordsToClearThenToRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::today(), $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToClearThenToRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::today())
                ->assertMultipleDatesOnHasRecords($this::today(), $this::today(), 3);
        });
    }

    public function testMultOnFromRecordsToClearThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->clickMultDates()
                ->assertMultipleDatesOffHasRecords($this::today(), 3);
        });
    }

    public function testMultOnFromClearToRecordsThenFromRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::today())
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOnHasRecords($this::tooOldDate(), $this::today(), 6);
        });
    }

    public function testMultOnFromClearToRecordsThenFromRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::today())
                ->typeFromDate($this::today())
                ->assertMultipleDatesOnHasRecords($this::today(), $this::today(), 3);
        });
    }

    public function testMultOnFromClearToRecordsThenToClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::today())
                ->typeToDate('')
                ->assertMultipleDatesOnNoRecords('', '');
        });
    }

    public function testMultOnFromClearToRecordsThenToRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords('', $this::tooOldDate());
        });
    }

    public function testMultOnFromClearToRecordsThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::today())
                ->clickMultDates()
                ->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testMultOnFromRecordlessToRecordsThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->typeFromDate('')
                ->assertMultipleDatesOnHasRecords('', $this::today(), 6);
        });
    }

    public function testMultOnFromRecordlessToRecordsThenFromRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->typeFromDate($this::today())
                ->assertMultipleDatesOnHasRecords($this::today(), $this::today(), 3);
        });
    }

    public function testMultOnFromRecordlessToRecordsThenToClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->typeToDate('')
                ->assertMultipleDatesOnHasRecords($this::tooOldDate(), '', 6);
        });
    }

    public function testMultOnFromRecordlessToRecordsThenToRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordlessToRecordsThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->clickMultDates()
                ->assertMultipleDatesOffNoRecords($this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToRecordsThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::today())
                ->typeFromDate('')
                ->assertMultipleDatesOnHasRecords('', $this::today(), 3);
        });
    }

    public function testMultOnFromRecordsToRecordsThenFromRecordlesss(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::today())
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOnHasRecords($this::tooOldDate(), $this::today(), 6);
        });
    }

    public function testMultOnFromRecordsToRecordsThenToClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::today())
                ->typeToDate('')
                ->assertMultipleDatesOnHasRecords($this::today(), '', 3);
        });
    }

    public function testMultOnFromRecordsToRecordsThenToRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::today(), $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToRecordsThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::today())
                ->clickMultDates()
                ->assertMultipleDatesOffHasRecords($this::today(), 3);
        });
    }

    public function testMultOnFromClearToRecordlessThenFromRecordless(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), $this::tooOldDate());
        });
    }

    public function testMultOnFromClearToRecordlessThenFromRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::tooOldDate())
                ->typeFromDate($this::today())
                ->assertMultipleDatesOnNoRecords($this::today(), $this::tooOldDate());
        });
    }

    public function testMultOnFromClearToRecordlessThenToClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::tooOldDate())
                ->typeToDate('')
                ->assertMultipleDatesOnNoRecords('', '');
        });
    }

    public function testMultOnFromClearToRecordlessThenToRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->assertMultipleDatesOnNoRecords('', $this::today());
        });
    }

    public function testMultOnFromClearToRecordlessThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeToDate($this::tooOldDate())
                ->clickMultDates()
                ->assertMultipleDatesOffNoRecords('');
        });
    }

    public function testMultOnFromRecordlessToRecordlessThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::tooOldDate())
                ->typeFromDate('')
                ->assertMultipleDatesOnNoRecords('', $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordlessToRecordlessThenFromRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::tooOldDate())
                ->typeFromDate($this::today())
                ->assertMultipleDatesOnNoRecords($this::today(), $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordlessToRecordlessThenToClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::tooOldDate())
                ->typeFromDate('')
                ->assertMultipleDatesOnNoRecords('', $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordlessToRecordlessThenToRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->assertMultipleDatesOnHasRecords($this::tooOldDate(), $this::today(), 6);
        });
    }

    public function testMultOnFromRecordlessToRecordlessThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tooOldDate())
                ->typeToDate($this::tooOldDate())
                ->clickMultDates()
                ->assertMultipleDatesOffNoRecords($this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToRecordlessThenFromClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->typeFromDate('')
                ->assertMultipleDatesOnNoRecords('', $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToRecordlessThenFromRecordlesss(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->typeFromDate($this::tooOldDate())
                ->assertMultipleDatesOnNoRecords($this::tooOldDate(), $this::tooOldDate());
        });
    }

    public function testMultOnFromRecordsToRecordlessThenToClear(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->typeToDate('')
                ->assertMultipleDatesOnNoRecords($this::today(), '');
        });
    }

    public function testMultOnFromRecordsToRecordlessThenToRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->typeToDate($this::today())
                ->assertMultipleDatesOnHasRecords($this::today(), $this::today(), 3);
        });
    }

    public function testMultOnFromRecordsToRecordlessThenMultOff(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::today())
                ->typeToDate($this::tooOldDate())
                ->clickMultDates()
                ->assertMultipleDatesOffHasRecords($this::today(), 3);
        });
    }

    public function testDatesEqual(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $todayForAssertion = $browser->formatDateForAssertion($this::today());

            $browser->assertSearch(
                $this::today(),
                $this::today(),
                [
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ]
                ]
            );
        });
    }

    public function testFromDateLessThanToDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $todayForAssertion = $browser->formatDateForAssertion($this::today());
            $tomorrowForAssertion = $browser->formatDateForAssertion($this::tomorrow());

            $browser->assertSearch(
                $this::tomorrow(),
                $this::dayAfterTomorrow(),
                [
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowForAssertion
                    ]
                ]
            );
        });
    }

    public function testEarlyFromDateTodayToDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $todayForAssertion = $browser->formatDateForAssertion($this::today());
            $yesterdayForAssertion = $browser->formatDateForAssertion($this::yesterday());
            $yesterdaySubFiveForAssertion = $browser->formatDateForAssertion($this::yesterday()->subDays(5));
            $yesterdaySubSixForAssertion = $browser->formatDateForAssertion($this::yesterday()->subDays(6));

            $browser->assertSearch(
                $this::yesterdaySubSeven(),
                $this::today(),
                [
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $yesterdayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $yesterdaySubFiveForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $yesterdaySubSixForAssertion
                    ]
                ]
            );
        });
    }

    public function testTodayFromDateLateToDate(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $todayForAssertion = $browser->formatDateForAssertion($this::today());
            $tomorrowForAssertion = $browser->formatDateForAssertion($this::tomorrow());
            $tomorrowAddNineForAssertion = $browser->formatDateForAssertion($this::tomorrowAddNine());

            $browser->assertSearch(
                $this::today(),
                $this::tomorrowAddNine(),
                [
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowAddNineForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowAddNineForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ],
                    [
                        'Request Date' => $todayForAssertion,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $todayForAssertion
                    ]
                ]
            );
        });
    }

    public function testMissesEarlierRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::yesterdaySubEight())
                ->typeToDate($this::yesterdaySubSeven())
                ->assertMissingTable();
        });
    }

    public function testMissesLaterRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::tomorrowAddEleven())
                ->typeToDate($this::tomorrowAddFifteen())
                ->assertMissingTable();
        });
    }

    public function testCapturesWideRangeOfRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $today = $browser->formatDateForAssertion($this::today());
            $tomorrow = $browser->formatDateForAssertion($this::tomorrow());
            $tomorrowAddNine = $browser->formatDateForAssertion($this::tomorrowAddNine());
            $tomorrowAddTen = $browser->formatDateForAssertion($this::tomorrowAddTen());

            $yesterday = $browser->formatDateForAssertion($this::yesterday());
            $yesterdaySubFive = $browser->formatDateForAssertion($this::yesterdaySubFive());
            $yesterdaySubSix = $browser->formatDateForAssertion($this::yesterdaySubSix());

            $browser->assertSearch(
                $this::yesterdaySubFifteen(),
                $this::tomorrowAddFifteen(),
                [
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'B.C. Cancer Agency',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowAddTen
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowAddNine
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrowAddNine
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $tomorrow
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $today
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['borrow'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $today
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['lend'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $today
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '1',
                        'Unfulfilled Reason' => null,
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'Notes',
                        'Created At' => $yesterday
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $yesterdaySubFive
                    ],
                    [
                        'Request Date' => $today,
                        'Fulfilled' => '0',
                        'Unfulfilled Reason' => 'REASON',
                        'Resource' => ILLRequest::RESOURCES['book'],
                        'Action' => ILLRequest::ACTIONS['ship-to-me'],
                        'Library Name' => 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library',
                        'VCC Borrower Type' => ILLRequest::VCC_BORROWER_TYPES['student'],
                        'Requestor Notes' => 'New Notes',
                        'Created At' => $yesterdaySubSix
                    ]
                ]
            );
        });
    }

    public function testDatesEqualBeforeRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::yesterdaySubOne())
                ->typeToDate($this::yesterdaySubOne())
                ->assertMissingTable();
        });
    }

    public function testDatesEqualAfterRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::dayAfterTomorrow())
                ->typeToDate($this::dayAfterTomorrow())
                ->assertMissingTable();
        });
    }

    public function testFromDateLessThanToDateBeforeRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::yesterday()->subDays(2))
                ->typeToDate($this::yesterdaySubOne())
                ->assertMissingTable();
        });
    }

    public function testFromDateLessThanToDateAfterRecords(): void
    {
        $this->browse(function (RecordsBrowser $browser) {
            $browser->clickMultDates()
                ->typeFromDate($this::dayAfterTomorrow())
                ->typeToDate($this::tomorrowAddTwo())
                ->assertMissingTable();
        });
    }

    private static function today()
    {
        return Carbon::today();
    }

    private static function tooOldDate()
    {
        return Carbon::yesterday()->subDays(50);
    }

    private static function tomorrow()
    {
        return Carbon::tomorrow();
    }

    private static function yesterday()
    {
        return Carbon::yesterday();
    }

    private static function yesterdaySubOne()
    {
        return Carbon::yesterday()->subDays(1);
    }

    private static function yesterdaySubFive()
    {
        return Carbon::yesterday()->subDays(5);
    }

    private static function yesterdaySubSix()
    {
        return Carbon::yesterday()->subDays(6);
    }

    private static function yesterdaySubSeven()
    {
        return Carbon::yesterday()->subDays(7);
    }

    private static function yesterdaySubEight()
    {
        return Carbon::yesterday()->subDays(8);
    }

    private static function yesterdaySubFifteen()
    {
        return Carbon::yesterday()->subDays(15);
    }

    private static function dayAfterTomorrow()
    {
        return Carbon::tomorrow()->addDays(1);
    }

    private static function tomorrowAddTwo()
    {
        return Carbon::tomorrow()->addDays(2);
    }

    private static function tomorrowAddNine()
    {
        return Carbon::tomorrow()->addDays(9);
    }

    private static function tomorrowAddTen()
    {
        return Carbon::tomorrow()->addDays(10);
    }

    private static function tomorrowAddEleven()
    {
        return Carbon::tomorrow()->addDays(11);
    }

    private static function tomorrowAddFifteen()
    {
        return Carbon::tomorrow()->addDays(15);
    }
}
