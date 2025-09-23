@if ($paginator->hasPages())
    <nav>
        <ul class="pagination mb-0">
            {{-- Previous --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <button type="button" class="page-link"
                        wire:click="previousPage"
                        wire:loading.attr="disabled"
                        aria-label="Previous">
                    &laquo;
                </button>
            </li>

            {{-- Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                            <button type="button" class="page-link"
                                    wire:click="gotoPage({{ $page }})"
                                    wire:loading.attr="disabled">
                                {{ $page }}
                            </button>
                        </li>
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <button type="button" class="page-link"
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        aria-label="Next">
                    &raquo;
                </button>
            </li>
        </ul>
    </nav>
@endif
