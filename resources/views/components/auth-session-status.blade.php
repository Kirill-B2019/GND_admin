@props(['status'])

@if ($status)
    <div class="visually-hidden" data-flash="success">{{ $status }}</div>
@endif
