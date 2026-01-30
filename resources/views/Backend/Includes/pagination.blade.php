@if ($paginator->hasPages())
    <section id="paginator">
        <div class="container">
            <div class="row">
                <div class="mx-auto d-block">
                    <div class="col-xs-12 col-sm-12 col-md-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="lang-dt_paginate">
                            <ul class="pagination">
                                <li class="paginate_button page-item previous @if ($paginator->onFirstPage()) disabled @endif" id="lang-dt_previous">
                                    <a href="@if ($paginator->onFirstPage()) # @else {{ $paginator->previousPageUrl() }} @endif" aria-controls="lang-dt" data-dt-idx="0" tabindex="0" class="page-link">Anterior</a>
                                </li>
                                @foreach(range(1, $paginator->lastPage()) as $i)
                                    @if($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2)
                                        <li class="paginate_button page-item {{ ($paginator->currentPage() == $i) ? ' active' : 'page-item' }}">
                                            <a href="{{ $paginator->appends(request()->query())->url($i) }}" aria-controls="lang-dt" data-dt-idx="{{ $i }}" tabindex="0" class="page-link">{{ $i }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                <li class="paginate_button page-item next @if (!$paginator->hasMorePages()) disabled @endif" id="lang-dt_next">
                                    <a href="@if ($paginator->hasMorePages()) {{ $paginator->nextPageUrl() }} @else # @endif" aria-controls="lang-dt" data-dt-idx="3" tabindex="0" class="page-link">Próximo</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif