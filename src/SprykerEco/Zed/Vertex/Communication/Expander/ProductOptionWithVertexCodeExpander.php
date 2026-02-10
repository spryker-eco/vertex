<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Expander;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ItemTaxMetadataTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper;

class ProductOptionWithVertexCodeExpander
{
    public function __construct(protected VertexCodeMapper $vertexCodeMapper)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer|\Generated\Shared\Transfer\CalculableObjectTransfer $transfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer|\Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function expand(OrderTransfer|CalculableObjectTransfer $transfer): OrderTransfer|CalculableObjectTransfer
    {
        foreach ($transfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getProductOptions()->count() <= 0) {
                continue;
            }

            foreach ($itemTransfer->getProductOptions() as $productOption) {
                $productOption->setTaxMetadata(
                    (new ItemTaxMetadataTransfer())
                        ->setProduct(
                            [
                                'productClass' => $this->vertexCodeMapper->getProductOptionClassCode($productOption->getSku()),
                            ],
                        ),
                );
            }
        }

        return $transfer;
    }
}
