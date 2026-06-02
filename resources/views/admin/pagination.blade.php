@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="opacity:.4;">← Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">← Prev</a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active"><span>{{ $page }}</span></span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Next →</a>
        @else
            <span style="opacity:.4;">Next →</span>
        @endif
    </div>
@endif
