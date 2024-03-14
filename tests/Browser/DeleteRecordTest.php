<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DeleteRecordTest extends DuskTestCase
{
    protected function createBrowser($driver)
    {
        return new Browser($driver);
    }

    public function testDeleteLastAddedRecord(): void
    {
        // TODO: implement
    }
}
