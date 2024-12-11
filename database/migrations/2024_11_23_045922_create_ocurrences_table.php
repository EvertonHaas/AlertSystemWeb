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
        Schema::create('ocurrences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('vendorid');
            $table->string('productid');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('value');
            $table->timestamp('dateinsert');
            $table->timestamps();

            $table->foreign('productid')->references('productid')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocurrences');
    }
};
