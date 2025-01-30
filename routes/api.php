<?php

use Illuminate\Support\Facades\Route;
use Marshmallow\NovaTotalsFooter\Http\Controllers\CalculateController;

Route::get('/calculate/{resource}', CalculateController::class);
