<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD аккаунтов. У аккаунта может быть несколько кошельков (связь через wallets.account_id).
 */
class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::withCount('wallets')->orderBy('name')->paginate(15);

        return view('admin.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('admin.accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        Account::create([
            'name' => $request->name,
            'description' => $request->filled('description') ? $request->description : null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.accounts.index')->with('status', 'Аккаунт создан.');
    }

    public function show(Account $account): View
    {
        $account->load('wallets');

        return view('admin.accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        return view('admin.accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $account->update([
            'name' => $request->name,
            'description' => $request->filled('description') ? $request->description : null,
        ]);

        return redirect()->route('admin.accounts.show', $account)->with('status', 'Аккаунт обновлён.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $account->wallets()->update(['account_id' => null]);
        $account->delete();

        return redirect()->route('admin.accounts.index')->with('status', 'Аккаунт удалён. Кошельки отвязаны.');
    }
}
