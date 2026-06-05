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
        Schema::create('provider_formats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_name', 255);
            $table->string('brand_name', 255);
            $table->string('ean_name', 255);
            $table->string('description_name', 255);
            $table->string('dimensions_name', 255);
            $table->string('family_and_subfamily_name', 255);
            $table->string('price_name', 255);
            $table->string('unit_name', 255)->nullable();
            $table->string('stretch_name', 255);
            $table->bigInteger('provider_id');
            $table->timestamps();
            $table->foreign('provider_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_format');
    }
};
