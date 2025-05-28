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
                'metro' => 20.00,        // Sydney Metro
                'regional' => 25.00,     // Regional NSW
            ],
            'NT' => 30.00,              // Northern Territory
            'WA' => 30.00,              // Western Australia
            'default' => 25.00,         // All other Australian states (Interstate)
        ],
        'international' => 45.00,       // Overseas shipping ($40-$50 range, using $45 as middle point)
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
        // If country is not Australia, return international rate
        if ($country !== 'AU') {
            return $this->shippingRates['international'];
        }

        // Get Australia rates
        $australiaRates = $this->shippingRates['AU'];

        // Handle NSW special case (metro vs regional)
        if ($state === 'NSW') {
            // For now, default to regional NSW rate
            return $australiaRates['NSW']['regional'];
        }

        // Return state-specific rate or default interstate rate
        return $australiaRates[$state] ?? $australiaRates['default'];
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
        // Convert dimensions from mm to cm
        $lengthCm = $length / 10;
        $widthCm = $width / 10;
        $heightCm = $this->largeItemConfig['height'] / 10; // Convert height to cm as well

        // Calculate cubic weight (length * width * height / 4000) - using cm
        $cubicWeight = ($lengthCm * $widthCm * $heightCm) / 4000;

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
