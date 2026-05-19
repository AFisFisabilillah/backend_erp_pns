<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/login", [AuthenticationController::class, "login"]);

Route::middleware('auth:sanctum')->group(function () {
    Route::delete("/logout", [AuthenticationController::class, "logout"]);

    Route::delete("/pegawai/delete", [PegawaiController::class, "destroy"]);
    Route::get("/pegawai/trash", [PegawaiController::class, "trash"]);
    Route::patch("/pegawai/restore", [PegawaiController::class, "restore"]);
    Route::delete("/pegawai/forceDelete", [PegawaiController::class, "forceDelete"]);

    Route::post("/pegawai", [PegawaiController::class, "store"]);
    Route::get("/pegawai", [PegawaiController::class, "index"]);
    Route::post("/pegawai/{pegawai}", [PegawaiController::class, "update"]);


});
