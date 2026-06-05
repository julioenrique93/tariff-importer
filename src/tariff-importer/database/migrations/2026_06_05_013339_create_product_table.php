<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference', 100)->unique();
            $table->string('brand', 100);
            $table->string('ean', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('dimensions', 255);
            $table->string('family_and_subfamily', 255);
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
        Schema::dropIfExists('product');
    }
};
