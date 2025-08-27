<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $fillable = ['name', 'color', 'description', 'owner_id', 'owner_type', 'is_public'];

    public function owner() {
        return $this->morphTo();
    }

    public function events() {
        return $this->hasMany(Event::class);
    }
}
