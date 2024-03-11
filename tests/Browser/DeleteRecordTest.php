<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DeleteRecordTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testDeleteLastAddedRecord(): void
    {
        // TODO: implement
    }
}
