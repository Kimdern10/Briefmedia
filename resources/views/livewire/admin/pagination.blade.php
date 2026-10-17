@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <ul class="pagination">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true"><span class="page-link"><i class="bi bi-arrow-left"></i></span></li>
            @else
                <li class="page-item"><button type="button" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" aria-label="Previous"><i class="bi bi-arrow-left"></i></button></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item"><button type="button" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" aria-label="Next"><i class="bi bi-arrow-right"></i></button></li>
            @else
                <li class="page-item disabled" aria-disabled="true"><span class="page-link"><i class="bi bi-arrow-right"></i></span></li>
            @endif
        </ul>
        <p class="small">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</p>
    </nav>
@endif
