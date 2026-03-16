<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Payment;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;

interface PaymentSubmitTaxInvoiceHandlerInterface
{
    public function handleSubmitPaymentTaxInvoice(OrderTransfer $orderTransfer): VertexCalculationResponseTransfer;
}
