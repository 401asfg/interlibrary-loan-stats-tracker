<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ILLRequestFactory extends Factory {
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
            'unfulfilled_reason' => self::randomEnumValueOrNullableString('unfulfilled_reasons'),
            'resource' => self::randomEnumValueOrString('resources'),
            'action' => self::randomEnumValue('actions'),
            'library' => self::randomNullableString(),
            'requestor_type' => self::randomEnumValue('requestor_types'),
            'requestor_notes' => self::randomNullableString()
        ];
    }

    private function randomDate() {
        return Carbon::today()->subDays(rand(0, self::MAX_SUB_DAYS));
    }

    private function randomEnumValueOrString(string $global_enum_name): string {
        $str = self::randomNullableString();
        if ($str !== null) return $str;
        return self::randomEnumValue($global_enum_name);
    }

    private function randomEnumValueOrNullableString(string $global_enum_name): ?string {
        if ($this->faker->boolean) self::randomEnumValue($global_enum_name);
        return self::randomNullableString();
    }

    private function randomNullableString(): ?string {
        if ($this->faker->boolean) return null;
        return $this->faker->name;
    }

    private function randomEnumValue(string $global_enum_name): string {
        $values = config('global.' . $global_enum_name);
        $index = $this->faker->randomNumber() % count($values);
        return $values[$index];
    }
}
