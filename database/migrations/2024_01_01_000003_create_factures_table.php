<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('numero')->unique(); // FAC-2024-0001
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('tva', 5, 2)->default(20.00);
            $table->decimal('montant_ttc', 10, 2);
            $table->string('statut')->default('emise'); // emise, payee, annulee
            $table->dateTime('date_emission');
            $table->dateTime('date_echeance')->nullable();
            $table->dateTime('date_paiement')->nullable();
            $table->string('methode_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
