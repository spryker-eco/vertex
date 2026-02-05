<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Validator;

use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class TaxIdValidator implements TaxIdValidatorInterface
{
    /**
     * @var string
     */
    protected const HEADER_AUTHORIZATION = 'Authorization';

    /**
     * @var string
     */
    protected const CONTENT_KEY_CODE = 'code';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_VERTEX_IS_DISABLED = 'vertex.tax-app-disabled';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_TAX_VALIDATOR_IS_UNAVAILABLE = 'vertex.tax-validator-unavailable';

    /**
     * @param \SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface $vertexConfigResolver
     * @param \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface $entityManager
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $vertexClient
     */
    public function __construct(
        protected VertexConfigResolverInterface $vertexConfigResolver,
        protected VertexEntityManagerInterface $entityManager,
        protected UtilEncodingServiceInterface $utilEncodingService,
        protected VertexClientInterface $vertexClient,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validate(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        $vertexValidationRequestTransfer->requireTaxId();
        $vertexValidationRequestTransfer->requireCountryCode();
        $vertexConfigTransfer = $this->vertexConfigResolver->resolve();

        if (
            !$vertexConfigTransfer ||
            !$vertexConfigTransfer->getIsActive()
        ) {
            return $this->createVertexValidationResponseTransfer(false, VertexConfig::MESSAGE_VERTEX_IS_DISABLED, static::GLOSSARY_KEY_VERTEX_IS_DISABLED);
        }

        $taxIdValidationRequestTransfer = (new TaxIdValidationRequestTransfer())
            ->fromArray($vertexValidationRequestTransfer->toArray(), true);
        $vertexValidationResponseTransfer = $this->vertexClient->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer())
            ->fromArray($vertexValidationResponseTransfer->toArray(), true)
            ->setMessageKey($vertexValidationResponseTransfer->getMessageKey() ?? null);

        if ($vertexValidationResponseTransfer->getIsValid() === true) {
            $this->entityManager->saveTaxIdValidationHistory(
                (new TaxIdValidationHistoryTransfer())
                    ->fromArray($vertexValidationResponseTransfer->toArray(), true)
                    ->setTaxId((string)$vertexValidationRequestTransfer->getTaxId())
                    ->setCountryCode((string)$vertexValidationRequestTransfer->getCountryCode())
                    ->setResponseData((string)$vertexValidationResponseTransfer->getAdditionalInfo()),
            );
        }

        return $vertexValidationResponseTransfer;
    }

    /**
     * @param bool $isValid
     * @param string $message
     * @param string $messageKey
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    protected function createVertexValidationResponseTransfer(
        bool $isValid,
        string $message,
        string $messageKey
    ): VertexValidationResponseTransfer {
        return (new VertexValidationResponseTransfer())
            ->setIsValid($isValid)
            ->setMessageKey($messageKey)
            ->setMessage($message);
    }
}
