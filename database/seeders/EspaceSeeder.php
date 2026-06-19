<?php

namespace Database\Seeders;

use App\Models\Espace;
use Illuminate\Database\Seeder;

class EspaceSeeder extends Seeder
{
    public function run(): void
    {
        $espaces = [
            // Réservables
            [
                'nom' => 'Bureau Soleil', 'nom_en' => 'Sunshine Office',
                'description' => 'Bureau individuel lumineux avec vue sur la cour intérieure. Idéal pour le travail concentré.',
                'description_en' => 'Bright individual office with courtyard view. Perfect for focused work.',
                'capacite' => 1, 'prix_heure' => 8.00, 'type' => 'bureau',
                'couleur' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'icone' => 'user', 'reservable' => true,
            ],
            [
                'nom' => 'Bureau Zen', 'nom_en' => 'Zen Office',
                'description' => 'Bureau privé calme et épuré, avec mobilier ergonomique et éclairage naturel.',
                'description_en' => 'Quiet private office with ergonomic furniture and natural lighting.',
                'capacite' => 2, 'prix_heure' => 15.00, 'type' => 'bureau',
                'couleur' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                'icone' => 'leaf', 'reservable' => true,
            ],
            [
                'nom' => 'Salle Horizon', 'nom_en' => 'Horizon Room',
                'description' => 'Grande salle de réunion équipée d\'un écran 75", tableau blanc et vidéoconférence.',
                'description_en' => 'Large meeting room with 75" screen, whiteboard and videoconferencing.',
                'capacite' => 12, 'prix_heure' => 45.00, 'type' => 'salle',
                'couleur' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'icone' => 'users', 'reservable' => true,
            ],
            [
                'nom' => 'Salle Étoile', 'nom_en' => 'Star Room',
                'description' => 'Salle de réunion medium pour brainstorming. Tableau interactif, café et snacks.',
                'description_en' => 'Medium meeting room for brainstorming. Interactive whiteboard, coffee and snacks.',
                'capacite' => 6, 'prix_heure' => 25.00, 'type' => 'salle',
                'couleur' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'icone' => 'star', 'reservable' => true,
            ],
            [
                'nom' => 'Open Space Creative', 'nom_en' => 'Creative Open Space',
                'description' => 'Grand espace ouvert et collaboratif avec 20 postes de travail. Ambiance startup.',
                'description_en' => 'Large open collaborative space with 20 workstations. Startup atmosphere.',
                'capacite' => 20, 'prix_heure' => 15.00, 'type' => 'open_space',
                'couleur' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                'icone' => 'network-wired', 'reservable' => true,
            ],
            [
                'nom' => 'Pod Phonique', 'nom_en' => 'Phonebooth Pod',
                'description' => 'Cabine individuelle insonorisée pour appels téléphoniques et visio-conférences privées.',
                'description_en' => 'Soundproof individual pod for phone calls and private video conferences.',
                'capacite' => 1, 'prix_heure' => 5.00, 'type' => 'bureau',
                'couleur' => 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
                'icone' => 'phone', 'reservable' => true,
            ],
            // Non réservables
            [
                'nom' => 'Coin Café', 'nom_en' => 'Coffee Corner',
                'description' => 'Espace convivial pour vos pauses avec café, thé et collations à disposition.',
                'description_en' => 'Cozy break area with coffee, tea and snacks available all day.',
                'capacite' => 10, 'prix_heure' => 0, 'type' => 'non_reservable',
                'couleur' => 'linear-gradient(135deg, #f6d365 0%, #fda085 100%)',
                'icone' => 'coffee', 'reservable' => false,
            ],
            [
                'nom' => 'Terrasse', 'nom_en' => 'Terrace',
                'description' => 'Terrasse ensoleillée pour travailler en plein air. Disponible de mai à septembre.',
                'description_en' => 'Sunny terrace for outdoor work. Available May to September.',
                'capacite' => 15, 'prix_heure' => 0, 'type' => 'non_reservable',
                'couleur' => 'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)',
                'icone' => 'sun', 'reservable' => false,
            ],
            [
                'nom' => 'Salon Détente', 'nom_en' => 'Relaxation Lounge',
                'description' => 'Lounge confortable pour vous ressourcer entre deux sessions de travail.',
                'description_en' => 'Comfortable lounge to recharge between work sessions.',
                'capacite' => 8, 'prix_heure' => 0, 'type' => 'non_reservable',
                'couleur' => 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
                'icone' => 'couch', 'reservable' => false,
            ],
        ];

        foreach ($espaces as $data) {
            Espace::create($data);
        }
    }
}
