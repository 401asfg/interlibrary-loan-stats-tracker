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
            ILLRequest::VCC_BORROWER_TYPES['student'],
            ILLRequest::ACTIONS['borrow'],
            ILLRequest::RESOURCES['ea'],
            'true',
            null
        );
    }

    public function testStoreUnfulfilledBorrowing(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['borrow'],
            ILLRequest::RESOURCES['ea']
        );
    }

    public function testStoreFulfilledLending(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['lend'],
            ILLRequest::RESOURCES['ea'],
            'true',
            null
        );
    }

    public function testStoreUnfulfilledLending(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['lend'],
            ILLRequest::RESOURCES['book-chapter']
        );
    }

    public function testStoreFulfilledShipToMe(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            ILLRequest::RESOURCES['book'],
            'true',
            null
        );
    }

    public function testStoreUnfulfilledShipToMe(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            'Other resource'
        );
    }

    public function testStoreNoNullFields(): void
    {
        $this->assertPostSuccessful();
    }

    public function testStoreNullRequestDate(): void
    {
        $this->assertPostFailed(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            ILLRequest::RESOURCES['book'],
            'false',
            'test reason',
            false,
            'test notes',
            true
        );
    }

    public function testStoreNullFulfilled(): void
    {
        $this->assertPostFailed(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            ILLRequest::RESOURCES['book'],
            null,
        );
    }

    public function testStoreNullUnfulfilledReason(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            ILLRequest::RESOURCES['book'],
            'false',
            null
        );
    }

    public function testStoreNullResource(): void
    {
        $this->assertPostFailed(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            null
        );
    }

    public function testStoreNullAction(): void
    {
        $this->assertPostFailed(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            null
        );
    }

    public function testStoreNullLibraryId(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            ILLRequest::RESOURCES['book'],
            'false',
            'test reason',
            true
        );
    }

    public function testStoreNullVCCBorrowerType(): void
    {
        $this->assertPostFailed(null);
    }

    public function testStoreNullVCCBorrowerNotes(): void
    {
        $this->assertPostSuccessful(
            ILLRequest::VCC_BORROWER_TYPES['employee'],
            ILLRequest::ACTIONS['ship-to-me'],
            ILLRequest::RESOURCES['book'],
            'false',
            'test reason',
            false,
            null
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

    public function testUpdate(): void
    {
        // TODO: implement
        // FIXME: test all permutations of null field submissions
    }

    private function assertPostSuccessful(
        string|null $vccBorrowerType = ILLRequest::VCC_BORROWER_TYPES['library'],
        string|null $action = ILLRequest::ACTIONS['borrow'],
        string|null $resource = ILLRequest::RESOURCES['book'],
        string|null $fulfilled = 'false',
        string|null $unfulfilledReason = 'test reason',
        bool $libraryIdIsNull = false,
        string|null $vccBorrowerNotes = 'test notes',
        bool $requestDateIsNull = false
    ) {
        $response = $this->postILLRequest(
            $vccBorrowerType,
            $action,
            $resource,
            $fulfilled,
            $unfulfilledReason,
            $libraryIdIsNull,
            $vccBorrowerNotes,
            $requestDateIsNull
        );

        $response->assertStatus(302);
        $id = ILLRequest::find(DB::table('ill_requests')->max('id'))->id;
        $response->assertRedirect('/show/' . $id);
    }

    private function assertPostFailed(
        string|null $vccBorrowerType = ILLRequest::VCC_BORROWER_TYPES['library'],
        string|null $action = ILLRequest::ACTIONS['borrow'],
        string|null $resource = ILLRequest::RESOURCES['book'],
        string|null $fulfilled = 'false',
        string|null $unfulfilledReason = 'test reason',
        bool $libraryIdIsNull = false,
        string|null $vccBorrowerNotes = 'test notes',
        bool $requestDateIsNull = false
    ) {
        $beforePostMaxId = ILLRequest::find(DB::table('ill_requests')->max('id'))->id;

        $response = $this->postILLRequest(
            $vccBorrowerType,
            $action,
            $resource,
            $fulfilled,
            $unfulfilledReason,
            $libraryIdIsNull,
            $vccBorrowerNotes,
            $requestDateIsNull
        );

        $response->assertStatus(422);
        $afterPostMaxId = ILLRequest::find(DB::table('ill_requests')->max('id'))->id;

        $this->assertEquals($beforePostMaxId, $afterPostMaxId);
    }

    private function postILLRequest(
        string|null $vccBorrowerType = ILLRequest::VCC_BORROWER_TYPES['library'],
        string|null $action = ILLRequest::ACTIONS['borrow'],
        string|null $resource = ILLRequest::RESOURCES['book'],
        string|null $fulfilled = 'false',
        string|null $unfulfilledReason = 'test reason',
        bool $libraryIdIsNull = false,
        string|null $vccBorrowerNotes = 'test notes',
        bool $requestDateIsNull = false
    ) {
        $requestDate = null;
        $libraryId = null;

        if (!$requestDateIsNull)
            $requestDate = Carbon::today()->subDays(rand(0, ILLRequestsAPITest::MAX_REQUEST_DATE_DAYS_AGO))->toDateString();

        if (!$libraryIdIsNull)
            $libraryId = rand(ILLRequestsAPITest::MIN_LIBRARY_ID, ILLRequestsAPITest::MAX_LIBRARY_ID);


        return $this->post('/', [
            'request_date' => $requestDate,
            'fulfilled' => $fulfilled,
            'unfulfilled_reason' => $unfulfilledReason,
            'resource' => $resource,
            'action' => $action,
            'library_id' => $libraryId,
            'vcc_borrower_type' => $vccBorrowerType,
            'vcc_borrower_notes' => $vccBorrowerNotes
        ]);
    }
}
