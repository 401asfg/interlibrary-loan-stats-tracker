<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SearchableSelectTest extends DuskTestCase
{
    protected function newBrowser($driver)
    {
        $browser = new Browser($driver);
        return $browser->visit('/');
    }

    // TODO: write tests
}
