<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Business\SecretsManager;

use Generated\Shared\Transfer\SecretKeyTransfer;
use Generated\Shared\Transfer\SecretTagTransfer;
use Generated\Shared\Transfer\SecretTransfer;
use Pyz\Zed\VertexConfig\Business\Exception\StoreSecretNotCreatedException;
use Spryker\Client\SecretsManager\SecretsManagerClientInterface;
use Spryker\Service\UtilText\UtilTextServiceInterface;

class SecretsManager implements SecretsManagerInterface
{
    /**
     * @var string
     */
    protected const SECRET_KEY_PREFIX = 'tenant_key';

    /**
     * @var string
     */
    protected const SECRET_KEY_TAG_KEY = 'type';

    /**
     * @var string
     */
    protected const SECRET_KEY_TAG_VALUE = 'tenant_key';

    /**
     * @var \Spryker\Client\SecretsManager\SecretsManagerClientInterface
     */
    protected $secretsManagerClient;

    /**
     * @var \Spryker\Service\UtilText\UtilTextServiceInterface
     */
    protected $utilTextService;

    /**
     * @param \Spryker\Client\SecretsManager\SecretsManagerClientInterface $secretsManagerClient
     * @param \Spryker\Service\UtilText\UtilTextServiceInterface $utilTextService
     */
    public function __construct(
        SecretsManagerClientInterface $secretsManagerClient,
        UtilTextServiceInterface $utilTextService
    ) {
        $this->secretsManagerClient = $secretsManagerClient;
        $this->utilTextService = $utilTextService;
    }

    /**
     * @inheritDoc
     */
    public function getPassphrase(string $tenantIdentifier): ?string
    {
        $secretKeyTransfer = (new SecretKeyTransfer())
            ->setPrefix(static::SECRET_KEY_PREFIX)
            ->setIdentifier($tenantIdentifier);
        $secretTransfer = (new SecretTransfer())
            ->setSecretKey($secretKeyTransfer);

        $secretTransfer = $this->secretsManagerClient->getSecret($secretTransfer);
        $passphrase = $secretTransfer->getValue();

        if (!$passphrase) {
            $secretTransfer = $this->createSecret($secretTransfer, $tenantIdentifier);
        }

        return $secretTransfer->getValue();
    }

    /**
     * @param \Generated\Shared\Transfer\SecretTransfer $secretTransfer
     * @param string $tenantIdentifier
     *
     * @throws \Pyz\Zed\VertexConfig\Business\Exception\StoreSecretNotCreatedException
     *
     * @return \Generated\Shared\Transfer\SecretTransfer
     */
    public function createSecret(SecretTransfer $secretTransfer, string $tenantIdentifier): SecretTransfer
    {
        $passphrase = $this->utilTextService->generateRandomString(128);

        $secretTagTransfer = (new SecretTagTransfer())
            ->setKey(static::SECRET_KEY_TAG_KEY)
            ->setValue(static::SECRET_KEY_TAG_VALUE);
        $secretTransfer->setValue($passphrase)
            ->addSecretTag($secretTagTransfer);

        $isSuccessful = $this->secretsManagerClient->createSecret($secretTransfer);

        if (!$isSuccessful) {
            throw new StoreSecretNotCreatedException(
                sprintf('The secret passphrase was not created for the store: %s', $tenantIdentifier),
            );
        }

        return $secretTransfer;
    }
}
