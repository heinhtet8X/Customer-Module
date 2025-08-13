<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomersController;


Route::resource('customers', CustomersController::class)->middleware('auth')->names('customers');
