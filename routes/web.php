<?php

use App\Livewire\Catalog;
use Illuminate\Support\Facades\Route;

Route::get('/', Catalog::class)->name('catalog');
