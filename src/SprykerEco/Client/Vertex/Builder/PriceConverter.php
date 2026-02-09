<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder;

class PriceConverter
{
    /**
     * @var int
     */
    protected const CURRENCY_DENOMINATION_DEFAULT = 100;

    /**
     * @param string $priceAmount
     *
     * @return float
     */
    public function convertPriceForVertex(string $priceAmount): float
    {
        if (!$priceAmount) {
            return 0;
        }

        return (int)$priceAmount / static::CURRENCY_DENOMINATION_DEFAULT;
    }

    /**
     * @param string $priceAmount
     *
     * @return float
     */
    public function convertToNegatedPriceForVertex(string $priceAmount): float
    {
        return -1 * $this->convertPriceForVertex($priceAmount);
    }

    /**
     * @param float $priceAmount
     *
     * @return string
     */
    public function convertPriceForSpryker(float $priceAmount): string
    {
        return (string)(abs($priceAmount) * static::CURRENCY_DENOMINATION_DEFAULT);
    }
}
