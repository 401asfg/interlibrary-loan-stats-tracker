<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class IndexTest extends DuskTestCase
{
    public function testClickSubmit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->click('@submit')
                ->waitFor('@form_title')
                ->assertVisible('@form_title');
        });
    }

    public function testClickViewRecords(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->click('@view_records')
                ->waitFor('@records_title')
                ->assertVisible('@records_title');
        });
    }
}
