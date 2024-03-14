<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SearchableSelectTest extends DuskTestCase
{
    protected function createBrowser($driver)
    {
        return new Browser($driver);
    }

    // TODO: write tests
}
