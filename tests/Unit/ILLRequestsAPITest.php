<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Unit;

use App\Models\ILLRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use SebastianBergmann\Type\VoidType;
use Carbon\Carbon;
use Tests\TestCase;

class MockILLRequest
{
    private $vccBorrowerType;
    private $action;
    private $resource;
    private $fulfilled;
    private $unfulfilledReason;
    private $libraryId;
    private $requestorNotes;
    private $requestDate;

    function __construct(
        ?string $vccBorrowerType = ILLRequest::VCC_BORROWER_TYPES['library'],
        ?string $action = ILLRequest::ACTIONS['borrow'],
        ?string $resource = ILLRequest::RESOURCES['book'],
        ?string $fulfilled = 'false',
        ?string $unfulfilledReason = 'test reason',
        bool $libraryIdIsNull = false,
        ?string $requestorNotes = 'test notes',
        bool $requestDateIsNull = false
    ) {
        $this->requestDate = null;
        $this->libraryId = null;

        if (!$requestDateIsNull)
            $this->requestDate = Carbon::today()->subDays(rand(0, ILLRequestsAPITest::MAX_REQUEST_DATE_DAYS_AGO))->toDateString();

        if (!$libraryIdIsNull)
            $this->libraryId = rand(ILLRequestsAPITest::MIN_LIBRARY_ID, ILLRequestsAPITest::MAX_LIBRARY_ID);

        $this->vccBorrowerType = $vccBorrowerType;
        $this->action = $action;
        $this->resource = $resource;
        $this->fulfilled = $fulfilled;
        $this->unfulfilledReason = $unfulfilledReason;
        $this->requestorNotes = $requestorNotes;
    }

    public function getAttributes()
    {
        return [
            'request_date' => $this->requestDate,
            'fulfilled' => $this->fulfilled,
            'unfulfilled_reason' => $this->unfulfilledReason,
            'action' => $this->action,
            'resource' => $this->resource,
            'library_id' => $this->libraryId,
            'vcc_borrower_type' => $this->vccBorrowerType,
            'requestor_notes' => $this->requestorNotes
        ];
    }

    public function isEqual(ILLRequest $illRequest): bool
    {
        return $this->requestDate === $illRequest->request_date
            && $this->fulfilled == $illRequest->fulfilled
            && $this->unfulfilledReason === $illRequest->unfulfilled_reason
            && $this->action === $illRequest->action
            && $this->resource === $illRequest->resource
            && $this->libraryId == $illRequest->library_id
            && $this->vccBorrowerType === $illRequest->vcc_borrower_type
            && $this->requestorNotes === $illRequest->requestor_notes;
    }
}

