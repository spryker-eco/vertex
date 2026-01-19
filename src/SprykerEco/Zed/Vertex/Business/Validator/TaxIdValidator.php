<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Validator;

use Generated\Shared\Transfer\AcpHttpRequestTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface;
use Spryker\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface;
use Spryker\Zed\Vertex\Business\Config\ConfigReaderInterface;
use Spryker\Zed\Vertex\Dependency\Facade\VertexToKernelAppFacadeInterface;
use Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface;
use Spryker\Zed\Vertex\VertexConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    protected const GLOSSARY_KEY_TAX_APP_IS_DISABLED = 'tax_app.vertex.tax-app-disabled';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_TAX_VALIDATOR_IS_UNAVAILABLE = 'tax_app.vertex.tax-validator-unavailable';

    /**
     * @param \Spryker\Zed\Vertex\Business\Config\ConfigReaderInterface $configReader
     * @param \Spryker\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToKernelAppFacadeInterface $kernelAppFacade
     * @param \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface $entityManager
     * @param \Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        protected ConfigReaderInterface $configReader,
        protected AccessTokenProviderInterface $accessTokenProvider,
        protected VertexToKernelAppFacadeInterface $kernelAppFacade,
        protected VertexEntityManagerInterface $entityManager,
        protected VertexToUtilEncodingServiceInterface $utilEncodingService
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $VertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validate(VertexValidationRequestTransfer $VertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        $VertexValidationRequestTransfer->requireTaxId();
        $VertexValidationRequestTransfer->requireCountryCode();
        $VertexConfigTransfer = $this->configReader->findVertexConfigForCurrentStore();

        if (
            !$VertexConfigTransfer ||
            !$VertexConfigTransfer->getIsActive() ||
            !$VertexConfigTransfer->getApiUrls() ||
            !$VertexConfigTransfer->getApiUrls()->getTaxIdValidationUrl()
        ) {
            return $this->createVertexValidationResponseTransfer(false, VertexConfig::MESSAGE_TAX_APP_IS_DISABLED, static::GLOSSARY_KEY_TAX_APP_IS_DISABLED);
        }

        $acpHttpResponseTransfer = $this->kernelAppFacade->makeRequest(
            (new AcpHttpRequestTransfer())
                ->setUri($VertexConfigTransfer->getApiUrls()->getTaxIdValidationUrlOrFail())
                ->setMethod(Request::METHOD_POST)
                ->setBody((string)$this->utilEncodingService->encodeJson($VertexValidationRequestTransfer->toArray(true, true)))
                ->setHeaders([static::HEADER_AUTHORIZATION => $this->accessTokenProvider->getAccessToken()]),
        );
        if ($acpHttpResponseTransfer->getHttpStatusCode() !== Response::HTTP_OK && $acpHttpResponseTransfer->getContent() === null) {
            return $this->createVertexValidationResponseTransfer(false, VertexConfig::MESSAGE_TAX_VALIDATOR_IS_UNAVAILABLE, static::GLOSSARY_KEY_TAX_VALIDATOR_IS_UNAVAILABLE);
        }

        $content = (array)$this->utilEncodingService->decodeJson((string)$acpHttpResponseTransfer->getContent(), true);

        if (!$content) {
            return $this->createVertexValidationResponseTransfer(false, VertexConfig::MESSAGE_TAX_VALIDATOR_IS_UNAVAILABLE, static::GLOSSARY_KEY_TAX_VALIDATOR_IS_UNAVAILABLE);
        }
        $content = $acpHttpResponseTransfer->getHttpStatusCode() === Response::HTTP_OK ? $content : current($content);
        $messageKey = $content[static::CONTENT_KEY_CODE] ?? null;
        $VertexValidationResponseTransfer = (new VertexValidationResponseTransfer())
            ->setMessageKey($messageKey)
            ->setIsValid(false)
            ->fromArray($content, true);

        if ($VertexValidationResponseTransfer->getIsValid() === true) {
            $this->entityManager->saveTaxIdValidationHistory(
                (new TaxIdValidationHistoryTransfer())
                    ->fromArray($VertexValidationResponseTransfer->toArray(), true)
                    ->setTaxId((string)$VertexValidationRequestTransfer->getTaxId())
                    ->setCountryCode((string)$VertexValidationRequestTransfer->getCountryCode())
                    ->setResponseData((string)$VertexValidationResponseTransfer->getAdditionalInfo()),
            );
        }

        return $VertexValidationResponseTransfer;
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
