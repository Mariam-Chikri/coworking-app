<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ChatbotFaq extends Component
{
    public bool $ouvert = false;
    public string $question = '';
    public array $historique = [];

    private array $faq = [
        'reservation' => [
            'keywords' => ['réserver', 'reservation', 'book', 'réservation', 'disponible', 'available'],
            'fr' => "Pour réserver un espace : connectez-vous, allez dans 'Espaces', choisissez votre espace et cliquez sur 'Réserver'. Vous pouvez filtrer par type, capacité et date.",
            'en' => "To book a space: log in, go to 'Spaces', choose your space and click 'Book'. You can filter by type, capacity and date.",
        ],
        'prolongation' => [
            'keywords' => ['prolonger', 'extend', 'prolongation', 'extension', 'plus longtemps', 'longer'],
            'fr' => "Pour prolonger une réservation en cours, allez dans 'Mes Réservations', trouvez la réservation active et cliquez sur '⏰ Prolonger'. Vous pouvez ajouter de 1 à 8 heures, sous réserve de disponibilité.",
            'en' => "To extend an active reservation, go to 'My Reservations', find the active booking and click '⏰ Extend'. You can add 1 to 8 hours, subject to availability.",
        ],
        'annulation' => [
            'keywords' => ['annuler', 'cancel', 'annulation', 'remboursement', 'refund'],
            'fr' => "Vous pouvez annuler une réservation future depuis 'Mes Réservations'. Les réservations passées ou en cours ne peuvent pas être annulées.",
            'en' => "You can cancel a future reservation from 'My Reservations'. Past or ongoing reservations cannot be cancelled.",
        ],
        'facture' => [
            'keywords' => ['facture', 'invoice', 'payer', 'paiement', 'pay', 'payment', 'télécharger', 'download'],
            'fr' => "Vos factures sont disponibles dans 'Mes Réservations' ou 'Mes Factures'. Vous pouvez les télécharger en PDF.",
            'en' => "Your invoices are available in 'My Reservations' or 'My Invoices'. You can download them as PDF.",
        ],
        'tarif' => [
            'keywords' => ['prix', 'tarif', 'price', 'coût', 'cost', 'combien', 'how much', 'euro'],
            'fr' => "Les tarifs varient selon l'espace. Nos bureaux individuels démarrent à 8€/h, les salles de réunion à 25€/h et l'open space à 15€/h. Consultez la page 'Espaces' pour les tarifs exacts.",
            'en' => "Prices vary by space. Individual offices start at €8/h, meeting rooms at €25/h and open space at €15/h. Check the 'Spaces' page for exact rates.",
        ],
        'wifi' => [
            'keywords' => ['wifi', 'internet', 'connexion', 'connection', 'réseau', 'network'],
            'fr' => "Tous nos espaces sont équipés du WiFi haut débit (fibre). Le réseau est disponible 24h/7j.",
            'en' => "All our spaces are equipped with high-speed WiFi (fiber). The network is available 24/7.",
        ],
        'acces' => [
            'keywords' => ['accès', 'access', 'horaire', 'hours', 'ouvert', 'open', 'entrée', 'entry', 'badge'],
            'fr' => "Le coworking est accessible du lundi au vendredi de 8h à 20h, et le samedi de 9h à 17h. Un badge vous sera remis lors de votre première réservation.",
            'en' => "The coworking space is open Monday to Friday 8am-8pm, and Saturday 9am-5pm. A badge will be provided on your first reservation.",
        ],
        'liberationanticipee' => [
            'keywords' => ['libérer', 'liberation', 'early', 'partir', 'leave', 'tôt', 'avant'],
            'fr' => "Si vous libérez l'espace avant l'heure prévue, cliquez sur '🕊️ Libérer' dans vos réservations. Le prix sera recalculé selon la durée réelle.",
            'en' => "If you leave the space early, click '🕊️ Release' in your reservations. The price will be recalculated based on actual duration.",
        ],
    ];

    public function toggle()
    {
        $this->ouvert = !$this->ouvert;
        if ($this->ouvert && empty($this->historique)) {
            $locale = app()->getLocale();
            $msg = $locale === 'en'
                ? "Hello! I'm your CoWork assistant. I can help you with reservations, extensions, invoices, pricing, and more. What's your question?"
                : "Bonjour ! Je suis votre assistant CoWork. Je peux vous aider pour les réservations, prolongations, factures, tarifs et plus encore. Quelle est votre question ?";
            $this->historique[] = ['role' => 'bot', 'message' => $msg];
        }
    }

    public function envoyer()
    {
        if (!trim($this->question)) return;

        $this->historique[] = ['role' => 'user', 'message' => $this->question];
        $reponse = $this->trouverReponse($this->question);
        $this->historique[] = ['role' => 'bot', 'message' => $reponse];
        $this->question = '';
    }

    private function trouverReponse(string $question): string
    {
        $locale = app()->getLocale();
        $questionLower = mb_strtolower($question);

        foreach ($this->faq as $categorie => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (str_contains($questionLower, $keyword)) {
                    return $data[$locale] ?? $data['fr'];
                }
            }
        }

        return $locale === 'en'
            ? "I didn't quite understand your question. You can contact us at contact@coworking.fr or call +33 1 23 45 67 89."
            : "Je n'ai pas bien compris votre question. Vous pouvez nous contacter à contact@coworking.fr ou appeler le +33 1 23 45 67 89.";
    }

    public function render()
    {
        return view('livewire.chatbot-faq');
    }
}
