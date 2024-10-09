<?php

namespace Appointment\Models\Relations;

use Appointment\Models\Appointment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ClientRelationships
{

    public function clientAppointments()
    {
        return $this->morphMany(Appointment::class, 'clientable');
    }

}
