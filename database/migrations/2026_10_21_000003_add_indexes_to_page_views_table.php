<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->index('visited_at');
            $table->index('path');
            $table->index('referrer');
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropIndex(['visited_at']);
            $table->dropIndex(['path']);
            $table->dropIndex(['referrer']);
        });
    }
};
