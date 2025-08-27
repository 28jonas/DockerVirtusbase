<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListSharing extends Model
{
    protected $table = 'shopping_list_sharings';
    protected $fillable = ['shopping_list_id', 'user_id', 'permission_level'];

    public function shoppingList() {
        return $this->belongsTo(ShoppingList::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
