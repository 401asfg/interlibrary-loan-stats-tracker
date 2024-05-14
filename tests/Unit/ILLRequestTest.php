<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Unit;

use App\Models\ILLRequest;
use Tests\TestCase;
use Carbon\Carbon;

class ILLRequestTest extends TestCase
{
    public function testGetLibraryNameWithNullId(): void
    {
        $this->assertGetsLibraryName(null, null);
    }

    public function testGetLibraryNameWithValidId(): void
    {
        $this->assertGetsLibraryName(1, 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library');
        $this->assertGetsLibraryName(158, 'Red River College');
        $this->assertGetsLibraryName(345, 'Libraries Branch of B.C.');
    }

    private function assertGetsLibraryName(?int $libraryId, ?string $expectedLibraryName): void
    {
        $illRequest = ILLRequestTest::createILLRequest($libraryId);
        $libraryName = $illRequest->getLibraryName();
        $this->assertEquals($libraryName, $expectedLibraryName);
    }

    private static function createILLRequest(?int $libraryId): ILLRequest
    {
        return ILLRequest::create([
            'request_date' => Carbon::today(),
            'fulfilled' => true,
            'unfulfilled_reason' => null,
            'resource' => ILLRequest::RESOURCES['book'],
            'action' => ILLRequest::ACTIONS['lend'],
            'library_id' => $libraryId,
            'vcc_borrower_type' => ILLRequest::VCC_BORROWER_TYPES['library'],
            'requestor_notes' => null
        ]);
    }
}
