@if ($paginator->hasPages())
    <div class="pagination" style="display:flex; justify-content:center; gap:12px; align-items:center; font-size:14px; padding:10px 0;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn ghost sm" style="opacity:0.5; cursor:not-allowed">السابق</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn ghost sm">السابق</a>
        @endif

        {{-- Pagination Elements --}}
        <div style="display:flex; gap:4px; align-items:center;">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span style="padding:4px 8px; color:var(--ink-3); font-weight:bold;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn sm" style="background:var(--accent); color:#fff; pointer-events:none; min-width:32px; padding:0 8px; justify-content:center;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn ghost sm" style="color:var(--ink-2); min-width:32px; padding:0 8px; justify-content:center;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn ghost sm">التالي</a>
        @else
            <span class="btn ghost sm" style="opacity:0.5; cursor:not-allowed">التالي</span>
        @endif
    </div>
@endif
