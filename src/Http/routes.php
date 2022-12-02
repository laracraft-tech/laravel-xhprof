<?php

use Illuminate\Support\Facades\Route;

// Requests entries...
Route::post('/spyglass-api/requests', 'RequestsController@index');
Route::get('/spyglass-api/requests/{spyglassEntryId}', 'RequestsController@show');

// Artisan Commands entries...
Route::post('/spyglass-api/commands', 'CommandsController@index');
Route::get('/spyglass-api/commands/{spyglassEntryId}', 'CommandsController@show');

// Scheduled Commands entries...
Route::post('/spyglass-api/schedule', 'ScheduleController@index');
Route::get('/spyglass-api/schedule/{spyglassEntryId}', 'ScheduleController@show');

// Monitored Tags...
Route::get('/spyglass-api/monitored-tags', 'MonitoredTagController@index');
Route::post('/spyglass-api/monitored-tags/', 'MonitoredTagController@store');
Route::post('/spyglass-api/monitored-tags/delete', 'MonitoredTagController@destroy');


// Toggle Recording...
Route::post('/spyglass-api/toggle-recording', 'RecordingController@toggle');

// Clear Entries...
Route::delete('/spyglass-api/entries', 'EntriesController@destroy');

Route::get('/{view?}', 'HomeController@index')->where('view', '(.*)')->name('spyglass');
