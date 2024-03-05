<?php

namespace Database\Seeders;

use App\Models\ILLRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ILLRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ILLRequest::factory()->count(10)->create();
    }
}
