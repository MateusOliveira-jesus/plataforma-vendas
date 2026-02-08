<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function (Request $request) {
    return response()->json([
        'message' => 'Bem-vindo à API do Projeto Filament!',
        'status' => 'success',
    ]);
});
