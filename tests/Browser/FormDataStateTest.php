<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FormDataStateTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testInitialValues(): void
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

    public function testVccBorrowerFieldsClearOnLendAction(): void
    {
        // TODO: implement
    }

    public function testPartiesInvolvedClearsWhenShipToMeIsCleared(): void
    {
        // TODO: implement
    }
}
