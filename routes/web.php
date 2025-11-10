<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableRowController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\TableSettingController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Middleware\Ensure2faIfForced;
use App\Http\Controllers\Admin\TwoFactorSettingController;

// Redirection de la racine vers la page principale
Route::redirect('/', '/tables');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ici, on déclare toutes les routes nécessaires pour :
|  - l’authentification (login, register, 2FA…)
|  - la gestion des tableaux (TableController)
|  - la gestion des lignes (TableRowController)
|
*/

Route::middleware(['auth'])->group(function () {
    // 2FA – configuration et activation/désactivation
    Route::get('/2fa/setup', [TwoFactorAuthController::class, 'showEnableForm'])->name('2fa.setup');
    Route::post('/2fa/setup', [TwoFactorAuthController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
});

// Vérification du code 2FA (accessible sans le middleware “auth”,
// afin que l’utilisateur, après login, puisse entrer son code)
Route::get('/2fa/verify', [TwoFactorAuthController::class, 'showVerifyForm'])->name('2fa.verify');
Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verifyCode'])->name('2fa.verify.code');

// Routes d’auth (login / register / logout)
Route::get('/login', [AuthController::class, 'loginIndex'])
    ->name('auth.login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('auth.login.submit');

Route::delete('/logout', [AuthController::class, 'logout'])
    ->name('auth.logout');
Route::aliasMiddleware('guest', 'logout');

Route::get('/register', [AuthController::class, 'registerIndex'])
    ->name('auth.register')
    ->middleware('auth');

Route::post('/register', [AuthController::class, 'register']);

Route::get('/tables', [TableController::class, 'index'])
    ->name('table.index')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| Routes protégées par le middleware "auth"
|--------------------------------------------------------------------------
| Toutes les routes à l’intérieur de ce groupe nécessitent que l’utilisateur
| soit connecté. Les Policies et Gate y gèrent ensuite les permissions.
*/
Route::middleware(['auth'])->group(function () {

    //
    // --- GESTION DES TABLEAUX (plans d’action) via TableController ---
    //

    // 1. LISTE DES TABLEAUX
    Route::get('/tables', [TableController::class, 'index'])
        ->name('table.index');

    // 2. FORMULAIRE DE CRÉATION + ENREGISTREMENT
    Route::get('/tables/create', [TableController::class, 'create'])
        ->name('table.create');
    Route::post('/tables', [TableController::class, 'store'])
        ->name('table.store');

    // 3. AFFICHER UN TABLEAU (via route-model binding : {table})
    Route::get('/tables/{table}', [TableController::class, 'show'])
        ->name('table.show');

    // 4. FORMULAIRE D’ÉDITION + MISE À JOUR
    Route::get('/tables/{table}/edit', [TableController::class, 'edit'])
        ->name('table.edit');
    Route::put('/tables/{table}', [TableController::class, 'update'])
        ->name('table.update');

    // 5. SUPPRESSION D’UN TABLEAU
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])
        ->name('table.destroy');

    // 6. PERSONNALISATION “COLONNES” D’UN TABLEAU
    Route::post('/tables/{table}/add-column', [
        TableController::class,
        'addColumn'
    ])->name('table.addColumn');

    Route::put(
        '/tables/{table}/update-column/{index}',
        [TableController::class, 'updateColumn']
    )->name('table.updateColumn');

    Route::delete(
        '/tables/{table}/delete-column/{index}',
        [TableController::class, 'deleteColumn']
    )->name('table.deleteColumn');

    //
    // --- GESTION DES LIGNES (actions) via TableRowController ---
    //     (on isole la logique CRUD des lignes dans un contrôleur dédié)
    //

    // 7. FORMULAIRE D’AJOUT D’UNE LIGNE DANS UN TABLEAU
    Route::get('/tables/{table}/rows/create', [
        TableRowController::class,
        'create'
    ])->name('rows.create');

    // 8. STOCKAGE D’UNE NOUVELLE LIGNE
    Route::post('/tables/{table}/rows', [
        TableRowController::class,
        'store'
    ])->name('rows.store');

    // 9. FORMULAIRE D’ÉDITION D’UNE LIGNE EXISTANTE
    Route::get('/rows/{row}/edit', [
        TableRowController::class,
        'edit'
    ])->name('rows.edit');

    // 10. MISE À JOUR D’UNE LIGNE
    Route::put('/rows/{row}', [
        TableRowController::class,
        'update'
    ])->name('rows.update');

    // 11. SUPPRESSION D’UNE LIGNE
    Route::delete('/rows/{row}', [
        TableRowController::class,
        'destroy'
    ])->name('rows.destroy');


    Route::post('/tables/{table}/update-data', [
        TableController::class,
        'updateData'
    ])->name('table.updateData');
});

Route::middleware('auth')->group(function () {

    // 1. liste des projets (uniquement ceux dont on est chef, ou tous si admin)
    Route::get('projects', [AdminUserController::class, 'index'])
        ->name('projects.index')
        ->can('viewAny', \App\Models\Table::class);

    // 2. retirer un utilisateur d’un projet
    Route::delete('projects/{table}/users/{user}', [AdminUserController::class, 'destroy'])
        ->name('projects.users.destroy')
        ->can('manageUsers', 'table');
    Route::delete('/users/{user}', [AdminUserController::class, 'delete'])
        ->name('admin.users.delete');
    Route::post('/users/{user}', [AdminUserController::class, 'promote'])
        ->name('admin.users.promote');
    Route::delete(
        'projects/{table}/pm/{user}',
        [AdminUserController::class, 'removePm']
    )->name('projects.pm.destroy');
    Route::get('/users/compte', [AuthController::class, 'compte'])
        ->name('auth.compte');
    Route::post('/auth/password/update', [AuthController::class, 'updatePassword'])
        ->name('auth.password.update');

    // Ajouter un CP
    Route::post(
        'projects/{table}/pm',
        [AdminUserController::class, 'addPm']
    )->name('projects.pm.store');
});

Route::prefix('tables/{table}/settings')->middleware('auth')->group(function () {
    Route::get('/', [TableSettingController::class, 'edit'])->name('table.settings.edit');
    Route::put('/', [TableSettingController::class, 'update'])->name('table.settings.update');
});

Route::post('/refresh-performance/{table}', [TableController::class, 'refreshPerformance'])
    ->name('table.refreshPerformance')
    ->middleware(['auth']);



Route::get('/tables/{table}/performance', [PerformanceController::class, 'show'])
    ->name('table.performance')
    ->middleware('auth');

Route::get('/users/search', [AuthController::class, 'search'])->middleware('auth')->name('users.search');

Route::patch('/tables/{table}/rename', [TableController::class, 'rename'])
    ->middleware(['auth', 'can:rename,table'])
    ->name('table.rename');

Route::delete('/tables/{table}', [TableController::class, 'destroyTable'])
    ->middleware(['auth', 'can:delete,table'])
    ->name('table.destroy');

Route::middleware(['auth'])->group(function () {
    // Formulaire d’invitation (chef de projet seulement)
    Route::get('tables/{table}/invite', [InviteController::class, 'create'])
        ->name('tables.invite')
        ->can('invite', 'table');

    Route::post('tables/{table}/invite', [InviteController::class, 'store'])
        ->name('tables.invite.store')
        ->can('invite', 'table');
});

// Formulaire de réinitialisation
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])
    ->name('password.update');


Route::middleware(['auth', Ensure2faIfForced::class])
    ->group(function () {

        Route::get('settings/2fa', [TwoFactorSettingController::class, 'edit'])
            ->name('settings.2fa');

        // Soumettre le switch
        Route::post('settings/2fa', [TwoFactorSettingController::class, 'update'])
            ->name('settings.2fa.update');
    });
