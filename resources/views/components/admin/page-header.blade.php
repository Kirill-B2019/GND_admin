@props(['title'])

<div {{ $attributes->merge(['class' => 'd-flex justify-content-between align-items-center flex-wrap gap-2']) }}>
    <h2 class="h5 mb-0 text-light">{{ $title }}</h2>
    @isset($actions)
        <div class="d-flex gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endisset
</div>
