<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    // RELATIES
    public function profile() {
        return $this->hasOne(Profile::class);
    }

    public function families() {
        return $this->belongsToMany(Family::class)
            ->withPivot('role')
            ->withTimestamps();
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

    // Helper methods
    public function getRoleInFamily(Family $family): ?string {
        return $this->families()->where('family_id', $family->id)->first()?->pivot->role;
    }

    public function hasPermissionInFamily(Family $family, string $permission): bool {
        $role = $this->getRoleInFamily($family);
        return \App\Enums\FamilyRolePermission::can($role, $permission);
    }
}
