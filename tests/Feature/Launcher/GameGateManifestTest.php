<?php

namespace Tests\Feature\Launcher;

use App\Application\Catalog\Actions\BuildPurchaseAccessManifestAction;
use App\Application\Catalog\Actions\ProductInformation\SyncProductSaleItemManifestAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameGateManifestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate this contract from the historical MySQL-only migration chain.
        config(['database.default' => 'game_gate_test', 'database.connections.game_gate_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);

        Schema::create('product_sale_item_manifests', function (Blueprint $table): void {
            $table->id();
            foreach ((new ProductSaleItemManifest)->getFillable() as $column) {
                if (! in_array($column, ['content_gate', 'protected_gate_files'], true)) {
                    $table->text($column)->nullable();
                }
            }
            $table->timestamps();
        });
        Schema::create('product_sale_item_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_sale_item_id');
            $table->integer('sort_order')->default(0);
        });
        Schema::create('launcher_allowed_dlls', function (Blueprint $table): void {
            $table->id();
            $table->string('sha256');
        });

        (require database_path('migrations/2026_10_15_000000_add_game_gate_columns_to_manifests.php'))->up();
    }

    protected function tearDown(): void
    {
        DB::purge('game_gate_test');
        parent::tearDown();
    }

    public static function gatePaths(): array
    {
        return [
            'textarea' => ["  Data\\layout.toc\r\n\r\nData\\layout.toc\n Data\\other.bin  "],
            'array' => [[' Data\\layout.toc ', '', 'Data\\layout.toc', "bad\r\nDIRECTIVE", 42, 'Data\\other.bin']],
        ];
    }

    #[DataProvider('gatePaths')]
    public function test_gate_settings_survive_request_storage_and_launcher_output(mixed $paths): void
    {
        $request = new UpdateProductRequest;
        $normalize = new \ReflectionMethod($request, 'normalizeManifestInput');
        $input = $normalize->invoke($request, [
            'content_gate' => '1',
            'content_key_id' => 'fifa23-test-01',
            'content_key_value' => base64_encode(str_repeat('k', 32)),
            'main_executable_path' => 'Launcher.exe',
            'monitor_process_name' => 'FIFA23.exe',
            'protected_gate_files' => $paths,
        ]);

        $saleItem = (new ProductSaleItem)->forceFill(['id' => 101, 'version' => '1.0.0']);
        Model::withoutEvents(fn () => app(SyncProductSaleItemManifestAction::class)->execute($saleItem, $input));
        $manifest = $saleItem->manifest()->firstOrFail();

        $this->assertTrue($manifest->content_gate);
        $this->assertSame(['Data\\layout.toc', 'Data\\other.bin'], $manifest->protected_gate_files);

        $saleItem->setRelation('manifest', $manifest);
        $access = new PurchaseAccess;
        $access->setRelation('product', new Product(['title' => 'FIFA', 'slug' => 'fifa']));
        $access->setRelation('productSaleItem', $saleItem);
        $launch = app(BuildPurchaseAccessManifestAction::class)->execute($access)['launch'];

        $this->assertTrue($launch['content_gate']);
        $this->assertSame('fifa23-test-01', $launch['content_key_id']);
        $this->assertSame('FIFA23.exe', $launch['monitor_process_name']);
        $this->assertSame(['Data\\layout.toc', 'Data\\other.bin'], $launch['content_protection']['gate_files']);
        $this->assertArrayNotHasKey('content_key_value', $launch);

        // Clearing the optional list and disabling the gate must also persist.
        Model::withoutEvents(fn () => app(SyncProductSaleItemManifestAction::class)->execute($saleItem, [
            ...$input, 'content_gate' => '0', 'protected_gate_files' => '',
        ]));
        $manifest->refresh();
        $launch = app(BuildPurchaseAccessManifestAction::class)->execute($access)['launch'];
        $this->assertFalse($manifest->content_gate);
        $this->assertFalse($launch['content_gate']);
        $this->assertSame([], $launch['content_protection']['gate_files']);
    }

    public function test_migration_is_repeatable_and_existing_manifests_default_to_gate_off(): void
    {
        $id = DB::table('product_sale_item_manifests')->insertGetId(['product_sale_item_id' => 101]);
        $migration = require database_path('migrations/2026_10_15_000000_add_game_gate_columns_to_manifests.php');
        $migration->up();
        $manifest = ProductSaleItemManifest::findOrFail($id);
        $this->assertFalse($manifest->content_gate);
        $this->assertNull($manifest->protected_gate_files);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('product_sale_item_manifests', 'content_gate'));
        $this->assertFalse(Schema::hasColumn('product_sale_item_manifests', 'protected_gate_files'));
    }

    public function test_gate_requires_key_and_process_and_rejects_either_sider_flag(): void
    {
        foreach ([false, true] as $published) {
            $request = new UpdateProductRequest(['sale_items' => [[
                'launcher_enabled' => $published,
                'manifest' => ['content_gate' => true],
            ]]]);
            $validator = Validator::make($request->all(), []);
            $request->withValidator($validator);
            $errors = $validator->errors();
            $this->assertTrue($errors->has('sale_items.0.manifest.content_key_id'));
            $this->assertTrue($errors->has('sale_items.0.manifest.monitor_process_name'));
        }

        foreach ([['requires_sider' => true], ['capabilities' => ['supports_sider' => true]]] as $sider) {
            $request = new UpdateProductRequest(['sale_items' => [['manifest' => [
                ...$sider, 'content_gate' => true,
                'content_key_id' => 'fifa23-test-01', 'monitor_process_name' => 'FIFA23.exe',
            ]]]]);
            $validator = Validator::make($request->all(), []);
            $request->withValidator($validator);
            $this->assertTrue($validator->errors()->has('sale_items.0.manifest.content_gate'));
        }
    }

    public function test_new_gate_key_is_allowed_without_sider_and_still_requires_vault_grant(): void
    {
        $request = new UpdateProductRequest;
        $session = app('session')->driver();
        $session->put('content_key_vault_unlocked_until', now()->addMinutes(5)->timestamp);
        $request->setLaravelSession($session);
        $validateKey = new \ReflectionMethod($request, 'validateContentKey');
        $manifest = [
            'content_gate' => true, 'content_key_id' => 'fifa23-test-01',
            'content_key_value' => base64_encode(str_repeat('k', 32)),
        ];
        $validator = Validator::make([], []);
        $validateKey->invoke($request, $validator, $manifest, 'sale_items.0', null, false);
        $this->assertSame([], $validator->errors()->all());

        $request->merge(['sale_items' => [[
            'launcher_enabled' => true, 'launcher_title' => 'FIFA23', 'version' => '1.0.0',
            'manifest' => [
                ...$manifest, 'main_executable_path' => 'Launcher.exe',
                'monitor_process_name' => 'FIFA23.exe', 'install_folder_name' => 'FIFA23',
            ],
            'files' => [[
                'file_name' => 'fifa.7z', 'url' => 'https://downloads.example.test/fifa.7z',
                'size_bytes' => 1024, 'sha256' => str_repeat('a', 64),
            ]],
        ]]]);
        $validator = Validator::make($request->all(), []);
        $request->withValidator($validator);
        $this->assertSame([], $validator->errors()->all());

        $session->forget('content_key_vault_unlocked_until');
        $validator = Validator::make([], []);
        $validateKey->invoke($request, $validator, $manifest, 'sale_items.0', null, false);
        $this->assertTrue($validator->errors()->has('sale_items.0.manifest.content_key_value'));

        $validator = Validator::make([], []);
        $validateKey->invoke($request, $validator, [...$manifest, 'content_gate' => false], 'sale_items.0', null, false);
        $this->assertTrue($validator->errors()->has('sale_items.0.manifest.content_key_id'));
    }

    public function test_admin_component_submits_gate_fields_in_both_product_modes(): void
    {
        foreach (['simple', 'variable'] as $mode) {
            $html = Blade::render(
                '<x-admin.catalog.product-sale-item-manifest-fields :mode="$mode" :manifest="$manifest" />',
                ['mode' => $mode, 'manifest' => ['content_gate' => true, 'protected_gate_files' => ['Data\\layout.toc']]],
            );
            $this->assertStringContainsString('[manifest][content_gate]', $html);
            $this->assertStringContainsString('[manifest][protected_gate_files]', $html);
            $this->assertStringContainsString('x-model="'.($mode === 'simple' ? 'manifest' : 'item.manifest').'.content_gate"', $html);
        }
    }
}
