{{ Form::open() }}
    {{ Form::text('search', $search) }}
    {{ Form::submit('search') }}
{{ Form::close() }}
@if ( ! empty($result))
    <p> Viewing results {{ $pagination->getFrom() }} - {{ $pagination->getTo()}} of {{ $pagination->getTotal() }} found for '{{$search}}'</p>
    <div class="search-results">
        @foreach ($result as $document)
            <div class="search-result">
                <a href="{{$document['url']}}" class="search-result-title">{{$document['title']}}</a>
                <p class="search-result-content">{{$document['content']}}</p>
            </div>
        @endforeach
        {{ $pagination->appends(array('search' => $search))->links() }}
    </div>
@else
    @if ( ! empty($search) )
        <p>No Search Results found for {{$search}}</p>
    @endif
@endif