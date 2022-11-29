<?php

use Illuminate\Support\Facades\Route;

Route::get('/{view?}', 'HomeController@index')->where('view', '(.*)')->name('spyglass');
