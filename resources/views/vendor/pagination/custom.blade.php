@if ($paginator->hasPages())

<nav class="pagination-wrapper">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())

        <span class="page-btn disabled">
            <i class="fa-solid fa-chevron-left"></i>
            Sebelumnya
        </span>

    @else

        <a class="page-btn"
           href="{{ $paginator->previousPageUrl() }}">
            <i class="fa-solid fa-chevron-left"></i>
            Sebelumnya
        </a>

    @endif


    {{-- Nomor halaman --}}
    <div class="page-numbers">

        @foreach ($elements as $element)

            @if (is_string($element))

                <span class="dots">{{ $element }}</span>

            @endif

            @if (is_array($element))

                @foreach ($element as $page => $url)

                    @if ($page == $paginator->currentPage())

                        <span class="page-number active">
                            {{ $page }}
                        </span>

                    @else

                        <a class="page-number"
                           href="{{ $url }}">
                            {{ $page }}
                        </a>

                    @endif

                @endforeach

            @endif

        @endforeach

    </div>


    {{-- Next --}}
    @if ($paginator->hasMorePages())

        <a class="page-btn"
           href="{{ $paginator->nextPageUrl() }}">
            Berikutnya
            <i class="fa-solid fa-chevron-right"></i>
        </a>

    @else

        <span class="page-btn disabled">
            Berikutnya
            <i class="fa-solid fa-chevron-right"></i>
        </span>

    @endif

</nav>

@endif
