<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ILLRequest;

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
            $table->enum('action', array_values(ILLRequest::ACTIONS));
            $table->string('library')->nullable();
            $table->enum('requestor_type', array_values(ILLRequest::REQUESTOR_TYPES));
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