<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la table existe
        if (!Schema::hasTable('espaces')) {
            return;
        }

        Schema::table('espaces', function (Blueprint $table) {
            // 1. capacite_min (remplace la colonne "capacite")
            if (!Schema::hasColumn('espaces', 'capacite_min')) {
                $table->integer('capacite_min')->default(1)->after('description');
            }

            // 2. capacite_max
            if (!Schema::hasColumn('espaces', 'capacite_max')) {
                $table->integer('capacite_max')->default(1)->after('capacite_min');
            }

            // 3. prix_journee
            if (!Schema::hasColumn('espaces', 'prix_journee')) {
                $table->decimal('prix_journee', 8, 2)->nullable()->after('prix_heure');
            }

            // 4. prix_mois
            if (!Schema::hasColumn('espaces', 'prix_mois')) {
                $table->decimal('prix_mois', 8, 2)->nullable()->after('prix_journee');
            }

            // 5. photo_principale
            if (!Schema::hasColumn('espaces', 'photo_principale')) {
                $table->string('photo_principale', 255)->nullable()->after('prix_mois');
            }

            // 6. adresse
            if (!Schema::hasColumn('espaces', 'adresse')) {
                $table->string('adresse', 255)->nullable()->after('photo_principale');
            }

            // 7. latitude
            if (!Schema::hasColumn('espaces', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('adresse');
            }

            // 8. longitude
            if (!Schema::hasColumn('espaces', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            // 9. equipements (JSON)
            if (!Schema::hasColumn('espaces', 'equipements')) {
                $table->json('equipements')->nullable()->after('longitude');
            }
        });

        // ============================================================
        // MIGRATION DES DONNÉES EXISTANTES
        // ============================================================

        // Initialiser capacite_min/max depuis l'ancienne colonne "capacite" si elle existe
        if (Schema::hasColumn('espaces', 'capacite')) {
            // Mettre à jour les enregistrements existants
            \DB::statement("
                UPDATE `espaces`
                SET `capacite_min` = `capacite`,
                    `capacite_max` = `capacite`
                WHERE `capacite_min` = 1
                  AND `capacite_max` = 1
                  AND `capacite` > 1
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('espaces', function (Blueprint $table) {
            $columns = [
                'equipements',
                'longitude',
                'latitude',
                'adresse',
                'photo_principale',
                'prix_mois',
                'prix_journee',
                'capacite_max',
                'capacite_min',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('espaces', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};