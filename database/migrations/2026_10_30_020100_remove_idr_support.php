<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('transactions')->where('currency', 'IDR')->exists()) {
            throw new RuntimeException('Resolve existing IDR transactions before removing IDR support.');
        }
        DB::table('settings')->where('default_currency', 'IDR')->update(['default_currency' => 'USD']);
        Schema::table('settings', function (Blueprint $table) {
            $table->enum('default_currency', ['USD', 'CAD'])->default('USD')->change();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('currency', ['USD', 'CAD'])->default('USD')->change();
        });
    }

    public function down(): void
    {
        // Keep the localized currency constraints.
    }
};
