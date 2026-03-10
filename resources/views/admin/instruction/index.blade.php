<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-light">
            {{ $title ?? 'Инструкции для администратора' }}
        </h2>
        @if($subtitle ?? null)
            <p class="text-secondary small mb-0 mt-1">{{ $subtitle }}</p>
        @endif
    </x-slot>

    <div class="py-2">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body instruction-content">
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        .instruction-content h1 { font-size: 1.5rem; margin-top: 1rem; margin-bottom: 0.75rem; color: #f8f9fa; }
        .instruction-content h1:first-child { margin-top: 0; }
        .instruction-content h2 { font-size: 1.25rem; margin-top: 1.25rem; margin-bottom: 0.5rem; color: #e9ecef; }
        .instruction-content h3 { font-size: 1.1rem; margin-top: 1rem; margin-bottom: 0.5rem; color: #dee2e6; }
        .instruction-content h4 { font-size: 1rem; margin-top: 0.75rem; margin-bottom: 0.4rem; }
        .instruction-content p { margin-bottom: 0.75rem; color: #adb5bd; }
        .instruction-content ul, .instruction-content ol { margin-bottom: 0.75rem; padding-left: 1.5rem; color: #adb5bd; }
        .instruction-content li { margin-bottom: 0.25rem; }
        .instruction-content code { background: #212529; color: #6ea8fe; padding: 0.15rem 0.4rem; border-radius: 0.25rem; font-size: 0.9em; }
        .instruction-content pre { background: #212529; border: 1px solid #495057; border-radius: 0.375rem; padding: 1rem; overflow-x: auto; margin-bottom: 0.75rem; }
        .instruction-content pre code { background: none; padding: 0; color: #e9ecef; }
        .instruction-content table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; }
        .instruction-content th, .instruction-content td { border: 1px solid #495057; padding: 0.5rem 0.75rem; text-align: left; color: #adb5bd; }
        .instruction-content th { background: #212529; color: #dee2e6; }
        .instruction-content hr { border-color: #495057; margin: 1.25rem 0; }
        .instruction-content strong { color: #dee2e6; }
        .instruction-content a { color: #6ea8fe; text-decoration: none; }
        .instruction-content a:hover { text-decoration: underline; }
    </style>
    @endpush
</x-app-layout>
