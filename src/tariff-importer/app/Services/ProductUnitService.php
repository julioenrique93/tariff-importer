<?php
namespace App\Services;

use App\Models\ProductUnit;

class ProductUnitService
{
    /**
     * Build a unique key for mapping product units.
     *
     * The key format is "product_id|unit".
     *
     *
     * @param int $productId
     *   The product ID.
     * @param string $unit
     *   The unit string (e.g. "kg", "box").
     *
     * @return string
     *   Concatenated key in the format "product_id|unit".
     */
    public static function buildKey(int $productId, string $unit): string
    {
        return "$productId|$unit";
    }

    /**
     * Get a map of product units by product IDs.
     *
     * The map key is a concatenation of product_id and unit,
     * formatted as "product_id|unit". The value is the product_unit ID.
     *
     * @param array $productIds
     *   List of product IDs to fetch units for.
     *
     * @return array
     *   Associative array mapping "product_id|unit" to product_unit_id.
     */
    public function getUnitMap(array $productIds): array
    {
        $products = ProductUnit::getProducts($productIds);


        return ProductUnit::whereIn('product_id', $productIds)
            ->get()
            ->pluck('id', 'unit', 'product_id')
            ->mapWithKeys(function ($productUnit) {
                return [
                    self::buildKey($productUnit->product_id, $productUnit->unit)
                        => $productUnit->id,
                ];
            })
            ->toArray();
    }

    /**
     * Retrieve a map of product units for a given provider,
     * excluding already known product unit IDs.
     *
     * The map key is built using ProductUnit::buildKey(product_id, unit),
     * formatted as "product_id|unit". The value is the product_unit ID.
     *
     * @param array $newProductsUnit
     * @param array $productsId
     * @return array
     *   Associative array mapping "product_id|unit" to product_unit_id.
     */
    public static function insertAndGetMappedProductUnits(
        array $newProductsUnit,
        array $productsId
    ): array {
        $productUnits = ProductUnit::insertManyAndGetKeyValues(
            $newProductsUnit, $productsId
        );

        $mappedProductUnits = [];

        foreach ($productUnits as $productUnit) {
            $productUnitKey = self::buildKey(
                $productUnit['product_id'], $productUnit['unit']
            );
            $mappedProductUnits[$productUnitKey] = $productUnit['id'];
        }

        return $mappedProductUnits;
    }
}
