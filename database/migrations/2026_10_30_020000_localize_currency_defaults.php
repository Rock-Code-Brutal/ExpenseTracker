<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->enum('default_currency', ['USD', 'CAD'])->default('USD')->change();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('currency', ['USD', 'CAD'])->default('USD')->change();
        });
        DB::table('settings')->whereNull('user_id')->where('default_currency', 'IDR')
            ->update(['default_currency' => 'USD']);
    }

    public function down(): void
    {
        // Preserve current user preferences on rollback.
    }
};
