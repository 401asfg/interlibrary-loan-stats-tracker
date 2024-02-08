<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        self::saveLibrariesFromCSV('data/ill_library_names.csv');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // FIXME: do we need to drop the referencing foreign key?
        Schema::dropIfExists('libraries');
    }

    private static function saveLibrariesFromCSV(string $filePath) {
        $names = self::readNamesFromCSV($filePath);
        self::saveLibraries($names);
    }

    private static function saveLibraries(array $names) {
        DB::table('libraries')->insert($names);
    }

    private static function readNamesFromCSV(string $filePath): array {
        $file = file(public_path($filePath));
        $names = [];

        foreach ($file as $name) {
            $names[] = ['name' => rtrim($name)];
        }

        return $names;
    }
};
