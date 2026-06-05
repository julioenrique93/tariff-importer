<?php
namespace App\Services;

use App\Models\Product;
class ProductService
{
    /**
     * Get product reference map for a provider.
     *
     * @param int $providerId
     * @return array<string,int>
     */
    public static function getReferenceMap(int $providerId): array
    {
        $products = Product::queryReferenceIdsByProvider($providerId);

        return self::getProductsMap($products);
    }

    /**
     * Get products maped key value.
     *
     * @param array $newProducts
     * @return array<string,int>
     */
    public static function insertAndGetMappedProducts(
        array $newProducts
    ): array {

        $products = Product::insertManyAndGetKeyValues($newProducts);

        return self::getProductsMap($products);
    }

    /**
     * Get products map for a provider.
     *
     * @param int $providerId
     * @return array<string,int>
     */
    public static function getProductsMap(array $products): array
    {
        $map = [];

        foreach ($products as $product) {
            $map[$product['reference']] = $product['id'];
        }

        return $map;
    }
}
