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
        Schema::table('ocurrences', function (Blueprint $table) {
            $table->boolean('resolvida')->default(false)->after('dateinsert');
            $table->boolean('interna')->default(false)->after('resolvida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocurrences', function (Blueprint $table) {
            $table->dropColumn(['resolvida', 'interna']);
        });
    }
};
