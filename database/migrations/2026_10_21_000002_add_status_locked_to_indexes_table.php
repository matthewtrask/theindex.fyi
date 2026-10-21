<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indexes', function (Blueprint $table) {
            $table->boolean('status_locked')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('indexes', function (Blueprint $table) {
            $table->dropColumn('status_locked');
        });
    }
};
