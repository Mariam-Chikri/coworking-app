<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter deleted_at à factures
        Schema::table('factures', function (Blueprint $table) {
            if (!Schema::hasColumn('factures', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Ajouter deleted_at à reservations
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            if (Schema::hasColumn('factures', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};