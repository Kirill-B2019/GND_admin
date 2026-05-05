@props([
    'type' => 'info',
    'title' => null,
])

@php
    $classes = match ($type) {
        'success' => 'alert-success border-success',
        'danger' => 'alert-danger border-danger',
        'warning' => 'alert-warning border-warning',
        default => 'alert-info border-info',
    };
    $live = $type === 'danger' ? 'assertive' : 'polite';
@endphp

<div {{ $attributes->merge(['class' => 'alert ' . $classes . ' border mb-3']) }} role="alert" aria-live="{{ $live }}">
    @if ($title)
        <div class="fw-semibold mb-1">{{ $title }}</div>
    @endif
    <div class="mb-0">{{ $slot }}</div>
</div>
