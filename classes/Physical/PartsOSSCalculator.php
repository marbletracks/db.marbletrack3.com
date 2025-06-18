<?php
namespace Physical;

class PartsOSSCalculator
{
    // Best-fit slope and intercept from your analysis
    private const SLOPE = 0.1864047531;
    private const INTERCEPT = 25.2055467494;

    /**
     * Given an SSOP distance (in mm), return the best-fit height (in mm)
     */
    public static function getBestFitHeight(float $ssop_mm): float
    {
        // Convert mm to cm for slope calculation
        $ssop_cm = $ssop_mm / 10.0;
        return self::SLOPE * $ssop_cm + self::INTERCEPT;
    }

    /**
     * Given a height (in mm), return the optimal SSOP location (in mm)
     */
    public static function getBestFitSSOP(float $height_mm): float
    {
        $ssop_cm = ($height_mm - self::INTERCEPT) / self::SLOPE;
        return $ssop_cm * 10.0;  // Convert back to mm
    }

}
