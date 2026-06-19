<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('espace_id')->constrained()->onDelete('cascade');
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->dateTime('fin_initiale')->nullable(); // fin avant prolongation
            $table->dateTime('liberation_anticipee')->nullable(); // si libéré avant fin
            $table->string('statut')->default('en_attente'); // en_attente, confirmee, terminee, annulee, prolongee
            $table->decimal('prix_total', 10, 2)->default(0);
            $table->decimal('prix_prolongation', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('numero')->unique(); // REZ-2024-0001
            $table->integer('nombre_personnes')->default(1);
            $table->boolean('notif_envoyee')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
