<?php

use Illuminate\Http\Request;

Route::get('/lists', 'ListsController@index');
Route::get('/lists/{id}', 'ListsController@show');