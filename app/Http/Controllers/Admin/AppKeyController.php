<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GndAdminApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Управление API-ключами через админское API ноды GND (X-Admin-Token).
 * Список и создание/отзыв идут через /api/v1/admin/keys.
 */
class AppKeyController extends Controller
{
    public function index(): View
    {
        $error = null;
        $keys = [];
        if (GndAdminApi::isConfigured()) {
            $result = GndAdminApi::listKeys();
            $keys = $result['keys'];
            $error = $result['error'];
        }
        return view('admin.keys.index', [
            'keys' => collect($keys),
            'gnd_error' => $error,
            'gnd_configured' => GndAdminApi::isConfigured(),
        ]);
    }

    public function create(): View
    {
        return view('admin.keys.create', [
            'gnd_configured' => GndAdminApi::isConfigured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.keys.create')
                ->withInput()
                ->withErrors(['gnd' => 'Задайте GND_NODE_URL и GND_ADMIN_SECRET в .env']);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date',
        ]);
        $expiresAt = $request->expires_at
            ? date(\DateTimeInterface::RFC3339, strtotime($request->expires_at))
            : null;
        $result = GndAdminApi::createKey(
            $request->name,
            $request->input('permissions', []) ?: [],
            $expiresAt
        );
        if ($result['error'] !== null) {
            return redirect()->route('admin.keys.create')
                ->withInput()
                ->withErrors(['gnd' => 'Ошибка ноды GND: ' . $result['error']]);
        }
        $data = $result['data'];
        return redirect()->route('admin.keys.created')
            ->with('new_key_plain', $data['key'] ?? null)
            ->with('new_key_name', $data['name'] ?? $request->name)
            ->with('status', 'Ключ создан. Сохраните его — он больше не будет показан.');
    }

    /**
     * Страница «Ключ создан» — показ ключа один раз (из сессии).
     */
    public function created(): View
    {
        return view('admin.keys.show', [
            'new_key_plain' => session('new_key_plain'),
            'new_key_name' => session('new_key_name'),
        ]);
    }

    /**
     * Отзыв ключа на ноде (POST /api/v1/admin/keys/:id/revoke).
     */
    public function revoke(int $id): RedirectResponse
    {
        if (! GndAdminApi::isConfigured()) {
            return redirect()->route('admin.keys.index')
                ->withErrors(['gnd' => 'Задайте GND_ADMIN_SECRET в .env']);
        }
        $result = GndAdminApi::revokeKey($id);
        if ($result['error'] !== null) {
            return redirect()->route('admin.keys.index')
                ->withErrors(['gnd' => 'Ошибка отзыва: ' . $result['error']]);
        }
        return redirect()->route('admin.keys.index')->with('status', 'Ключ отозван.');
    }
}
