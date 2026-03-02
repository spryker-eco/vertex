<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Expander;

use DateTime;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ItemTaxMetadataTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper;

/**
 * This class is just example of how to implement expander for Vertex. No real data is used.
 */
class ItemWithVertexSpecificFieldsExpander
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
            $itemTransfer->setTaxMetadata(
                (new ItemTaxMetadataTransfer())
                    ->setProduct(
                        [
                            'productClass' => $this->vertexCodeMapper->getProductClassCode($itemTransfer->getSkuOrFail()),
                        ],
                    )
                    ->setFlexibleFields(
                        [
                            'flexibleCodeFields' => $this->getFlexibleCodeFields(),
                            'flexibleNumericFields' => $this->getFlexibleNumericFields(),
                            'flexibleDateFields' => $this->getFlexibleDateFields(),
                        ],
                    ),
            );
        }

        return $transfer;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getFlexibleCodeFields(): array
    {
        return [
            [
                'fieldId' => 1,
                'value' => 'VFFC_0',
            ],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getFlexibleNumericFields(): array
    {
        return [
            [
                'fieldId' => 2,
                'value' => 1000,
            ],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function getFlexibleDateFields(): array
    {
        return [
            [
                'fieldId' => 3,
                'value' => (new DateTime())->format('Y-m-d'),
            ],
        ];
    }
}
