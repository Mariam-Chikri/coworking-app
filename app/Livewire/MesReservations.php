<?php

namespace App\Livewire;

use App\Models\Reservation;
use Livewire\Component;
use Livewire\WithPagination;

class MesReservations extends Component
{
    use WithPagination;

    public string $filtreStatut = '';
    public ?int $reservationAnnulerID = null;
    public ?int $reservationProlongerID = null;
    public int $heuresProlongation = 1;
    public string $erreurProlongation = '';

    public function mount(): void
    {
        // Terminaison initiale au chargement
        Reservation::terminateExpiredReservations();
    }

    public function render()
    {
        // ✅ Terminer les réservations expirées à CHAQUE rendu (y compris les polls Livewire)
        // Nécessaire car le middleware TerminateReservations ne s'exécute pas sur les requêtes XHR Livewire
        Reservation::terminateExpiredReservations();

        $query = auth()->user()->reservations()
            ->with(['espace', 'facture', 'avis'])
            ->latest();

        if ($this->filtreStatut) {
            $query->where('statut', $this->filtreStatut);
        }

        return view('livewire.mes-reservations', [
            'reservations' => $query->paginate(10),
        ]);
    }

    /**
     * Ouvre le modal de confirmation d'annulation
     */
    public function confirmerAnnulation(int $id): void
    {
        $this->reservationAnnulerID = $id;
    }

    /**
     * Annule la réservation (uniquement si pas encore commencée)
     */
    public function annuler(): void
    {
        $reservation = Reservation::findOrFail($this->reservationAnnulerID);
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        if ($reservation->debut->isPast()) {
            $this->dispatch('toast', message: 'Impossible d\'annuler une réservation passée.', type: 'error');
            return;
        }

        $reservation->update(['statut' => 'annulee']);
        $this->reservationAnnulerID = null;
        $this->dispatch('toast', message: 'Réservation annulée.', type: 'success');
    }

    /**
     * Ouvre le modal de prolongation
     * Condition : 5 minutes de grâce après expiration UNIQUEMENT
     */
    public function ouvrirProlongation(int $id): void
    {
        $reservation = Reservation::find($id);

        if (!$reservation || $reservation->user_id !== auth()->id()) {
            $this->dispatch('toast', message: 'Réservation introuvable.', type: 'error');
            return;
        }

        if (!$reservation->canBeProlonged()) {
            $this->dispatch('toast', message: 'La prolongation n\'est plus possible (délai de grâce dépassé).', type: 'error');
            return;
        }

        $this->reservationProlongerID = $id;
        $this->heuresProlongation = 1;
        $this->erreurProlongation = '';
    }

    /**
     * Prolonge la réservation
     * Vérifie la disponibilité de l'espace avant de confirmer
     */
    public function prolonger(): void
    {
        $reservation = Reservation::findOrFail($this->reservationProlongerID);
        if ($reservation->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            if (!$reservation->canBeProlonged()) {
                $this->erreurProlongation = 'La prolongation n\'est plus possible (délai de grâce de 5 minutes dépassé).';
                return;
            }

            // La méthode prolonger() vérifie déjà la disponibilité de l'espace
            $reservation->prolonger($this->heuresProlongation);
            $this->reservationProlongerID = null;
            $this->erreurProlongation = '';
            $this->dispatch('toast', message: 'Réservation prolongée avec succès !', type: 'success');

        } catch (\Exception $e) {
            $this->erreurProlongation = $e->getMessage();
        }
    }

    /**
     * Ferme le modal de prolongation
     */
    public function fermerProlongation(): void
    {
        $this->reservationProlongerID = null;
        $this->erreurProlongation = '';
    }

    /**
     * Libère l'espace anticipativement
     * Visible UNIQUEMENT pendant la période de réservation (début <= now <= fin)
     * Disparaît une fois la période terminée
     */
    public function libererAnticipement(int $id): void
    {
        try {
            $reservation = Reservation::findOrFail($id);
            if ($reservation->user_id !== auth()->id()) {
                abort(403);
            }

            if (!$reservation->canBeReleasedEarly()) {
                $this->dispatch('toast', message: 'La libération anticipée n\'est pas possible.', type: 'error');
                return;
            }

            $reservation->libererAnticipement();
            $this->dispatch('toast', message: 'Espace libéré avec succès !', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }
}

