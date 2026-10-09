@if($paginator->hasPages())
<nav class="pagination" aria-label="Pagination">
  @if($paginator->previousPageUrl())<a href="{{ $paginator->previousPageUrl() }}">Previous</a>@endif
  <span>Showing {{ $paginator->count() }} records</span>
  @if($paginator->nextPageUrl())<a href="{{ $paginator->nextPageUrl() }}">Next</a>@endif
</nav>
@endif
