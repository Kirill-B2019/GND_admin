@php
    $mobile = $mobile ?? false;
    $sections = config('admin_navigation', ['main' => [], 'instruction' => []]);
    $dismiss = $mobile ? 'data-bs-dismiss="offcanvas"' : '';
@endphp
@foreach ($sections['main'] ?? [] as $item)
    <li class="nav-item {{ $item['item_class'] ?? '' }}">
        <a
            class="nav-link text-light {{ request()->routeIs($item['pattern']) ? 'active bg-secondary bg-opacity-25 rounded' : '' }}"
            href="{{ route($item['route']) }}"
            {!! $dismiss !!}
        >{{ $item['label'] }}</a>
    </li>
@endforeach
@foreach ($sections['instruction'] ?? [] as $item)
    <li class="nav-item {{ $item['item_class'] ?? '' }}">
        <a
            class="nav-link text-light {{ request()->routeIs($item['pattern']) ? 'active bg-secondary bg-opacity-25 rounded' : '' }}"
            href="{{ route($item['route']) }}"
            {!! $dismiss !!}
        >{{ $item['label'] }}</a>
    </li>
@endforeach
