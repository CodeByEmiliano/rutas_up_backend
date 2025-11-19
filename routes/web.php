<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\ChoferController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\RecuperarContrasenaController;

Route::post('/api/solicitar-recuperacion', [RecuperarContrasenaController::class, 'solicitar']);
Route::get('/api/recuperar-password', [RecuperarContrasenaController::class, 'vistaRecuperar']);
Route::post('/api/actualizar-password', [RecuperarContrasenaController::class, 'actualizarPassword'])->name('actualizar-password');

Route::get('/', function () {
    return view('welcome');
});


Route::get('/map', function () {
    return view('map');
});

Route::get('/vehiculo-gestion', function () {
    return view('VehiculoGestion');
});

Route::get('/route-planner', function () {
    return view('route_planner');
});
// autenticacion

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/registerChofer', [AuthController::class, 'registerChofer']);
Route::post('/api/login', [AuthController::class, 'login']);


// marcas 
Route::get('/api/marcas', [MarcaController::class, 'index']);
Route::post('/api/marcas/store', [MarcaController::class, 'store']);
Route::put('/api/marcas/update/{id}', [MarcaController::class, 'update']);
Route::delete('/api/marcas/delete/{id}', [MarcaController::class, 'delete']);

// modelos
Route::post('/api/modelos/store', [ModeloController::class, 'store']);
Route::get('/api/modelos/marca/{marca_id}', [ModeloController::class, 'getModelosByMarca']);



// vehículos web.php
Route::prefix('api/vehiculos')->group(function () {
    Route::get('/', [VehiculoController::class, 'index']);
    Route::post('/store', [VehiculoController::class, 'store']);
    Route::get('{id}', [VehiculoController::class, 'show']);
    Route::put('/update/{id}', [VehiculoController::class, 'update']);
    Route::delete('/delete/{id}', [VehiculoController::class, 'destroy']);
});

// choferes
Route::get('/api/choferes', [ChoferController::class, 'index']);
Route::get('/api/choferes/usuarios', [ChoferController::class, 'getChoferes']);
Route::post('/api/choferes/store', [ChoferController::class, 'store']);
Route::put('/api/choferes/update/{id}', [ChoferController::class, 'update']);
Route::delete('/api/choferes/delete/{id}', [ChoferController::class, 'destroy']);

// rutas
Route::get('/api/rutas', [RutaController::class, 'index']);
Route::post('/api/rutas/store', [RutaController::class, 'store']);
Route::put('/api/rutas/update/{id}', [RutaController::class, 'update']);
Route::delete('/api/rutas/delete/{id}', [RutaController::class, 'destroy']);

// asignaciones

Route::get('/api/asignaciones', [AsignacionController::class, 'index']);
Route::post('/api/asignaciones/store', [AsignacionController::class, 'store']);
Route::put('/api/asignaciones/update/{id}', [AsignacionController::class, 'update']);
Route::delete('/api/asignaciones/delete/{id}', [AsignacionController::class, 'destroy']);
Route::get('/api/asignaciones/vehiculos', [AsignacionController::class, 'getVehiculos']);
