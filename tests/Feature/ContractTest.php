<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Тесты контрактов: список, просмотр, сохранение ABI на ноде, синхронизация с нодой, работа с методами (call/send).
 */
class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gnd.node_url' => 'http://test-gnd-node']);
        config(['services.gnd.admin_secret' => 'test-secret']);
    }

    /** Список контрактов доступен только админу. */
    public function test_contracts_index_requires_admin(): void
    {
        $response = $this->get(route('admin.contracts.index'));
        $response->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $this->actingAs($user);
        $response = $this->get(route('admin.contracts.index'));
        $response->assertStatus(403);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);
        $response = $this->get(route('admin.contracts.index'));
        $response->assertStatus(200);
        $response->assertSee('Контракты');
        $response->assertSee('Синхронизировать с нодой');
        $response->assertSee('Обновить ABI на ноде (все)');
    }

    /** Просмотр контракта: страница загружается, отображаются данные. */
    public function test_contract_show_loads(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $contract = Contract::create([
            'name' => 'Test Contract',
            'description' => 'Desc',
            'status' => Contract::STATUS_DRAFT,
            'source_code' => 'pragma solidity ^0.8.0; contract C {}',
        ]);

        $this->actingAs($admin);
        Http::fake([
            '*api/v1/admin/wallets*' => Http::response(['success' => true, 'data' => ['wallets' => []]], 200),
        ]);

        $response = $this->get(route('admin.contracts.show', $contract));
        $response->assertStatus(200);
        $response->assertSee('Test Contract');
        $response->assertSee('Информация');
    }

    /** Обновление ABI на ноде: при успешном PATCH — редирект с сообщением. */
    public function test_update_abi_on_node_success(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $abi = [['type' => 'function', 'name' => 'transfer', 'inputs' => []]];
        $contract = Contract::create([
            'name' => 'Token',
            'address' => 'GNDct123',
            'status' => Contract::STATUS_DEPLOYED,
            'abi_json' => json_encode($abi),
            'source_code' => 'contract C {}',
        ]);

        Http::fake([
            '*api/v1/admin/contracts/GNDct123/abi' => Http::response([
                'success' => true,
                'data' => ['address' => 'GNDct123', 'message' => 'ABI обновлён'],
            ], 200),
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.update-abi-on-node', $contract), ['_token' => csrf_token()]);
        $response->assertRedirect(route('admin.contracts.show', $contract));
        $response->assertSessionHas('status');
        $this->assertStringContainsString('ABI', session('status'));
    }

    /** Обновление ABI на ноде: контракт без адреса — ошибка. */
    public function test_update_abi_on_node_requires_address(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $contract = Contract::create([
            'name' => 'No Address',
            'address' => null,
            'status' => Contract::STATUS_DRAFT,
            'abi_json' => json_encode([['type' => 'function']]),
            'source_code' => 'contract C {}',
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.update-abi-on-node', $contract), ['_token' => csrf_token()]);
        $response->assertRedirect(route('admin.contracts.show', $contract));
        $response->assertSessionHasErrors('abi');
    }

    /** Обновление ABI на ноде: при пустом abi_json — ошибка «Нет локального ABI». */
    public function test_update_abi_on_node_requires_abi(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $contract = Contract::create([
            'name' => 'Deployed No ABI',
            'address' => 'GNDct456',
            'status' => Contract::STATUS_DEPLOYED,
            'abi_json' => null,
            'source_code' => 'contract C {}',
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.update-abi-on-node', $contract), ['_token' => csrf_token()]);
        $response->assertRedirect(route('admin.contracts.show', $contract));
        $response->assertSessionHasErrors('abi');
        $this->assertStringContainsString('Нет локального ABI', session('errors')->first('abi'));
    }

    /** Массовое обновление ABI на ноде: для контрактов с адресом и ABI вызывается PATCH. */
    public function test_sync_abi_to_node_calls_patch(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $abi = [['type' => 'function', 'name' => 'mint']];
        $abiJson = json_encode($abi);
        Contract::create([
            'name' => 'C1',
            'address' => 'GNDct1',
            'status' => Contract::STATUS_DEPLOYED,
            'abi_json' => $abiJson,
            'source_code' => 'contract C {}',
        ]);
        Contract::create([
            'name' => 'C2',
            'address' => 'GNDct2',
            'status' => Contract::STATUS_DEPLOYED,
            'abi_json' => $abiJson,
            'source_code' => 'contract C {}',
        ]);

        Http::fake([
            '*api/v1/admin/contracts/*/abi' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.sync-abi-to-node'), ['_token' => csrf_token()]);
        $response->assertRedirect(route('admin.contracts.index'));
        $response->assertSessionHas('status');
        $this->assertStringContainsString('2', session('status'));
    }

    /** Компиляция: при успешном ответе ноды — байткод и ABI сохраняются в карточке. */
    public function test_compile_saves_bytecode_and_abi(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $contract = Contract::create([
            'name' => 'CompileMe',
            'status' => Contract::STATUS_DRAFT,
            'source_code' => 'pragma solidity ^0.8.0; contract C { function f() public pure returns (uint) { return 1; } }',
            'standard' => 'GND-st1',
        ]);

        Http::fake([
            '*api/v1/contract/compile*' => Http::response([
                'success' => true,
                'data' => [
                    'bytecode' => '0x6080604052348015600f57600080fd5b506004361060285760003560e01c8063b3eebf7914602d575b600080fd5b60336047565b60405190815260200160405180910390f35b60006001905090565b609e8060596000396000f3fe608060405260043610603f5760003560e01c8063b3eebf79146044575b600080fd5b604a6004803603810190604a91906065565b605c565b005b8060008190555050565b6000819050919050565b606081603d565b8114606a57600080fd5b50565b600081359050607c816076565b92915050565b60006020828403121560955760946071565b5b600060a184828501606e565b9150509291505056fea2646970667358221220',
                    'abi' => [['type' => 'function', 'name' => 'f', 'inputs' => [], 'outputs' => [['type' => 'uint256']], 'stateMutability' => 'pure']],
                ],
            ], 200),
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.compile', $contract), ['_token' => csrf_token()]);
        $response->assertRedirect(route('admin.contracts.show', $contract));
        $response->assertSessionHas('status');

        $contract->refresh();
        $this->assertNotEmpty($contract->bytecode_hex);
        $this->assertNotEmpty($contract->abi_json);
        $abi = is_string($contract->abi_json) ? json_decode($contract->abi_json, true) : $contract->abi_json;
        $this->assertIsArray($abi);
        $this->assertNotEmpty($abi);
    }

    /** Деплой: при успешном ответе ноды — адрес и статус сохраняются. */
    public function test_deploy_saves_address_and_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $contract = Contract::create([
            'name' => 'DeployMe',
            'status' => Contract::STATUS_ANALYZED,
            'source_code' => 'contract C {}',
            'bytecode_hex' => '0x6080604052348015600f57600080fd5b506004361060285760003560e01c8063b3eebf7914602d575b600080fd5b60336047565b60405190815260200160405180910390f35b60006001905090565b609e8060596000396000f3fe',
            'abi_json' => json_encode([['type' => 'function', 'name' => 'f']]),
            'standard' => 'GND-st1',
        ]);

        Http::fake([
            '*api/v1/contract/GNDctDeployed123*' => Http::response([
                'success' => true,
                'data' => ['ID' => 99, 'address' => 'GNDctDeployed123'],
            ], 200),
            '*api/v1/contract*' => Http::response([
                'success' => true,
                'data' => ['address' => 'GNDctDeployed123', 'contract_id' => 99],
            ], 200),
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.deploy', $contract), [
            '_token' => csrf_token(),
            'deployer_address' => 'GNDwallet1',
        ]);
        $response->assertRedirect(route('admin.contracts.show', $contract));
        $response->assertSessionHas('status');

        $contract->refresh();
        $this->assertSame('GNDctDeployed123', $contract->address);
        $this->assertSame(Contract::STATUS_DEPLOYED, $contract->status);
        $this->assertSame(99, $contract->node_contract_id);
    }

    /** Синхронизация с нодой (node_contract_id): GET contract возвращает id, сохраняется в БД. */
    public function test_sync_from_node_updates_node_contract_id(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $contract = Contract::create([
            'name' => 'Synced',
            'address' => 'GNDctSync',
            'status' => Contract::STATUS_DEPLOYED,
            'node_contract_id' => null,
            'source_code' => 'contract C {}',
        ]);

        Http::fake([
            '*api/v1/contract/GNDctSync*' => Http::response([
                'success' => true,
                'data' => ['ID' => 42, 'address' => 'GNDctSync'],
            ], 200),
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.contracts.sync-from-node'), ['_token' => csrf_token()]);
        $response->assertRedirect(route('admin.contracts.index'));
        $response->assertSessionHas('status');

        $contract->refresh();
        $this->assertSame(42, $contract->node_contract_id);
    }
}
