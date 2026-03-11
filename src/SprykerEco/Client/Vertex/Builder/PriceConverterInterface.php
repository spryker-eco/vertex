<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder;

interface PriceConverterInterface
{
    /**
     * @param string|int $priceAmount
     *
     * @return float
     */
    public function convertPriceForVertex(int|string $priceAmount): float;

    /**
     * @param string|int $priceAmount
     *
     * @return float
     */
    public function convertToNegatedPriceForVertex(int|string $priceAmount): float;

    public function convertPriceForSpryker(float $priceAmount): string;
}
