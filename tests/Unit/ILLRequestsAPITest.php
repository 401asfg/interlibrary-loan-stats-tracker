<?php

namespace Tests\Unit;

use App\Models\ILLRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;

class ILLRequestsAPITest extends TestCase
{
    use RefreshDatabase;

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

    public function testStore(): void
    {
        // TODO: implement
        // FIXME: test all permutations of null field submissions
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
}
