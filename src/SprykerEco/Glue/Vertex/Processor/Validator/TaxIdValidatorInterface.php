<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\Vertex\Processor\Validator;

use Generated\Shared\Transfer\RestVertexValidationAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface TaxIdValidatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer
     * @param string $locale
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function validate(RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer, string $locale): RestResponseInterface;
}
