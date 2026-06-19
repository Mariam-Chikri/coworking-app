<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id || $user->is_admin;
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id || $user->is_admin;
    }
}
