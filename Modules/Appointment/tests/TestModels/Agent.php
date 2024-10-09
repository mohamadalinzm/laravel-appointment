<?php

namespace TestModels;

use Appointment\Models\Relations\AgentRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory,AgentRelationships;

    protected $table = 'agents';
    protected $fillable = ['name'];
}
