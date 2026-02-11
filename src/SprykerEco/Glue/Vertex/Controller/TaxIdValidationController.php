<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Glue\Vertex\Controller;

use Generated\Shared\Transfer\RestVertexValidationAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\Kernel\Controller\AbstractController;

/**
 * @method \SprykerEco\Glue\Vertex\VertexFactory getFactory()
 */
class TaxIdValidationController extends AbstractController
{
    /**
     * @Glue({
     *     "validateTaxId": {
     *          "summary": [
     *              "Validates taxId for country code."
     *          ],
     *          "parameters": [{
     *              "ref": "acceptLanguage"
     *          }],
     *          "responses": {
     *              "200": "Tax id is valid.",
     *              "400": "Validation is failed.",
     *              "422": "Tax identifier or country code is not specified"
     *          }
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function postAction(
        RestRequestInterface $restRequest,
        RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer,
    ): RestResponseInterface {
        $locale = $restRequest->getMetadata()->getLocale();

        return $this->getFactory()->createTaxIdValidator()->validate($restVertexValidationAttributesTransfer, $locale);
    }
}
