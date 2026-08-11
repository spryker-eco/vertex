<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEco\Glue\Vertex\Api\Storefront\Processor;

use Generated\Api\Storefront\TaxIdValidateStorefrontResource;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Glue\Vertex\Api\Storefront\Exception\VertexExceptionFactory;
use SprykerEco\Glue\Vertex\VertexConfig;

class TaxIdValidateStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string GLOSSARY_SUFFIX_VERTEX = 'vertex';

    protected const string GLOSSARY_KEY_VERTEX_IS_DISABLED = 'vertex.tax-app-disabled';

    protected const string GLOSSARY_KEY_INVALID_REQUEST_DATA = 'vertex.invalid-request-data';

    protected const string MESSAGE_VERTEX_IS_DISABLED = 'Tax service is disabled.';

    public function __construct(
        protected VertexClientInterface $vertexClient,
        protected GlossaryStorageClientInterface $glossaryStorageClient,
        protected VertexConfig $vertexConfig,
        protected VertexExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): TaxIdValidateStorefrontResource
    {
        $locale = $this->getLocale()->getLocaleNameOrFail();

        if ($this->vertexConfig->getIsActive() === false) {
            $detail = $this->translateGlossaryMessage(
                static::MESSAGE_VERTEX_IS_DISABLED,
                $locale,
                sprintf('%s.%s', static::GLOSSARY_SUFFIX_VERTEX, static::GLOSSARY_KEY_VERTEX_IS_DISABLED),
            );

            throw $this->exceptionFactory->createVertexDisabledException($detail);
        }

        if ($data->taxId === null || $data->taxId === '' || $data->countryCode === null || $data->countryCode === '') {
            $detail = $this->translateGlossaryMessage(
                VertexConfig::RESPONSE_DETAIL_MESSAGE_INVALID_REQUEST_DATA,
                $locale,
                static::GLOSSARY_KEY_INVALID_REQUEST_DATA,
            );

            throw $this->exceptionFactory->createInvalidRequestDataException($detail);
        }

        $vertexValidationResponseTransfer = $this->vertexClient->requestTaxIdValidation(
            (new VertexValidationRequestTransfer())
                ->setTaxId($data->taxId)
                ->setCountryCode($data->countryCode),
        );

        if ($vertexValidationResponseTransfer->getIsValid()) {
            return $data;
        }

        $message = $vertexValidationResponseTransfer->getMessage();

        if ($message !== null && $message !== '') {
            $rawMessageKey = $vertexValidationResponseTransfer->getMessageKey();
            $messageKey = $rawMessageKey !== null && $rawMessageKey !== ''
                ? sprintf('%s.%s', static::GLOSSARY_SUFFIX_VERTEX, $rawMessageKey)
                : null;

            $detail = $this->translateGlossaryMessage($message, $locale, $messageKey);

            throw $this->exceptionFactory->createValidationFailedException($detail);
        }

        throw $this->exceptionFactory->createValidationFailedWithoutMessageException();
    }

    protected function translateGlossaryMessage(string $defaultMessage, string $locale, ?string $messageKey): string
    {
        if ($messageKey === null || $messageKey === '') {
            return $defaultMessage;
        }

        $translated = $this->glossaryStorageClient->translate($messageKey, $locale);

        if ($translated !== $messageKey) {
            return $translated;
        }

        return $defaultMessage;
    }
}
