<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Joke extends Model
{
    protected $fillable = ['external_id', 'joke_text'];
}
