<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('espaces', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('nom_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->integer('capacite')->default(1);
            $table->decimal('prix_heure', 8, 2)->default(0);
            $table->boolean('reservable')->default(true);
            $table->string('type')->default('bureau'); // bureau, salle, open_space, non_reservable
            $table->json('photos')->nullable();
            $table->string('couleur')->default('#667eea');
            $table->string('icone')->default('building');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espaces');
    }
};
