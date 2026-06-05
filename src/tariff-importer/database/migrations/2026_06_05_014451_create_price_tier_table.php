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
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('min_quantity');
            $table->integer('max_quantity')->nullable();
            $table->decimal('unit_price', 20,2);
            $table->bigInteger('product_unit_id');
            $table->timestamps();
            $table->foreign('product_unit_id')
                ->references('id')
                ->on('product_units')
                ->onDelete('cascade')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tier');
    }
};
