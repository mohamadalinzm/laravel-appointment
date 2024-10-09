<?php

namespace TestModels;

use Appointment\Models\Relations\ClientRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory,ClientRelationships;

    protected $table = 'clients';
    protected $fillable = ['name'];
}
