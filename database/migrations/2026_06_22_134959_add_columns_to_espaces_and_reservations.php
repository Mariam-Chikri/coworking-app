<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================================
        // TABLE ESPACES
        // ============================================================
        
        Schema::table('espaces', function (Blueprint $table) {
            // 1. capacite_min
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

            // 10. nombre_bureaux (open_space_creatif seulement)
            if (!Schema::hasColumn('espaces', 'nombre_bureaux')) {
                $table->integer('nombre_bureaux')->unsigned()->nullable()->after('equipements');
            }
        });

        // ============================================================
        // TABLE RESERVATIONS
        // ============================================================
        
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                // numero_bureau (open_space_creatif)
                if (!Schema::hasColumn('reservations', 'numero_bureau')) {
                    $table->smallInteger('numero_bureau')->unsigned()->nullable()->after('nombre_personnes');
                }
            });
        }

        // ============================================================
        // Backfill : copier l'ancienne colonne "capacite" si elle existe
        // ============================================================
        if (Schema::hasColumn('espaces', 'capacite')) {
            DB::statement("
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
        // ============================================================
        // TABLE ESPACES - Suppression des colonnes
        // ============================================================
        Schema::table('espaces', function (Blueprint $table) {
            $columns = [
                'capacite_min',
                'capacite_max',
                'prix_journee',
                'prix_mois',
                'photo_principale',
                'adresse',
                'latitude',
                'longitude',
                'equipements',
                'nombre_bureaux',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('espaces', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // ============================================================
        // TABLE RESERVATIONS - Suppression des colonnes
        // ============================================================
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (Schema::hasColumn('reservations', 'numero_bureau')) {
                    $table->dropColumn('numero_bureau');
                }
            });
        }
    }
};