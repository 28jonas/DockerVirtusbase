<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['name'];

    public function members() {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function owners() {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->wherePivot('role', 'owner');
    }

    // Polymorfe relaties
    public function ownedCalendars() {
        return $this->morphMany(Calendar::class, 'owner');
    }

    public function ownedEvents() {
        return $this->morphMany(Event::class, 'owner');
    }

    public function ownedShoppingLists() {
        return $this->morphMany(ShoppingList::class, 'owner');
    }
}
