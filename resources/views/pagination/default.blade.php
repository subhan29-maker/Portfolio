{{--
    Pagination for the dashboard.

    Rendered as a compact bar rather than the framework default so it fits the
    design system: a summary on the left, a window of numbered links on the
    right, and the current page announced to assistive technology. The links
    keep every active filter, because the paginator is built with
    withQueryString() in the controller.
--}}
@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Pagination">
        <p class="admin-pagination__summary">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>

        <ul class="admin-pagination__list">
            @foreach ($paginator->linkCollection() as $link)
                <li>
                    @if ($link['active'])
                        <span class="admin-pagination__link" aria-current="page">{!! $link['label'] !!}</span>
                    @elseif (filled($link['url']))
                        <a class="admin-pagination__link" href="{{ $link['url'] }}">{!! $link['label'] !!}</a>
                    @else
                        <span class="admin-pagination__span">{!! $link['label'] !!}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
@endif
