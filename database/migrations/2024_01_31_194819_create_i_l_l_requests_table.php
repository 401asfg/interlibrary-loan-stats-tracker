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
        Schema::create('ill_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('request_date');
            $table->boolean('fulfilled')->default(true);
            $table->string('unfulfilled_reason')->nullable();
            $table->string('resource');
            $table->enum('action', config('global.actions'));
            $table->string('library')->nullable();
            $table->enum('requestor_type', config('global.requestor_types'));
            $table->string('requestor_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ill_requests');
    }
};
