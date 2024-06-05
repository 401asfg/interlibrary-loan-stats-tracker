<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\ILLRequest;
use Carbon\Carbon;

class SelectRecordBrowser extends Browser
{
    private const IDS = [10, 8, 9, 5, 1, 2, 3, 4, 6, 7];

    public function assertHovered($index)
    {
        return $this->assertAttribute('@records_table_entry_Created_At_' . $index, 'class', 'focused-item');
    }

    public function assertNotHovered($index)
    {
        return $this->assertAttribute('@records_table_entry_Created_At_' . $index, 'class', '');
    }

    public function hover($index)
    {
        return $this->mouseover('@records_table_entry_Created_At_' . $index);
    }

    public function assertHoverOn($index)
    {
        return $this->hover($index)
            ->assertHovered($index);
    }

    public function assertHoverOff($oldIndex)
    {
        return $this->mouseover('@records_title')
            ->assertNotHovered($oldIndex);
    }

    public function assertHoverOnThenOff($index)
    {
        return $this->assertHoverOn($index)
            ->assertHoverOff($index);
    }

    public function assertHoverOnThenOffThenOn($indexA, $indexB)
    {
        return $this->assertHoverOnThenOff($indexA)
            ->assertHoverOn($indexB);
    }

    public function assertChangeHover($indexA, $indexB)
    {
        return $this->assertHoverOn($indexA)
            ->assertHoverOn($indexB)
            ->assertNotHovered($indexA);
    }

    public function clickRecord($index)
    {
        return $this->click('@records_table_entry_Created_At_' . $index);
    }

    public function assertClickRecord($index)
    {
        $recordId = $this::IDS[$index];

        return $this->clickRecord($index)
            ->waitForText('Summary')
            ->assertMissing('@submission_title')
            ->assertUrlIs(url('/') . '/ill-requests/' . $recordId);
    }

    public function typeViewDates()
    {
        return $this->type('@from_date', Carbon::yesterday()->subDays(6)->format('m-d-Y'))
            ->type('@to_date', Carbon::tomorrow()->addDays(10)->format('m-d-Y'))
            ->waitFor('@records_table_entry_Created_At_0');
    }

    public function fillOutView()
    {
        return $this->click('@multiple_checkbox')
            ->typeViewDates();
    }

    public function assertSubmitForm()
    {
        return $this->click('@resource_book')
            ->click('@action_borrow')
            ->type('@searchable_select_input', 'british')
            ->waitFor('@searchable_select_result_0', 10)
            ->click('@searchable_select_result_0')
            ->click('@vcc_borrower_type_student')
            ->click('@submit')
            ->waitFor('@submission_title')
            ->assertSee('Submission Successful!');
    }

    public function assertSubmitEditForm()
    {
        return $this->click('@submit')
            ->waitFor('@submission_title')
            ->assertSee('Edit Successful!');
    }
}

class SelectRecordTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        $browser = new SelectRecordBrowser($driver);
        return $browser;
    }

    protected function setUp(): void
    {
        parent::setUp();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "true",
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::today()->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "false",
            'unfulfilled_reason' => ILLRequest::UNFULFILLED_REASONS['google-scholar'],
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['borrow'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::today()->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "true",
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::today()->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "true",
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'Notes',
            'created_at' => Carbon::yesterday()->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "true",
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "false",
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::yesterday()->subDays(5)->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "false",
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['ship-to-me'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::yesterday()->subDays(6)->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "false",
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['student'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->addDays(9)->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "false",
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 1,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->addDays(9)->format('Y-m-d')
        ])->save();

        ILLRequest::create([
            'request_date' => Carbon::today()->format('Y-m-d'),
            'fulfilled' => "false",
            'unfulfilled_reason' => 'REASON',
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => 4,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['employee'],
            'requestor_notes' => 'New Notes',
            'created_at' => Carbon::tomorrow()->addDays(10)->format('Y-m-d')
        ])->save();

        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->visit('ill-requests/records')
                ->waitFor('@records_title')
                ->fillOutView();
        });
    }

    public function testHoverOne(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertHoverOn(0);
        });
    }

    public function testHoverMoveOff(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertHoverOnThenOff(0);
        });
    }

    public function testHoverDirectlyToNext(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertChangeHover(0, 1);
        });
    }

    public function testHoverMoveOffThenReturn(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertHoverOnThenOffThenOn(0, 0);
        });
    }

    public function testHoverMoveOffToOther(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertHoverOnThenOffThenOn(0, 3);
        });
    }

    public function testHoverThenClearRecordsThenHoverAgain(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertHoverOn(3)
                ->type('@from_date', '01-01-1999')
                ->type('@to_date', '01-01-1999')
                ->typeViewDates()
                ->waitFor('@records_table_entry_Created_At_0')
                ->assertNotHovered(2)
                ->assertHoverOn(2)
                ->assertNotHovered(3);
        });
    }

    public function testClickRecordClickViewRecordsAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(1)
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordClickViewRecordsFillOutDatesAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(1)
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordClickViewRecordsFillOutDatesAndClickRecord(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(1)
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->assertClickRecord(9);
        });
    }

    public function testClickRecordSubmitAnotherRecordViewRecordsAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(2)
                ->click('@submit')
                ->assertSubmitForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordSubmitAnotherRecordViewRecordsFillOutDatesAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(2)
                ->click('@submit')
                ->assertSubmitForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordSubmitAnotherRecordViewRecordsFillOutDatesAndClickRecord(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(2)
                ->click('@submit')
                ->assertSubmitForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->assertClickRecord(2);
        });
    }

    public function testClickRecordEditRecordViewRecordsAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(2)
                ->click('@edit')
                ->assertSubmitEditForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordEditRecordViewRecordsFillOutDatesAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(2)
                ->click('@edit')
                ->assertSubmitEditForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordEditRecordViewRecordsFillOutDatesAndClickRecord(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(2)
                ->click('@edit')
                ->assertSubmitEditForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->assertClickRecord(2);
        });
    }

    public function testClickRecordAndDeleteRecordViewRecordsAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(4)
                ->click('@delete')
                ->waitFor('@form_title')
                ->assertSubmitForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordAndDeleteRecordViewRecordsFillOutDatesAndClickBack(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(4)
                ->click('@delete')
                ->waitFor('@form_title')
                ->assertSubmitForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->click('@back')
                ->assertUrlIs(url('/') . '/');
        });
    }

    public function testClickRecordAndDeleteRecordViewRecordsFillOutDatesAndClickRecord(): void
    {
        $this->browse(function (SelectRecordBrowser $browser) {
            $browser->assertClickRecord(4)
                ->click('@delete')
                ->waitFor('@form_title')
                ->assertSubmitForm()
                ->click('@view_records')
                ->waitFor('@records_title')
                ->assertSee('Records Found: 0')
                ->fillOutView()
                ->assertClickRecord(3);
        });
    }
}
