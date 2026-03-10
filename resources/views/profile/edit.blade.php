<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-light">Профиль</h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <div style="max-width: 32rem;">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <div style="max-width: 32rem;">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <div style="max-width: 32rem;">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
