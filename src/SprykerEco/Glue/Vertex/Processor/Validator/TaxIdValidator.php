<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\Vertex\Processor\Validator;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\RestVertexValidationAttributesTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Glue\Vertex\VertexConfig;
use Symfony\Component\HttpFoundation\Response;

class TaxIdValidator implements TaxIdValidatorInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_RESPONSE_DETAIL_INVALID_REQUEST_DATA = 'vertex.invalid-request-data';

    /**
     * @var string
     */
    protected const GLOSSARY_SUFFIX_VERTEX = 'vertex';

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $vertexClient
     * @param \Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface $glossaryStorageClient
     */
    public function __construct(
        protected RestResourceBuilderInterface $restResourceBuilder,
        protected VertexClientInterface $vertexClient,
        protected GlossaryStorageClientInterface $glossaryStorageClient
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer
     * @param string $locale
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function validate(RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer, string $locale): RestResponseInterface
    {
        if (!$restVertexValidationAttributesTransfer->getTaxId() || !$restVertexValidationAttributesTransfer->getCountryCode()) {
            $messageByLocale = $this->getGlossaryMessage(VertexConfig::RESPONSE_DETAIL_MESSAGE_INVALID_REQUEST_DATA, $locale, static::GLOSSARY_KEY_RESPONSE_DETAIL_INVALID_REQUEST_DATA);

            return $this->restResourceBuilder->createRestResponse()->addError(
                (new RestErrorMessageTransfer())
                    ->setStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->setDetail($messageByLocale),
            );
        }
        $vertexValidationRequestTransfer = (new VertexValidationRequestTransfer())->fromArray($restVertexValidationAttributesTransfer->toArray(), true);
        $vertexValidationResponseTransfer = $this->vertexClient->requestTaxIdValidation($vertexValidationRequestTransfer);

        if ($vertexValidationResponseTransfer->getIsValid()) {
            return $this->restResourceBuilder->createRestResponse()->setStatus(Response::HTTP_OK);
        }

        if ($vertexValidationResponseTransfer->getMessage()) {
            $messageKey = $vertexValidationResponseTransfer->getMessageKey() ? sprintf('%s.%s', static::GLOSSARY_SUFFIX_VERTEX, $vertexValidationResponseTransfer->getMessageKey()) : null;

            $messageByLocale = $this->getGlossaryMessage($vertexValidationResponseTransfer->getMessage(), $locale, $messageKey);

            return $this->restResourceBuilder->createRestResponse()->addError(
                (new RestErrorMessageTransfer())
                    ->setStatus(Response::HTTP_BAD_REQUEST)
                    ->setDetail($messageByLocale),
            )->setStatus(Response::HTTP_BAD_REQUEST);
        }

        return $this->restResourceBuilder->createRestResponse()->setStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param string $defaultMessage
     * @param string $locale
     * @param string|null $messageKey
     *
     * @return string|null
     */
    protected function getGlossaryMessage(string $defaultMessage, string $locale, ?string $messageKey): ?string
    {
        if (!$messageKey) {
            return $defaultMessage;
        }

        $message = $this->glossaryStorageClient->translate($messageKey, $locale);

        if ($message !== $messageKey) {
            return $message;
        }

        return $defaultMessage;
    }
}
