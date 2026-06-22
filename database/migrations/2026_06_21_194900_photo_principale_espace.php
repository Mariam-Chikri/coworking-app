<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : complète la table espaces avec toutes les colonnes modernes.
 * - capacite_min / capacite_max (remplace l'ancienne colonne 'capacite')
 * - prix_journee / prix_mois
 * - photo_principale (chemin vers l'image uploadée par l'admin)
 * - adresse / latitude / longitude
 * - equipements (JSON)
 *
 * Ordre impératif : prix_journee → prix_mois → photo_principale
 * pour éviter les erreurs de référence MySQL (after()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('espaces', function (Blueprint $table) {

            // 1. Capacité min / max
            if (!Schema::hasColumn('espaces', 'capacite_min')) {
                $table->integer('capacite_min')->default(1)->after('description');
            }
            if (!Schema::hasColumn('espaces', 'capacite_max')) {
                $table->integer('capacite_max')->default(1)->after('capacite_min');
            }

            // 2. Prix journée et mois (AVANT photo_principale pour respecter l'ordre after())
            if (!Schema::hasColumn('espaces', 'prix_journee')) {
                $table->decimal('prix_journee', 8, 2)->nullable()->after('prix_heure');
            }
            if (!Schema::hasColumn('espaces', 'prix_mois')) {
                $table->decimal('prix_mois', 8, 2)->nullable()->after('prix_journee');
            }

            // 3. Photo principale (doit venir APRÈS prix_mois)
            if (!Schema::hasColumn('espaces', 'photo_principale')) {
                $table->string('photo_principale')->nullable()->after('prix_mois');
            }

            // 4. Localisation
            if (!Schema::hasColumn('espaces', 'adresse')) {
                $table->string('adresse')->nullable()->after('photo_principale');
            }
            if (!Schema::hasColumn('espaces', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('adresse');
            }
            if (!Schema::hasColumn('espaces', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            // 5. Équipements (JSON : ['wifi', 'projecteur', ...])
            if (!Schema::hasColumn('espaces', 'equipements')) {
                $table->json('equipements')->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('espaces', function (Blueprint $table) {
            $cols = [
                'capacite_min', 'capacite_max',
                'prix_journee', 'prix_mois',
                'photo_principale',
                'adresse', 'latitude', 'longitude',
                'equipements',
            ];
            // Supprimer uniquement les colonnes qui existent
            foreach ($cols as $col) {
                if (Schema::hasColumn('espaces', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

