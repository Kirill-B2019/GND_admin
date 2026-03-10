<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0 text-light">Пользователи</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Админ</a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Создать пользователя</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card bg-dark border-secondary">
                <div class="table-responsive table-mobile-wrap">
                    <table class="table table-dark table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td class="text-secondary">{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">{{ $user->role }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm">Изменить роль</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-dark border-secondary">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
