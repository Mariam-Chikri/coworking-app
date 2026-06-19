<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->default('fr')->after('email');
            $table->boolean('is_admin')->default(false)->after('locale');
            $table->string('telephone')->nullable()->after('is_admin');
            $table->string('entreprise')->nullable()->after('telephone');
            $table->string('avatar')->nullable()->after('entreprise');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'is_admin', 'telephone', 'entreprise', 'avatar']);
        });
    }
};
