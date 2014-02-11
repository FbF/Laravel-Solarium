@if (!empty($results))
	<p>Viewing results {{ $paginator->getFrom() }} - {{ $paginator->getTo()}} of {{ $paginator->getTotal() }} found for '{{ Input::get('term') }}'</p>
	<div class="search-results">
		@foreach ($results as $document)
			<div class="search-result">
				<a href="{{$document['url']}}" class="search-result-title">{{ $document['title'] }}</a>
				<p class="search-result-content">
				     <?php $highlighted = $highlighting->getResult($document->id) ?>
                     @if ($highlighted)
                         @foreach ($highlighted as $field => $highlight)
                             {{ implode(' (...) ', $highlight); }}
                         @endforeach
                     @endif
				</p>
			</div>
		@endforeach
		{{ $paginator->appends(array('search' => Input::get('term')))->links() }}
	</div>
@else
    <?php $input = Input::get('term'); ?>
	@if (!empty($input))
		<p>No Search Results found for {{ Input::get('term') }}</p>
	@else
		<p>Please enter a search term</p>
	@endif
@endif