<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\AccessTokenProvider;

use DateTime;
use Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Pyz\Zed\VertexApi\Business\Authenticator\VertexApiAuthenticatorInterface;
use Pyz\Zed\VertexApi\Persistence\VertexApiRepositoryInterface;
use Pyz\Zed\VertexConfig\Business\EncryptionConfigurator\TenantPropelEncryptionConfiguratorInterface;
use Spryker\Shared\Log\LoggerTrait;

class AccessTokenProvider implements AccessTokenProviderInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_CANNOT_RETRIEVE_ACCESS_TOKEN = 'Unable to retrieve new access token using Vertex credentials';

    protected VertexApiAuthenticatorInterface $vertexApiAuthenticator;

    protected VertexApiRepositoryInterface $vertexApiRepository;

    protected TenantPropelEncryptionConfiguratorInterface $tenantPropelEncryptionConfigurator;

    /**
     * @param \Pyz\Zed\VertexApi\Business\Authenticator\VertexApiAuthenticatorInterface $vertexApiAuthenticator
     * @param \Pyz\Zed\VertexApi\Persistence\VertexApiRepositoryInterface $vertexApiRepository
     * @param \Pyz\Zed\VertexConfig\Business\EncryptionConfigurator\TenantPropelEncryptionConfiguratorInterface $tenantPropelEncryptionConfigurator
     */
    public function __construct(
        VertexApiAuthenticatorInterface $vertexApiAuthenticator,
        VertexApiRepositoryInterface $vertexApiRepository,
        TenantPropelEncryptionConfiguratorInterface $tenantPropelEncryptionConfigurator
    ) {
        $this->vertexApiAuthenticator = $vertexApiAuthenticator;
        $this->vertexApiRepository = $vertexApiRepository;
        $this->tenantPropelEncryptionConfigurator = $tenantPropelEncryptionConfigurator;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function provideVertexAccessToken(VertexConfigTransfer $vertexConfigTransfer): VertexApiAccessTokenTransfer
    {
        $vertexApiAccessTokenTransfer = $this->getAccessTokenFromCache($vertexConfigTransfer);

        if (!$vertexApiAccessTokenTransfer) {
            return $this->retrieveNewAccessToken($vertexConfigTransfer);
        }

        return $vertexApiAccessTokenTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer|null
     */
    protected function getAccessTokenFromCache(VertexConfigTransfer $vertexConfigTransfer): ?VertexApiAccessTokenTransfer
    {
        if (!$vertexConfigTransfer->getCredentialHash()) {
            return null;
        }

        $vertexApiTokenCriteriaTransfer = (new VertexApiAccessTokenCriteriaTransfer())
            ->setCredentialHash($vertexConfigTransfer->getCredentialHash());

        $vertexApiAccessTokenTransfer = $this->vertexApiRepository->findAccessToken($vertexApiTokenCriteriaTransfer);

        if ($vertexApiAccessTokenTransfer->getAccessToken() && !$this->isAccessTokenExpired($vertexApiAccessTokenTransfer)) {
            return $vertexApiAccessTokenTransfer;
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    protected function retrieveNewAccessToken(VertexConfigTransfer $vertexConfigTransfer): VertexApiAccessTokenTransfer
    {
        $this->configurePropelEncryption($vertexConfigTransfer);

        $vertexApiAccessTokenTransfer = (new VertexApiAccessTokenTransfer());

        $vertexApiAuthResponseTransfer = $this->vertexApiAuthenticator->authenticate($vertexConfigTransfer);

        if (!$vertexApiAuthResponseTransfer->getErrors()) {
            return $vertexApiAccessTokenTransfer
                ->setCredentialHash($vertexConfigTransfer->getCredentialHash())
                ->setAccessToken($vertexApiAuthResponseTransfer->getAccessToken());
        }

        $this->getLogger()->error(
            static::ERROR_MESSAGE_CANNOT_RETRIEVE_ACCESS_TOKEN,
            ['cause' => $vertexApiAuthResponseTransfer->getErrors()],
        );

        return $vertexApiAccessTokenTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @return bool
     */
    protected function isAccessTokenExpired(VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer): bool
    {
        $dateTime = new DateTime($vertexApiAccessTokenTransfer->getExpirationDate());

        if ($dateTime->getTimestamp() <= time()) {
            return true;
        }

        return false;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return void
     */
    protected function configurePropelEncryption(VertexConfigTransfer $vertexConfigTransfer): void
    {
        $this->tenantPropelEncryptionConfigurator->configurePropelEncryption($vertexConfigTransfer->getStoreReference());
    }
}
