<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    public function provider() {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function units() {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Select only id and reference for a provider.
     *
     * @param int $providerId
     * @return \Illuminate\Support\Collection
     */
    public static function queryReferenceIdsByProvider(int $providerId): array
    {
        return self::select('id', 'reference')
            ->where('provider_id', $providerId)
            ->get()
            ->toArray();
    }

    /**
     * Insert multiple products in a single bulk operation.
     *
     * @param array $newProducts
     *   An array of associative arrays, each containing product attributes.
     * @return void
     */
    public static function insertManyAndGetKeyValues(array $newProducts)
    {
        self::insert($newProducts);

        return self::select('id', 'reference')
            ->where('provider_id', $newProducts[0]['provider_id'])
            ->get()
            ->toArray();
    }

    /**
     * Get all products for a provider mapped by reference.
     *
     * @param int $providerId
     * @return array reference => id
     */
    public static function getMapByProvider(int $providerId): array
    {
        return self::select('id', 'reference')
            ->where('provider_id', $providerId)
            ->get()
            ->toArray();
    }

    /**
     * Get all products for a provider mapped by reference.
     *
     * @param int $providerId
     * @return array reference => id
     */
    public static function getMapByProviderAndProducts(
        int $providerId,
        array $productIds
    ): array
    {
        return self::where('provider_id', $providerId)
            ->whereNotIn('id', $productIds)
            ->pluck('id', 'reference')
            ->toArray();
    }
}
