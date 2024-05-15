<?php

/*
 * Author: Michael Allan
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;

class LibrariesAPITest extends TestCase
{
    public function testIndexNoQuery(): void
    {
        $response = $this->get('libraries');
        $response->assertStatus(422);
    }

    public function testIndexEmptyQuery(): void
    {
        $response = $this->get('libraries?query=');
        $response->assertStatus(422);
    }

    public function testIndexSingleLetterQuery(): void
    {
        $response = $this->get('libraries?query=z');

        $response->assertStatus(200);

        $response->assertJson(
            function (AssertableJson $json) {
                return $json->has(4)
                    ->has(0, function (AssertableJson $json) {
                        return $json->where('id', 19)
                            ->where('name', 'Memorial University of Newfoundland, Queen Elizabeth II Library');
                    })
                    ->has(1, function (AssertableJson $json) {
                        return $json->where('id', 91)
                            ->where('name', 'Mackenzie Public Library');
                    })
                    ->has(2, function (AssertableJson $json) {
                        return $json->where('id', 99)
                            ->where('name', 'Hazelton District Public Library');
                    })
                    ->has(3, function (AssertableJson $json) {
                        return $json->where('id', 243)
                            ->where('name', 'New Zealand National Library');
                    });
            }
        );
    }

    public function testIndexWordQuery(): void
    {
        $response = $this->get('libraries?query=british');

        $response->assertStatus(200);

        $response->assertJson(
            function (AssertableJson $json) {
                return $json->has(2)
                    ->has(0, function (AssertableJson $json) {
                        return $json->where('id', 58)
                            ->where('name', 'University of British Columbia');
                    })
                    ->has(1, function (AssertableJson $json) {
                        return $json->where('id', 59)
                            ->where('name', 'University of Northern British Columbia');
                    });
            }
        );
    }

    public function testIndexMultiWordQuery(): void
    {
        $response = $this->get('libraries?query=regina+public+library');

        $response->assertStatus(200);
        $response->assertJsonCount(1);

        $response->assertSimilarJson([
            [
                'id' => 17,
                'name' => 'Regina Public Library'
            ]
        ]);
    }

    public function testIndexSpecialCharactersQuery(): void
    {
        $response = $this->get('libraries?query=coastal+health,+vancouver');

        $response->assertStatus(200);
        $response->assertJsonCount(1);

        $response->assertSimilarJson([
            [
                'id' => 8,
                'name' => 'Vancouver Coastal Health, Vancouver Community Library'
            ]
        ]);
    }

    public function testIndexInvalidQuery(): void
    {
        $response = $this->get('libraries?query=notreal');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function testShowZerothId(): void
    {
        $this->assertShowIsInvalid('0');
    }

    public function testShowFirstId(): void
    {
        $this->assertShow('1', 'Environment Canada, Pacific & Yukon Region, Environmental Protection Library');
    }

    public function testShowMiddleId(): void
    {
        $this->assertShow('172', 'Royal Inland Hospital');
    }

    public function testShowLastId(): void
    {
        $this->assertShow('345', 'Libraries Branch of B.C.');
    }

    public function testShowNegativeId(): void
    {
        $this->assertShowIsInvalid('-1');
        $this->assertShowIsInvalid('-3');
        $this->assertShowIsInvalid('-27');
    }

    public function testShowTooLargeId(): void
    {
        $this->assertShowIsInvalid('346');
        $this->assertShowIsInvalid('347');
        $this->assertShowIsInvalid('400');
    }

    private function assertShow($id, $expectedName): void
    {
        $response = $this->get('libraries/' . $id);

        $response->assertStatus(200);

        $response->assertJson(
            function (AssertableJson $json) use ($expectedName) {
                return $json->where('name', $expectedName);
            }
        );
    }

    private function assertShowIsInvalid($id): void
    {
        $response = $this->get('libraries/' . $id);

        $response->assertStatus(200);
        $response->assertSimilarJson([]);
    }
}
