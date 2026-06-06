<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function priceTiers() {
        return $this->hasMany(PriceTier::class);
    }

    public function taxes() {
        return $this->hasMany(Tax::class);
    }

    /**
     * Get all product units for a list of product IDs,
     * mapped by "product_id|unit".
     *
     * @param array $productIds
     * @return array
     */
    public static function getProducts(array $productIds): array
    {
        return self::select('id', 'unit', 'product_id')
            ->whereIn('product_id', $productIds)
            ->get()
            ->toArray();
    }

    /**
     * Insert multiple products units in a single bulk operation.
     *
     * @param array $newProductUnits
     *   An array of associative arrays, each containing product attributes.
     * @return void
     */
    public static function insertMany(array $newProductUnits)
    {
        self::insert($newProductUnits);
    }

    /**
     * Get all product units for a products mapped.
     *
     * @param array $newProductUnits
     * @param array $productsId
     * @return array
     */
    public static function insertManyAndGetKeyValues(
        array $newProductUnits,
        array $productsId
    ): array {
        self::insert($newProductUnits);

        return self::select('id', 'product_id', 'unit')
            ->whereIn('product_id', $productsId)
            ->get()
            ->toArray();
    }
}
