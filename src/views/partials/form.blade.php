{{ Form::open(array('action' => 'Fbf\LaravelSolarium\SearchController@results', 'method' => 'get', 'class' => 'search-form')) }}
	{{ Form::text('term', Input::get('term'), array('class' => 'search-term')) }}
	{{ Form::submit('Search', array('class' => 'search-submit')) }}
{{ Form::close() }}