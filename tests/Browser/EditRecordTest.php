<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EditRecordTest extends DuskTestCase
{
    protected function createBrowser($driver)
    {
        return new Browser($driver);
    }

    public function testEditLastAddedRecordPopulatesAllFields(): void
    {
        // TODO: implement
    }
}
