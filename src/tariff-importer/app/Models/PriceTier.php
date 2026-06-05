<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    public function productUnit() {
        return $this->belongsTo(ProductUnit::class);
    }

    /**
     * Delete all price tiers for a specific product
     *
     * Performs a direct database deletion without loading models into memory.
     * More efficient than deleting records one by one.
     *
     * @param int $productUnitId The product unit  ID whose tiers will be deleted
     * @return int Number of deleted records
     *
     */
    public static function deleteByProducts(int $productUnitId): int
    {
        return self::where('product_unit_id', $productUnitId)->delete();
    }


    /**
     * Insert multiple price tiers in a single bulk operation.
     *
     * @param array $newPriceTiers
     *   An array of associative arrays, each containing price tiers attributes.
     * @return void
     */
    public static function insertMany(array $newPriceTiers)
    {
        self::insert($newPriceTiers);
    }
}
