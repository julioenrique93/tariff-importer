<?php
namespace App\Services;

use App\Models\ProviderFormat;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelRowsImport;
use App\Models\PriceTier;
use App\Models\Product;
use App\Services\ProductUnitService;
use App\Services\ProductService;


class ExcelImporterService
{
    /**
     * Import products and price tiers from Excel file.
     *
     * @param mixed $file Excel file uploaded
     * @param int $providerId Provider identifier
     * @return string
     * @throws \Exception
     */
    public function import(mixed $file, int $providerId):string
    {
        try {
            $format = ProviderFormat::byProvider($providerId);
            $message = 'incorrect document';

            if (empty($format)) {
                return $message;
            }

            DB::beginTransaction();

            $import = new ExcelRowsImport;
            Excel::import($import, $file);
            $rows = $import->getRows();
            $productMap = ProductService::getReferenceMap($providerId);
            $newProducts = [];
            $unitName = strtolower($format->unit_name);
            $unit = null;
            $priceName = strtolower($format->price_name);
            $columnsMap = $format->columnsMap();
            $isCorrectFile = true;
            $headers = $rows->first()->keys()->toArray();

            foreach ($headers as $header) {
                $lowerHeader = strtolower($header);

                if (isset($columnsMap[$lowerHeader])) {
                    continue;
                }

                if (str_contains($lowerHeader, $priceName)) {
                    $parts = explode(' ', $lowerHeader);
                    $unit = strtolower(end($parts));
                    $priceName = $parts[0];
                } else {
                    $isCorrectFile = false;

                    break;
                }
            }


            if (!$isCorrectFile) {
                return $message;
            }

            $priceTiersDeleted = [];
            $productMapDB = Product::getMapByProvider($providerId);
            $productsIds = [];
            $productMap = [];

            foreach ($productMapDB as $product) {
                $productId = $product['id'];
                $productsIds[] = $productId;
                $productMap[$product['reference']] = $productId;
            }

            $productUnitService = new ProductUnitService;
            $productUnitDb = ProductUnit::getProducts($productsIds);
            $productUnitsMap = [];

            foreach ($productUnitDb as $productUnit) {
                $productUnitId = $productUnit['id'];
                $productUnitKey = $productUnitService::buildKey(
                    $productUnit['product_id'], $productUnit['unit']
                );
                $productUnitsMap[$productUnitKey] = $productUnitId;
            }

            $newProducts = [];
            $newProductsUnit = [];
            $newPriceTiers = [];

            foreach ($rows as $row) {
                $reference = $row[$format->reference_name] ?? null;
                $strench = $row[$format->stretch_name] ?? null;
                $price = $row[$priceName] ?? null;
                $unitValue = $unitName ? (
                    strtolower($row[$format->unit_name]) ?? null
                ): $unit;
                $range = null;
                $newPriceTier = [];

                if (!empty($strench)) {
                    $range = $format->parseQuantityRange($strench);
                }
                if (isset($productMap[$reference])) {
                    $productId = $productMap[$reference];
                    $productUnitKey = $productUnitService::buildKey(
                        $productId, $unitValue
                    );

                    if (isset($productUnitsMap[$productUnitKey])) {
                        $productUnitId = $productUnitsMap[$productUnitKey];

                        if (!isset($priceTiersDeleted[$productUnitKey])) {
                            PriceTier::deleteByProducts($productUnitId);
                            $priceTiersDeleted[$productUnitKey] =
                                $productUnitId;
                        }

                        $newPriceTier['product_unit_id'] = $productUnitId;
                    } else {
                        $productUnitsMap[$productUnitKey] = true;
                        $newProductsUnit[] = [
                            'product_id' => $productId,
                            'unit' => $unitValue,
                        ];
                    }
                } else {
                    $newProducts[] = [
                        'provider_id' => $providerId,
                        'reference' => $reference,
                        'brand' => $row[$format->brand_name] ?? null,
                        'ean' => $row[$format->ean_name] ?? null,
                        'description' => $row[$format->description_name]
                            ?? null,
                        'dimensions' => $row[$format->dimensions_name] ?? null,
                        'family_and_subfamily' =>
                            $row[$format->family_and_subfamily_name] ?? null,
                    ];
                    $productMap[$reference] = true;
                    $values =  [
                        'reference' => $reference,
                        'unit' => $unitValue,
                    ];
                    $newProductsUnit[] = $values;
                    $newPriceTier = $values;
                }

                $newPriceTier['min_quantity'] = $range['min'];
                $newPriceTier['max_quantity'] = $range['max'];
                $newPriceTier['unit_price'] = $price;
                $newPriceTier['unit'] = $unitValue;
                $newPriceTiers[] = $newPriceTier;
            }


            if (!empty($newProducts)) {
                $productMap = ProductService::insertAndGetMappedProducts(
                    $newProducts
                );
            }

            if (!empty($newProductsUnit)) {
                $productsId = [];

                foreach ($newProductsUnit as &$productUnit) {
                    $productId = $productUnit['product_id'] ?? null;

                    if (empty($productId)) {

                        $productId = $productMap[$productUnit['reference']];
                        $productUnit['product_id'] = $productId;
                    }

                    $productsId[] = $productId;

                    if (isset($productUnit['reference'])) {
                        unset($productUnit['reference']);
                    }
                }

                $productUnitsMap =
                    $productUnitService::insertAndGetMappedProductUnits(
                        $newProductsUnit, $productsId
                    );
            }

            if (!empty($newPriceTiers)) {
                foreach ($newPriceTiers as &$newPriceTier) {
                    $productUnitId = $newPriceTier['product_unit_id'] ?? null;

                    if (empty($productUnitId)) {
                        $productId = $productMap[$newPriceTier['reference']];
                        $productUnitKey = $productUnitService::buildKey(
                            $productId, $newPriceTier['unit']
                        );
                        $productUnitId = $productUnitsMap[$productUnitKey];
                    }

                    $newPriceTier['product_unit_id'] = $productUnitId;

                    if (isset($newPriceTier['unit'])) {
                        unset($newPriceTier['unit']);
                    }

                    if (isset($newPriceTier['reference'])) {
                        unset($newPriceTier['reference']);
                    }
                }

                PriceTier::insertMany($newPriceTiers);

            }

            DB::commit();
            return 'Import completed successfully';
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
