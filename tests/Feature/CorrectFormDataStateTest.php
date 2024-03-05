<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CorrectFormDataStateTest extends TestCase
{
    public function testDefaultDateIsToday(): void
    {
        // TODO: implement
    }

    public function testUnfulfilledReasonClearsOnFulfilled(): void
    {
        // TODO: implement
    }

    public function testOtherUnfulfilledReasonClearsOnNonOtherAndFulfilled(): void
    {
        // TODO: implement
    }

    public function testOtherResourceClearsOnNonOther(): void
    {
        // TODO: implement
    }

    public function testShipToMeActionClearsOnBookChapterOrEAResource(): void
    {
        // TODO: implement
    }

    public function testPartiesInvolvedInitiallyClear(): void
    {
        // TODO: implement
    }

    public function testLibraryIdClearsOnShipToMeAction(): void
    {
        // TODO: implement
    }

    public function testVCCBorrowerFieldsClearOnLendAction(): void
    {
        // TODO: implement
    }

    public function testPartiesInvolvedClearsWhenShipToMeIsCleared(): void
    {
        // TODO: implement
    }
}
