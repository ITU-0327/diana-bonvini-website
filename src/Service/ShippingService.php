<?php
declare(strict_types=1);

namespace App\Service;

class ShippingService
{
    /**
     * Shipping rates configuration
     */
    private array $shippingRates = [
        'AU' => [
            'NSW' => [
                'metro' => 20.00,
                'regional' => 25.00,
            ],
            'NT' => 30.00,
            'WA' => 30.00,
            'default' => 25.00,
        ],
        'default' => 45.00,
    ];

    /**
     * Size limits for standard shipping
     */
    private array $sizeLimits = [
        'length' => 1050,
        'width' => 420,
    ];

    /**
     * Large item shipping configuration
     */
    private array $largeItemConfig = [
        'baseFee' => 50.00,
        'freeWeight' => 5.00,
        'weightRate' => 10.00,
        'height' => 100,
    ];

    /**
     * Constructor
     *
     * @param array|null $config Optional configuration override
     */
    public function __construct(?array $config = null)
    {
        if ($config !== null) {
            $this->shippingRates = $config['shippingRates'] ?? $this->shippingRates;
            $this->sizeLimits = $config['sizeLimits'] ?? $this->sizeLimits;
            $this->largeItemConfig = $config['largeItemConfig'] ?? $this->largeItemConfig;
        }
    }

    /**
     * Calculate shipping fee based on location and artwork size
     *
     * @param string $state The state/territory code
     * @param string $country The country code
     * @param float $weight The weight of the artwork in kg
     * @param float $length The length of the artwork in mm
     * @param float $width The width of the artwork in mm
     * @return float The shipping fee
     */
    public function calculateShippingFee(string $state, string $country, float $weight = 0, float $length = 0, float $width = 0): float
    {
        // For standard sized orders (up to A3 or tubes up to 1050mm)
        if ($length <= $this->sizeLimits['length'] && $width <= $this->sizeLimits['width']) {
            return $this->getStandardShippingFee($state, $country);
        }

        // For larger items, calculate based on weight and cubic size
        return $this->calculateLargeItemShippingFee($weight, $length, $width);
    }

    /**
     * Get standard shipping fee based on location
     *
     * @param string $state The state/territory code
     * @param string $country The country code
     * @return float The shipping fee
     */
    private function getStandardShippingFee(string $state, string $country): float
    {
        // Get country rates or use default
        $countryRates = $this->shippingRates[$country] ?? $this->shippingRates['default'];

        // If country is not Australia, return overseas rate
        if ($country !== 'AU') {
            return $countryRates;
        }

        // Handle NSW special case (metro vs regional)
        if ($state === 'NSW') {
            // Check if it's Sydney Metro (postcodes 1000-2999)
            if (preg_match('/^[1-2]\d{3}$/', $state)) {
                return $countryRates['NSW']['metro'];
            }

            return $countryRates['NSW']['regional'];
        }

        // Return state-specific rate or default interstate rate
        return $countryRates[$state] ?? $countryRates['default'];
    }

    /**
     * Calculate shipping fee for large items based on weight and dimensions
     *
     * @param float $weight The weight in kg
     * @param float $length The length in mm
     * @param float $width The width in mm
     * @return float The shipping fee
     */
    private function calculateLargeItemShippingFee(float $weight, float $length, float $width): float
    {
        // Calculate cubic weight (length * width * height / 4000)
        $cubicWeight = ($length * $width * $this->largeItemConfig['height']) / 4000;

        // Use the greater of actual weight and cubic weight
        $chargeableWeight = max($weight, $cubicWeight);

        // Add rate per kg after the free weight limit
        if ($chargeableWeight > $this->largeItemConfig['freeWeight']) {
            return $this->largeItemConfig['baseFee'] +
                   (($chargeableWeight - $this->largeItemConfig['freeWeight']) * $this->largeItemConfig['weightRate']);
        }

        return $this->largeItemConfig['baseFee'];
    }
}
