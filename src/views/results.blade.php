@extends('layouts.master')

{{-- Assumes you've done @include ('laravel-solarium::partials.form') in the layout --}}

@section('title')
	@if (!empty(Input::get('term')))
		Search Results for {{ Input::get('term') }}
	@else
		Please enter a search term
	@endif
@stop

@section('content')
	@include('laravel-solarium::partials.results')
@stop