<?php

/*
 * Author: Michael Allan
 */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\ILLRequest;
use App\Models\Library;

class ILLRequestFactory extends Factory
{
    const MAX_SUB_DAYS = 365;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'request_date' => self::randomDate(),
            'fulfilled' => $this->faker->boolean,
            'unfulfilled_reason' => self::randomSetValueOrNullableString(ILLRequest::UNFULFILLED_REASONS),
            'resource' => self::randomSetValueOrString(ILLRequest::RESOURCES),
            'action' => self::randomSetValue(ILLRequest::ACTIONS),
            'library_id' => self::randomNullableForeignKey(Library::count()),
            'vcc_borrower_type' => self::randomSetValue(ILLRequest::VCC_BORROWER_TYPES),
            'requestor_notes' => self::randomNullableString()
        ];
    }

    private function randomNullableForeignKey(int $maxId)
    {
        if ($this->faker->boolean)
            return null;
        return $this->faker->numberBetween(1, $maxId);
    }

    private function randomDate()
    {
        return Carbon::today()->subDays(rand(0, self::MAX_SUB_DAYS));
    }

    private function randomSetValueOrString(array $set): string
    {
        $str = self::randomNullableString();
        if ($str !== null)
            return $str;
        return self::randomSetValue($set);
    }

    private function randomSetValueOrNullableString(array $set): ?string
    {
        if ($this->faker->boolean)
            return self::randomSetValue($set);
        return self::randomNullableString();
    }

    private function randomNullableString(): ?string
    {
        if ($this->faker->boolean)
            return null;
        return $this->faker->name;
    }

    private function randomSetValue(array $set)
    {
        $values = array_values($set);
        $index = $this->faker->randomNumber() % count($values);
        return $values[$index];
    }
}
