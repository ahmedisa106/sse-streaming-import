<?php

use App\ImportStatusEnum;
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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->unsignedInteger('processed_records')->default(0);
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedBigInteger('total_success')->default(0);
            $table->unsignedBigInteger('total_faild')->default(0);
            $table->unsignedTinyInteger('status')->default(ImportStatusEnum::PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
