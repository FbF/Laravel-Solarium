@extends('layouts.master')

{{-- Assumes you've done @include ('laravel-solarium::partials.form') in the layout --}}

@section('title')
    <?php $input = Input::get('term'); ?>
	@if (!empty($input))
		Search Results for {{ Input::get('term') }}
	@else
		Please enter a search term
	@endif
@stop

@section('content')
	@include('laravel-solarium::partials.results')
@stop