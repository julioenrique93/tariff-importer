<?php
namespace App\Services;

use App\Models\Product;
class ProductService
{
    /**
     * Get product reference map for a provider.
     *
     * @param int $providerId
     * @return array
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
     * @return array
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
     * @param array $products
     * @return array
     */
    public static function getProductsMap(array $products): array
    {
        $map = [];

        foreach ($products as $product) {
            $map[$product['reference']] = $product['id'];
        }

        return $map;
    }

    /**
     * Seek-based search for imported products.
     *
     * @param array $filters
     *   - brand (string, optional): filter by product brand.
     *   - reference (string, optional): filter by product reference.
     * @param int $limit
     *   Maximum number of records per page (default 100, capped at 200).

     * @return array
     *   - data: list of products
     *   - has_more: boolean indicating if more records exist
     */
    public function search(
        array $filters,
        int $limit = 10
    ): array
    {
        $limit = min($limit, 200);

        $query = Product::query()
            ->select([
                'id', 'reference', 'brand', 'ean', 'description', 'dimensions',
                'family_and_subfamily', 'provider_id'
            ])
            ->orderBy('id')
            ->limit($limit + 1);

        $query->when(
            !empty($filters['brand']),
            fn($q) => $q->where('brand', $filters['brand'])
        );
        $query->when(
            !empty($filters['reference']),
            fn($q) => $q->where('reference', $filters['reference'])
        );

        $products = $query->get();
        $hasMore = $products->count() > $limit;

        if ($hasMore) {
            $products = $products->slice(0, $limit);
        }

        return [
            'data' => $products->values(),
            'has_more' => $hasMore,
        ];
    }
}
