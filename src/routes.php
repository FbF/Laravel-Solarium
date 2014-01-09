<?php

Route::get(Config::get('laravel-solarium::uri'), 'Fbf\LaravelSolarium\SearchController@results');