<?php

namespace Nzm\Appointment\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Nzm\Appointment\Models\Appointment;

trait Clientable
{
    public function clientAppointments(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'clientable');
    }

    public function getBookedSlots(): Collection
    {
        return $this->clientAppointments()
            ->where('start_time', '>', now())
            ->get();
    }

    public function getUpComingBookedSlots(): Collection
    {
        return $this->clientAppointments()
            ->where('start_time', '>', now())
            ->get();
    }
}
