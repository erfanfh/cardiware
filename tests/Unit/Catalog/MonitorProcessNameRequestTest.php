<?php

namespace Tests\Unit\Catalog;

use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MonitorProcessNameRequestTest extends TestCase
{
    public function test_monitor_process_name_survives_manifest_normalization(): void
    {
        $request = new UpdateProductRequest;
        $normalize = new \ReflectionMethod($request, 'normalizeManifestInput');

        $manifest = $normalize->invoke($request, [
            'requires_injection' => true,
            'inject_dll_path' => 'LiveEditor\\editor.dll',
            'monitor_process_name' => '  FIFA23.exe  ',
            'capabilities' => ['supports_injection' => true],
        ]);

        $this->assertTrue($manifest['requires_injection']);
        $this->assertSame('LiveEditor\\editor.dll', $manifest['inject_dll_path']);
        $this->assertSame('FIFA23.exe', $manifest['monitor_process_name']);
        $this->assertTrue($manifest['capabilities']['supports_injection']);
        $this->assertNull($normalize->invoke($request, [])['monitor_process_name']);
    }

    public function test_monitor_process_name_accepts_an_image_name_but_rejects_paths(): void
    {
        $request = new UpdateProductRequest;
        $rules = $request->rules()['sale_items.*.manifest.monitor_process_name'];

        foreach (['FIFA23.exe', null] as $name) {
            $this->assertTrue(Validator::make(['name' => $name], ['name' => $rules])->passes());
        }

        foreach (['games/FIFA23.exe', 'games\\FIFA23.exe', '.', '..'] as $name) {
            $this->assertTrue(Validator::make(['name' => $name], ['name' => $rules])->fails());
        }
    }
}
