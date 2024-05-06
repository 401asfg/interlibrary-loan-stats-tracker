<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Unit;

use Tests\TestCase;

class IndexAPITest extends TestCase
{
    public function testIndex(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('index');
    }
}
