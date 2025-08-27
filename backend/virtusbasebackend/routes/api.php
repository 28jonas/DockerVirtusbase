<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FamilyController;
use App\Http\Controllers\API\CalendarController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ShoppingListController;
use App\Http\Controllers\API\ShoppingItemController;

// Alle routes zijn beveiligd met auth:sanctum middleware
Route::middleware('auth:sanctum')->group(function () {

    // Family Routes
    Route::apiResource('families', FamilyController::class);
    Route::post('families/{family}/members', [FamilyController::class, 'addMember']);
    Route::delete('families/{family}/members/{member}', [FamilyController::class, 'removeMember']);

    // Calendar Routes
    Route::apiResource('calendars', CalendarController::class);

    // Event Routes
    Route::apiResource('events', EventController::class)->except(['store']);
    Route::post('calendars/{calendar}/events', [EventController::class, 'store']);
    Route::get('events', [EventController::class, 'index']);

    // Shopping List Routes
    Route::apiResource('shopping-lists', ShoppingListController::class);
    Route::post('shopping-lists/{shoppingList}/share', [ShoppingListController::class, 'share']);
    Route::delete('shopping-lists/{shoppingList}/share/{user}', [ShoppingListController::class, 'unshare']);

    // Shopping Item Routes
    Route::apiResource('shopping-lists.items', ShoppingItemController::class)
        ->except(['show'])
        ->shallow();
    Route::patch('shopping-items/{shoppingItem}/toggle', [ShoppingItemController::class, 'toggleComplete']);
});
