<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_views')
            ->where('path', 'like', '/reset-password/%')
            ->orWhere('path', 'like', '/email/verify/%')
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
