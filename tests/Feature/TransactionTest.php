<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gnd.node_url' => 'http://test-node']);
        config(['services.gnd.admin_secret' => 'test-secret']);
    }

    /** Админ видит список транзакций и форму создания. */
    public function test_transactions_index_and_create_require_admin(): void
    {
        Http::fake([
            '*/api/v1/transactions' => Http::response(['success' => true, 'data' => []], 200),
            '*/api/v1/transactions/list*' => Http::response(['success' => true, 'data' => ['transactions' => []]], 200),
            '*/api/v1/admin/wallets*' => Http::response([
                'success' => true,
                'data' => [
                    'wallets' => [
                        ['address' => 'GNDaddr1', 'name' => 'Wallet 1', 'role' => null],
                        ['address' => 'GNDaddr2', 'name' => 'Wallet 2', 'role' => null],
                    ],
                    'metadata' => [
                        ['logo_url' => '/logo.png', 'contract_address' => 'GNDct1'],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($user);

        $response = $this->get(route('admin.transactions.index'));
        $response->assertStatus(200);

        $response = $this->get(route('admin.transactions.create'));
        $response->assertStatus(200);
        $response->assertSee('Новый перевод');
        $response->assertSee('from_address');
        $response->assertSee('to_address');
    }

    /** В ответе кошельков нет private_key; metadata передаётся. */
    public function test_list_wallets_strips_private_key_and_includes_metadata(): void
    {
        Http::fake([
            '*/api/v1/admin/wallets*' => Http::response([
                'success' => true,
                'data' => [
                    'wallets' => [
                        ['address' => 'GNDx', 'name' => 'X', 'private_key' => 'secret', 'privateKey' => 'secret2'],
                    ],
                    'metadata' => [
                        ['logo_url' => '/logo.png', 'contract_address' => 'GNDct1'],
                    ],
                ],
            ], 200),
        ]);

        $result = \App\Services\GndAdminApi::listWallets(null);
        $this->assertNull($result['error']);
        $this->assertCount(1, $result['wallets']);
        $this->assertArrayNotHasKey('private_key', $result['wallets'][0]);
        $this->assertArrayNotHasKey('privateKey', $result['wallets'][0]);
        $this->assertCount(1, $result['metadata']);
        $this->assertSame('/logo.png', $result['metadata'][0]['logo_url']);
        $this->assertSame('GNDct1', $result['metadata'][0]['contract_address']);
    }

    /** Валидация store: отправитель и получатель не совпадают, сумма — число. */
    public function test_transaction_store_validation(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($user);

        $response = $this->post(route('admin.transactions.store'), [
            'from_address' => 'GNDa',
            'to_address' => 'GNDa',
            'asset_type' => 'token',
            'token_address' => 'GNDct1',
            'amount' => '100',
        ]);
        $response->assertSessionHasErrors('to_address');

        $response = $this->post(route('admin.transactions.store'), [
            'from_address' => 'GNDa',
            'to_address' => 'GNDb',
            'asset_type' => 'token',
            'token_address' => 'GNDct1',
            'amount' => 'not-a-number',
        ]);
        $response->assertSessionHasErrors('amount');
    }
}
