@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label text-light']) }}>
    {{ $value ?? $slot }}
</label>
