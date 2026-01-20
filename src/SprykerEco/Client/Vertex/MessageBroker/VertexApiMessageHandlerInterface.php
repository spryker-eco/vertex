<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\MessageBroker;

use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;

interface VertexApiMessageHandlerInterface
{
    /**
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer
     */
    public function handleSubmitPaymentTaxInvoice(SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer): SubmitPaymentTaxInvoiceResponseTransfer;
}
