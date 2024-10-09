<?php

namespace Appointment\Models\Relations;

use Appointment\Models\Appointment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait AgentRelationships
{

    public function agentAppointments()
    {
        return $this->morphMany(Appointment::class, 'agentable');
    }

}
