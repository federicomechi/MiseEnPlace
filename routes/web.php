<?php

use App\Http\Controllers\Admin\DevelopmentController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::middleware('workspace')->group(function (): void {
        Route::get('operativita/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
        Route::get('operativita/ingredients/create', [IngredientController::class, 'create'])->name('ingredients.create');
        Route::post('operativita/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::get('operativita/ingredients/{ingredient}/edit', [IngredientController::class, 'edit'])->name('ingredients.edit');
        Route::put('operativita/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
        Route::delete('operativita/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');
        Route::get('operativita/ingredients/fornitori', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('operativita/ingredients/fornitori', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('operativita/ingredients/fornitori/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('operativita/ingredients/fornitori/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
    Route::get('operativita/{section}', WorkspaceController::class)->middleware('workspace')->name('workspace');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::inertia('admin', 'Admin/Index')->name('admin');
    Route::inertia('admin/import', 'Admin/Placeholder', [
        'title' => 'Importazione dati',
        'description' => 'Qui verrà gestito il caricamento controllato degli export FileMaker e la sincronizzazione delle tabelle.',
    ])->name('admin.import');
    Route::inertia('admin/settings', 'Admin/Placeholder', [
        'title' => 'Impostazioni',
        'description' => 'Qui verranno raccolte le preferenze dell’applicazione e i valori condivisi del progetto.',
    ])->name('admin.settings');
    Route::get('admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('admin/development', [DevelopmentController::class, 'index'])->name('admin.development.index');
    Route::get('admin/development/create', [DevelopmentController::class, 'create'])->name('admin.development.create');
    Route::post('admin/development', [DevelopmentController::class, 'store'])->name('admin.development.store');
    Route::get('admin/development/{developmentEntry}/edit', [DevelopmentController::class, 'edit'])->name('admin.development.edit');
    Route::put('admin/development/{developmentEntry}', [DevelopmentController::class, 'update'])->name('admin.development.update');
    Route::delete('admin/development/{developmentEntry}', [DevelopmentController::class, 'destroy'])->name('admin.development.destroy');
    Route::get('admin/menus', [MenuController::class, 'index'])->name('admin.menus.index');
    Route::get('admin/menus/create', [MenuController::class, 'create'])->name('admin.menus.create');
    Route::post('admin/menus', [MenuController::class, 'store'])->name('admin.menus.store');
    Route::get('admin/menus/{menuItem}/edit', [MenuController::class, 'edit'])->name('admin.menus.edit');
    Route::put('admin/menus/{menuItem}', [MenuController::class, 'update'])->name('admin.menus.update');
    Route::delete('admin/menus/{menuItem}', [MenuController::class, 'destroy'])->name('admin.menus.destroy');
});

require __DIR__.'/settings.php';
