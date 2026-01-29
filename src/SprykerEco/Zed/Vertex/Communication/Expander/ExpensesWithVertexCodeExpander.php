<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Expander;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTaxMetadataTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper;

class ExpensesWithVertexCodeExpander
{
    /**
     * @param \SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper $vertexCodeMapper
     */
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
        if ($transfer->getExpenses()->count() > 0) {
            foreach ($transfer->getExpenses() as $expenseTransfer) {
                $expenseTransfer->setTaxMetadata(
                    (new ItemTaxMetadataTransfer())
                        ->setProduct(
                            [
                                'productClass' => $this->vertexCodeMapper->getProductClassCode($this->getExpenseKey($expenseTransfer)),
                            ],
                        ),
                );
            }
        }

        return $transfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return string
     */
    protected function getExpenseKey(ExpenseTransfer $expenseTransfer): string
    {
        return implode(
            '|',
            [
                $expenseTransfer->getType(),
                $expenseTransfer->getName(),
                $expenseTransfer->getMerchantReference(),
            ],
        );
    }
}