class ILLRequestsAPITest extends TestCase
{
    const MAX_REQUEST_DATE_DAYS_AGO = 180;
    const MIN_LIBRARY_ID = 1;
    const MAX_LIBRARY_ID = 345;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
    }

    public function testCreate(): void
    {
        $response = $this->get('ill-requests/create');

        $response->assertStatus(200);
        $response->assertViewIs('form');
    }

    public function testIndexNoParams(): void
    {
        $this->assertIndexMissingParameters('');
    }

    public function testIndexNoDates(): void
    {
        $this->assertIndexMissingParameters('fromDate=&toDate=');
    }

    public function testIndexNoFromDate(): void
    {
        $this->assertIndexMissingParameters('fromDate=&toDate=' . Carbon::today());
    }

    public function testIndexNoToDate(): void
    {
        $this->assertIndexMissingParameters('fromDate=' . Carbon::today() . '&toDate=');
    }

    public function testIndexDatesEqual(): void
    {
        $this->setupIndex();

        $today = explode('T', Carbon::today())[0];

        $this->assertIndex(
            'fromDate=' . Carbon::today() . '&toDate=' . Carbon::today(),
            [
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
                ]
            ]
        );
    }

    public function testIndexToDateLessThanFromDate(): void
    {
        $this->assertIndexEmpty('fromDate=' . Carbon::tomorrow() . '&toDate=' . Carbon::today());
    }

    public function testIndexFromDateLessThanToDate(): void
    {
        $this->setupIndex();

        $today = explode('T', Carbon::today())[0];
        $tomorrow = explode('T', Carbon::tomorrow())[0];

        $this->assertIndex(
            'fromDate=' . Carbon::tomorrow() . '&toDate=' . Carbon::tomorrow()->addDays(1),
            [
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
                ]
            ]
        );
    }

    public function testIndexEarlyFromDateTodayToDate(): void
    {
        $this->setupIndex();

        $today = explode('T', Carbon::today())[0];
        $yesterday = explode('T', Carbon::yesterday())[0];
        $yesterdaySubFive = explode('T', Carbon::yesterday()->subDays(5))[0];
        $yesterdaySubSix = explode('T', Carbon::yesterday()->subDays(6))[0];

        $this->assertIndex(
            'fromDate=' . Carbon::yesterday()->subDays(7) . '&toDate=' . Carbon::today(),
            [
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
    }

    public function testIndexTodayFromDateLateToDate(): void
    {
        $this->setupIndex();

        $today = explode('T', Carbon::today())[0];
        $tomorrow = explode('T', Carbon::tomorrow())[0];
        $tomorrowAddNine = explode('T', Carbon::tomorrow()->addDays(9))[0];

        $this->assertIndex(
            'fromDate=' . Carbon::today() . '&toDate=' . Carbon::tomorrow()->addDays(9),
            [
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
                    'Requestor Notes' => 'New Notes',
                    'Created At' => $tomorrow
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
                ]
            ]
        );
    }

    public function testIndexMissesEarlierRecords(): void
    {
        $this->setupIndex();
        $this->assertIndexEmpty('fromDate=' . Carbon::yesterday()->subDays(8) . '&toDate=' . Carbon::yesterday()->subDays(7));
    }

    public function testIndexMissesLaterRecords(): void
    {
        $this->setupIndex();
        $this->assertIndexEmpty('fromDate=' . Carbon::tomorrow()->addDays(11) . '&toDate=' . Carbon::tomorrow()->addDays(15));
    }

    public function testIndexCapturesWideRangeOfRecords(): void
    {
        $this->setupIndex();

        $today = explode('T', Carbon::today())[0];
        $tomorrow = explode('T', Carbon::tomorrow())[0];
        $tomorrowAddNine = explode('T', Carbon::tomorrow()->addDays(9))[0];
        $tomorrowAddTen = explode('T', Carbon::tomorrow()->addDays(10))[0];

        $yesterday = explode('T', Carbon::yesterday())[0];
        $yesterdaySubFive = explode('T', Carbon::yesterday()->subDays(5))[0];
        $yesterdaySubSix = explode('T', Carbon::yesterday()->subDays(6))[0];

        $this->assertIndex(
            'fromDate=' . Carbon::yesterday()->subDays(15) . '&toDate=' . Carbon::tomorrow()->addDays(15),
            [
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
                    'Requestor Notes' => 'New Notes',
                    'Created At' => $tomorrow
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
    }

    public function testIndexDatesEqualBeforeRecords(): void
    {
        $this->assertIndexEmpty('fromDate=' . Carbon::yesterday() . '&toDate=' . Carbon::yesterday());
    }

    public function testIndexDatesEqualAfterRecords(): void
    {
        $this->assertIndexEmpty('fromDate=' . Carbon::tomorrow()->addDays(1) . '&toDate=' . Carbon::tomorrow()->addDays(1));
    }

    public function testIndexFromDateLessThanToDateBeforeRecords(): void
    {
        $this->assertIndexEmpty('fromDate=' . Carbon::yesterday()->subDays(1) . '&toDate=' . Carbon::yesterday());
    }

    public function testIndexFromDateLessThanToDateAfterRecords(): void
    {
        $this->assertIndexEmpty('fromDate=' . Carbon::tomorrow()->addDays(1) . '&toDate=' . Carbon::tomorrow()->addDays(2));
    }

    public function testIndexNonDateFromDate(): void
    {
        $this->assertIndexMissingParameters('fromDate=x&toDate=' . Carbon::today());
    }

    public function testIndexNonDateToDate(): void
    {
        $this->assertIndexMissingParameters('fromDate=' . Carbon::today() . '&toDate=date');
    }

    public function testIndexNonDateFromAndToDate(): void
    {
        $this->assertIndexMissingParameters('fromDate=20240101&toDate=53531');
    }

    public function testRecords(): void
    {
        $response = $this->get('ill-requests/records');

        $response->assertStatus(200);
        $response->assertViewIs('records');
    }

    public function testShow(): void
    {
        $firstILLRequest = ILLRequest::first();

        $response = $this->get('ill-requests/' . $firstILLRequest->id);
        $response->assertStatus(200);
        $response->assertViewIs('submission');

        $response->assertViewHasAll([
            'illRequest' => $firstILLRequest,
            'libraryName' => $firstILLRequest->getLibraryName()
        ]);
    }

    public function testShowNoId(): void
    {
        $response = $this->get('ill-requests');
        $response->assertStatus(422);
    }

    public function testShowInvalidId(): void
    {
        $response = $this->get('ill-requests/3i');
        $response->assertStatus(422);
    }

    public function testShowOutOfBoundsId(): void
    {
        $response = $this->get('ill-requests/-6');
        $response->assertStatus(404);
    }

    public function testStoreFulfilledBorrowing(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['student'],
                ILLRequest::ACTIONS['borrow'],
                ILLRequest::RESOURCES['ea'],
                'true',
                null
            )
        );
    }

    public function testStoreUnfulfilledBorrowing(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['borrow'],
                ILLRequest::RESOURCES['ea']
            )
        );
    }

    public function testStoreFulfilledRenewing(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['student'],
                ILLRequest::ACTIONS['renewal'],
                ILLRequest::RESOURCES['ea'],
                'true',
                null
            )
        );
    }

    public function testStoreUnfulfilledRenewing(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['renewal'],
                ILLRequest::RESOURCES['ea']
            )
        );
    }

    public function testStoreFulfilledLending(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['lend'],
                ILLRequest::RESOURCES['ea'],
                'true',
                null
            )
        );
    }

    public function testStoreUnfulfilledLending(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['lend'],
                ILLRequest::RESOURCES['book-chapter']
            )
        );
    }

    public function testStoreFulfilledShipToMe(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'true',
                null
            )
        );
    }

    public function testStoreUnfulfilledShipToMe(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                'Other resource'
            )
        );
    }

    public function testStoreNoNullFields(): void
    {
        $this->assertPostSuccessful(new MockILLRequest());
    }

    public function testStoreNullRequestDate(): void
    {
        $this->assertPostFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                'test reason',
                false,
                'test notes',
                true
            )
        );
    }

    public function testStoreNullFulfilled(): void
    {
        $this->assertPostFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                null,
            )
        );
    }

    public function testStoreNullUnfulfilledReason(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                null
            )
        );
    }

    public function testStoreNullResource(): void
    {
        $this->assertPostFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                null
            )
        );
    }

    public function testStoreNullAction(): void
    {
        $this->assertPostFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                null
            )
        );
    }

    public function testStoreNullLibraryId(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                'test reason',
                true
            )
        );
    }

    public function testStoreNullVccBorrowerType(): void
    {
        $this->assertPostFailed(
            new MockILLRequest(
                null
            )
        );
    }

    public function testStoreNullRequestorNotes(): void
    {
        $this->assertPostSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                'test reason',
                false,
                null
            )
        );
    }

    public function testDestory(): void
    {
        $id = ILLRequest::first()->id;

        $response = $this->delete('ill-requests/' . $id);
        $response->assertStatus(302);
        $response->assertRedirect('ill-requests/create');

        $response->assertSessionHas([
            'status' => 'Last submission deleted!'
        ]);

        $deletedILLRequest = ILLRequest::find($id);
        $this->assertNull($deletedILLRequest);
    }

    public function testDestroyNoId(): void
    {
        $response = $this->delete('ill-requests');
        $response->assertStatus(405);
    }

    public function testDestroyInvalidId(): void
    {
        $response = $this->delete('ill-requests/x');
        $response->assertStatus(422);
    }

    public function testDestroyOutOfBoundsId(): void
    {
        $response = $this->delete('ill-requests/-1');
        $response->assertStatus(404);
    }

    public function testEdit(): void
    {
        $firstILLRequest = ILLRequest::first();

        $response = $this->get('ill-requests/' . $firstILLRequest->id . '/edit');
        $response->assertStatus(200);
        $response->assertViewIs('form');

        $response->assertViewHasAll([
            'actions' => ILLRequest::ACTIONS,
            'vccBorrowerTypes' => ILLRequest::VCC_BORROWER_TYPES,
            'unfulfilledReasons' => ILLRequest::UNFULFILLED_REASONS,
            'resources' => ILLRequest::RESOURCES,
            'illRequest' => $firstILLRequest,
            'libraryName' => $firstILLRequest->getLibraryName()
        ]);
    }

    public function testEditNoId(): void
    {
        $response = $this->get('ill-requests//edit');
        $response->assertStatus(404);
    }

    public function testEditInvalidId(): void
    {
        $response = $this->get('ill-requests/x/edit');
        $response->assertStatus(422);
    }

    public function testEditOutOfBoundsId(): void
    {
        $response = $this->get('ill-requests/-7/edit');
        $response->assertStatus(404);
    }

    public function testUpdateFulfilledBorrowing(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['student'],
                ILLRequest::ACTIONS['borrow'],
                ILLRequest::RESOURCES['ea'],
                'true',
                null
            )
        );
    }

    public function testUpdateUnfulfilledBorrowing(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['borrow'],
                ILLRequest::RESOURCES['ea']
            )
        );
    }

    public function testUpdateFulfilledRenewing(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['student'],
                ILLRequest::ACTIONS['renewal'],
                ILLRequest::RESOURCES['ea'],
                'true',
                null
            )
        );
    }

    public function testUpdateUnfulfilledRenewing(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['renewal'],
                ILLRequest::RESOURCES['ea']
            )
        );
    }

    public function testUpdateFulfilledLending(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['lend'],
                ILLRequest::RESOURCES['ea'],
                'true',
                null
            )
        );
    }

    public function testUpdateUnfulfilledLending(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['lend'],
                ILLRequest::RESOURCES['book-chapter']
            )
        );
    }

    public function testUpdateFulfilledShipToMe(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'true',
                null
            )
        );
    }

    public function testUpdateUnfulfilledShipToMe(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                'Other resource'
            )
        );
    }

    public function testUpdateNoNullFields(): void
    {
        $this->assertPutSuccessful(new MockILLRequest());
    }

    public function testUpdateNullRequestDate(): void
    {
        $this->assertPutFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                'test reason',
                false,
                'test notes',
                true
            )
        );
    }

    public function testUpdateNullFulfilled(): void
    {
        $this->assertPutFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                null,
            )
        );
    }

    public function testUpdateNullUnfulfilledReason(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                null
            )
        );
    }

    public function testUpdateNullResource(): void
    {
        $this->assertPutFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                null
            )
        );
    }

    public function testUpdateNullAction(): void
    {
        $this->assertPutFailed(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                null
            )
        );
    }

    public function testUpdateNullLibraryId(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                'test reason',
                true
            )
        );
    }

    public function testUpdateNullVccBorrowerType(): void
    {
        $this->assertPutFailed(
            new MockILLRequest(
                null
            )
        );
    }

    public function testUpdateNullRequestorNotes(): void
    {
        $this->assertPutSuccessful(
            new MockILLRequest(
                ILLRequest::VCC_BORROWER_TYPES['employee'],
                ILLRequest::ACTIONS['ship-to-me'],
                ILLRequest::RESOURCES['book'],
                'false',
                'test reason',
                false,
                null
            )
        );
    }

    public function testUpdateNoId(): void
    {
        $response = $this->put('ill-requests');
        $response->assertStatus(405);
    }

    public function testUpdateInvalidId(): void
    {
        $response = $this->put('ill-requests/x');
        $response->assertStatus(422);
    }

    public function testUpdateOutOfBoundsId(): void
    {
        $response = $this->put('ill-requests/-7');
        $response->assertStatus(422);
    }

    private function setupIndex()
    {
        Artisan::call("migrate:fresh");

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
    }

    private function assertIndex(string $params, array $expectedResponse)
    {
        $response = $this->get('ill-requests?' . $params);

        $response->assertStatus(200);
        $response->assertSimilarJson($expectedResponse);
    }

    private function assertIndexMissingParameters(string $params)
    {
        $response = $this->get('ill-requests?' . $params);
        $response->assertStatus(422);
    }

    private function assertIndexEmpty(string $params)
    {
        $this->assertIndex($params, []);
    }

    private function assertPostSuccessful(MockILLRequest $illRequest)
    {
        $beforePostMaxId = ILLRequestsAPITest::getLatestILLRequest()->id;

        $response = $this->callRestMethod(
            'POST',
            null,
            $illRequest
        );

        $response->assertStatus(302);

        $afterPostLatestILLRequest = ILLRequestsAPITest::getLatestILLRequest();
        $afterPostMaxId = $afterPostLatestILLRequest->id;

        $this->assertEquals($beforePostMaxId + 1, $afterPostMaxId);
        $this->assertTrue($illRequest->isEqual($afterPostLatestILLRequest));
        $response->assertRedirect('ill-requests/' . $afterPostMaxId);
    }

    private function assertPostFailed(MockILLRequest $illRequest)
    {
        $beforePostMaxId = ILLRequestsAPITest::getLatestILLRequest()->id;

        $response = $this->callRestMethod(
            'POST',
            null,
            $illRequest
        );

        $response->assertStatus(422);
        $afterPostMaxId = ILLRequestsAPITest::getLatestILLRequest()->id;

        $this->assertEquals($beforePostMaxId, $afterPostMaxId);
    }

    private function assertPutSuccessful(MockILLRequest $illRequest)
    {
        $id = $this->postForPutTest();
        $response = $this->callRestMethod(
            'PUT',
            $id,
            $illRequest
        );

        $response->assertStatus(302);
        $response->assertRedirect('ill-requests/' . $id);

        $updatedILLRequest = ILLRequest::find($id);
        $this->assertTrue($illRequest->isEqual($updatedILLRequest));
    }

    private function assertPutFailed(MockILLRequest $illRequest)
    {
        $id = $this->postForPutTest();
        $beforePutILLRequest = ILLRequest::find($id);

        $response = $this->callRestMethod(
            'PUT',
            $id,
            $illRequest
        );

        $response->assertStatus(422);
        $afterPutILLRequest = ILLRequest::find($id);

        $this->assertEquals($beforePutILLRequest->getAttributes(), $afterPutILLRequest->getAttributes());
    }

    private function postForPutTest(): int
    {
        $postILLRequest = new MockILLRequest(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['lend'],
            'put test resource',
            'true',
            'put test reason',
            false,
            'put test notes'
        );

        $this->post('ill-requests', $postILLRequest->getAttributes());
        return ILLRequestsAPITest::getLatestILLRequest()->id;
    }

    private static function getLatestILLRequest(): ILLRequest
    {
        return ILLRequest::find(DB::table('ill_requests')->max('id'));
    }

    private function callRestMethod(string $method, ?string $id, MockILLRequest $illRequest)
    {
        return $this->call($method, 'ill-requests/' . $id ?? '', $illRequest->getAttributes());
    }
}
