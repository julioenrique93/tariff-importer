<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExcelImporterService;

class ProductServiceTest extends TestCase
{
    public function test_import_returns_string()
    {
        $service = new ExcelImporterService();

        $result = $service->import('fake.xlsx', 1);

        // Solo verificamos que devuelve un string
        $this->assertIsString($result);
    }

    public function test_import_with_invalid_provider_returns_string()
    {
        $service = new ExcelImporterService();

        $result = $service->import('fake.xlsx', 999);

        $this->assertIsString($result);
    }
}
