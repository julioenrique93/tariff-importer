<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderFormat extends Model
{
    public function provider() {
        return $this->hasOne(User::class, 'provider_id');
    }

    /**
     * Retrieve provider format configuration by provider ID.
     *
     * This static method encapsulates the query to fetch the format
     * definition for a given provider. It uses an explicit SELECT to
     * ensure only required columns are retrieved, improving performance
     * and clarity. If no format is found, it throws a ModelNotFoundException.
     *
     * @param int $providerId The provider identifier.
     * @return ProviderFormat|null The provider format configuration instance.
     */
    public static function byProvider(int $providerId): ?ProviderFormat
    {
        return self::select([
            'reference_name',
            'brand_name',
            'ean_name',
            'description_name',
            'dimensions_name',
            'family_and_subfamily_name',
            'price_name',
            'unit_name',
            'stretch_name',
        ])
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * Return required columns as a map column => true.
     *
     * Each configured column name is returned as a key
     * with a boolean flag. This allows quick validation
     * against Excel headers or row values.
     *
     * @return array
     */
    public function columnsMap(): array
    {
        return [
            $this->reference_name => true,
            $this->brand_name => true,
            $this->ean_name => true,
            $this->description_name => true,
            $this->dimensions_name => true,
            $this->family_and_subfamily_name => true,
            $this->price_name => true,
            $this->stretch_name => true,
            $this->unit_name => true,
        ];
    }

    /**
     * Parse quantity range text into min/max values.
     *
     * Supported formats:
     * - "1"       => ['min' => 1, 'max' => null]   // exact start, open end
     * - "1-10"    => ['min' => 1, 'max' => 10]     // closed range
     * - "10+"     => ['min' => 10, 'max' => '-1']   // 10 or more
     *
     * @param string $text Quantity cell value from Excel
     * @return array
     */
   public  function parseQuantityRange(string $text): array
   {
       $text = strtolower(trim($text, "'"));

       // Case: open-ended (10+)
       if (preg_match('/(\d+)\s*\+/', $text, $matches)) {
           return ['min' => (int) $matches[1], 'max' => '-1'];
       }

       // Case: closed range (1-10)
       if (preg_match('/(\d+)\s*-\s*(\d+)/', $text, $matches)) {
           return ['min' => (int) $matches[1], 'max' => (int) $matches[2]];
       }

       // Case: exact start (1)
       if (preg_match('/^\d+$/', $text)) {
           $val = (int) $text;
           return ['min' => $val, 'max' => null];
       }

       // Default: invalid format
       return ['min' => null, 'max' => null];
   }
}
