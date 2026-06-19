<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('espace_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->tinyInteger('note')->unsigned(); // 1-5
            $table->string('titre')->nullable();
            $table->text('commentaire')->nullable();
            $table->boolean('valide')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
