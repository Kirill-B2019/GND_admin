@props(['items' => []])

@if (count($items))
    <nav class="mb-2" aria-label="Навигационная цепочка">
        <ol class="breadcrumb mb-0 small">
            @foreach ($items as $i => $crumb)
                @php
                    $isLast = $i === count($items) - 1;
                    $label = $crumb['label'] ?? '';
                    $url = $crumb['url'] ?? null;
                @endphp
                <li class="breadcrumb-item {{ $isLast ? 'active text-light' : '' }}" @if ($isLast) aria-current="page" @endif>
                    @if ($isLast || empty($url))
                        {{ $label }}
                    @else
                        <a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
