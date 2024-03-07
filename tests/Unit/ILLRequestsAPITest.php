<?php

namespace Tests\Unit;

use App\Models\ILLRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use SebastianBergmann\Type\VoidType;
use Carbon\Carbon;
use Tests\TestCase;

class MockILLRequest
{
    private string|null $vccBorrowerType;
    private string|null $action;
    private string|null $resource;
    private string|null $fulfilled;
    private string|null $unfulfilledReason;
    private int|null $libraryId;
    private string|null $vccBorrowerNotes;
    private string|null $requestDate;

    function __construct(
        string|null $vccBorrowerType = ILLRequest::VCC_BORROWER_TYPES['library'],
        string|null $action = ILLRequest::ACTIONS['borrow'],
        string|null $resource = ILLRequest::RESOURCES['book'],
        string|null $fulfilled = 'false',
        string|null $unfulfilledReason = 'test reason',
        bool $libraryIdIsNull = false,
        string|null $vccBorrowerNotes = 'test notes',
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
        $this->vccBorrowerNotes = $vccBorrowerNotes;
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
            'vcc_borrower_notes' => $this->vccBorrowerNotes
        ];
    }

    public function isEqual(ILLRequest $illRequest): bool
    {
        return $this->requestDate === $illRequest->request_date
            && $this->fulfilled === $illRequest->fulfilled
            && $this->unfulfilledReason === $illRequest->unfulfilled_reason
            && $this->action === $illRequest->action
            && $this->resource === $illRequest->resource
            && $this->libraryId === $illRequest->library_id
            && $this->vccBorrowerType === $illRequest->vcc_borrower_type
            && $this->vccBorrowerNotes === $illRequest->vcc_borrower_notes;
    }
}

class ILLRequestsAPITest extends TestCase
{
    use RefreshDatabase;

    const MAX_REQUEST_DATE_DAYS_AGO = 180;
    const MIN_LIBRARY_ID = 1;
    const MAX_LIBRARY_ID = 345;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');
    }

    public function testIndex(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('create');
    }

    public function testCreate(): void
    {
        $response = $this->get('/create');

        $response->assertStatus(200);
        $response->assertViewIs('form');
    }

    public function testShow(): void
    {
        $firstILLRequest = ILLRequest::first();

        // FIXME: remove show from url
        $response = $this->get('/show/' . $firstILLRequest->id);
        $response->assertStatus(200);
        $response->assertViewIs('submission');

        $response->assertViewHasAll([
            'illRequest' => $firstILLRequest,
            'libraryName' => $firstILLRequest->getLibraryName()
        ]);
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

    public function testStoreNullVCCBorrowerType(): void
    {
        $this->assertPostFailed(
            new MockILLRequest(
                null
            )
        );
    }

    public function testStoreNullVCCBorrowerNotes(): void
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

        $response = $this->delete($id);
        $response->assertStatus(302);
        $response->assertRedirect('create');

        $response->assertSessionHas([
            'status' => 'Last submission deleted!'
        ]);

        $deletedILLRequest = ILLRequest::find($id);
        $this->assertNull($deletedILLRequest);
    }

    public function testEdit(): void
    {
        $firstILLRequest = ILLRequest::first();

        $response = $this->get($firstILLRequest->id . '/edit');
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

    public function testUpdateNullVCCBorrowerType(): void
    {
        $this->assertPutFailed(
            new MockILLRequest(
                null
            )
        );
    }

    public function testUpdateNullVCCBorrowerNotes(): void
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
        $response->assertRedirect('/show/' . $afterPostMaxId);
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
        $response->assertRedirect('/show/' . $id);

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

        $this->post('/', $postILLRequest->getAttributes());
        return ILLRequestsAPITest::getLatestILLRequest()->id;
    }

    private static function getLatestILLRequest(): ILLRequest
    {
        return ILLRequest::find(DB::table('ill_requests')->max('id'));
    }

    private function callRestMethod(string $method, string|null $id, MockILLRequest $illRequest)
    {
        return $this->call($method, '/' . $id ?? '', $illRequest->getAttributes());
    }
}
