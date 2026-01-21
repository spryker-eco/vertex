<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\MessageBroker;

use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;

interface VertexApiMessageHandlerInterface
{
    /**
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer
     */
    public function handleSubmitPaymentTaxInvoice(
        SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): SubmitPaymentTaxInvoiceResponseTransfer;
}
