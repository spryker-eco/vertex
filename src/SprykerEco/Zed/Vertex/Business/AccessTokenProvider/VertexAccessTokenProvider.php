<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Business\AccessTokenProvider;

use DateInterval;
use DateTime;
use Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Zed\Vertex\Business\EncryptionConfigurator\TenantPropelEncryptionConfiguratorInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface;

class VertexAccessTokenProvider implements VertexAccessTokenProviderInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_CANNOT_RETRIEVE_ACCESS_TOKEN = 'Unable to retrieve new access token using Vertex credentials';

    protected VertexClientInterface $vertexClient;

    protected VertexRepositoryInterface $vertexRepository;

    protected VertexEntityManagerInterface $vertexEntityManager;

    protected TenantPropelEncryptionConfiguratorInterface $tenantPropelEncryptionConfigurator;

    /**
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $vertexClient
     * @param \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface $vertexRepository
     * @param \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface $vertexEntityManager
     * @param \SprykerEco\Zed\Vertex\Business\EncryptionConfigurator\TenantPropelEncryptionConfiguratorInterface $tenantPropelEncryptionConfigurator
     */
    public function __construct(
        VertexClientInterface $vertexClient,
        VertexRepositoryInterface $vertexRepository,
        VertexEntityManagerInterface $vertexEntityManager,
        TenantPropelEncryptionConfiguratorInterface $tenantPropelEncryptionConfigurator
    ) {
        $this->vertexClient = $vertexClient;
        $this->vertexRepository = $vertexRepository;
        $this->vertexEntityManager = $vertexEntityManager;
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

        $vertexApiAccessTokenTransfer = $this->vertexRepository->findAccessToken($vertexApiTokenCriteriaTransfer);

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

        $vertexAuthResponseTransfer = $this->vertexClient->authenticate($vertexConfigTransfer);

        if ($vertexAuthResponseTransfer->getErrors()) {
            $this->getLogger()->error(
                static::ERROR_MESSAGE_CANNOT_RETRIEVE_ACCESS_TOKEN,
                ['cause' => $vertexAuthResponseTransfer->getErrors()],
            );

            return $vertexApiAccessTokenTransfer;
        }

        $vertexApiAccessTokenTransfer = (new VertexApiAccessTokenTransfer())
            ->setCredentialHash($vertexConfigTransfer->getCredentialHash())
            ->setAccessToken($vertexAuthResponseTransfer->getAccessToken())
            ->setExpirationDate($this->calculateExpirationDate($vertexAuthResponseTransfer->getExpiresInOrFail()));

        $this->vertexEntityManager->saveAccessToken($vertexApiAccessTokenTransfer);

        return $vertexApiAccessTokenTransfer
            ->setCredentialHash($vertexConfigTransfer->getCredentialHash())
            ->setAccessToken($vertexAuthResponseTransfer->getAccessToken());
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

    /**
     * @param int $expiresIn in seconds, default on Vertex is 1800 seconds (30 minutes)
     * https://developer.vertexinc.com/oseries/docs/authenticate-op-od
     *
     * @return string|null
     */
    protected function calculateExpirationDate(int $expiresIn): ?string
    {
        $dateTime = new DateTime();
        $dateTime->add(new DateInterval(sprintf('PT%dS', $expiresIn)));

        return $dateTime->format('Y-m-d H:i:s');
    }
}
