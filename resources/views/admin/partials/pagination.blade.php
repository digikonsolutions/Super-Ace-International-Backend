@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        <div class="pager__info">
            @if (method_exists($paginator, 'total'))
                Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
            @else
                Page {{ $paginator->currentPage() }}
            @endif
        </div>
        <ul class="pager__list">
            @if ($paginator->onFirstPage())
                <li class="is-disabled"><span><svg class="i i-sm"><use href="#i-left"/></svg></span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"><svg class="i i-sm"><use href="#i-left"/></svg></a></li>
            @endif

            @isset($elements)
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="is-disabled"><span>{{ $element }}</span></li>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="is-active"><span>{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endisset

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"><svg class="i i-sm"><use href="#i-right"/></svg></a></li>
            @else
                <li class="is-disabled"><span><svg class="i i-sm"><use href="#i-right"/></svg></span></li>
            @endif
        </ul>
    </nav>
@endif
