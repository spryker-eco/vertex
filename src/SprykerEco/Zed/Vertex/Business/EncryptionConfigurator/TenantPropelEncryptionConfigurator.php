<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Business\EncryptionConfigurator;

use Closure;
use Exception;
use Spryker\PropelEncryptionBehavior\Cipher;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\SecretsManager\SecretsManagerInterface;

class TenantPropelEncryptionConfigurator implements TenantPropelEncryptionConfiguratorInterface
{
    use LoggerTrait;

    protected SecretsManagerInterface $secretsManager;

    /**
     * @param \SprykerEco\Zed\Vertex\Business\SecretsManager\SecretsManagerInterface $secretsManager
     */
    public function __construct(SecretsManagerInterface $secretsManager)
    {
        $this->secretsManager = $secretsManager;
    }

    /**
     * @param string $tenantIdentifier
     *
     * @return bool
     */
    public function configurePropelEncryption(string $tenantIdentifier): bool
    {
        $passphrase = $this->secretsManager->getPassphrase($tenantIdentifier);

        if (!$passphrase) {
            $this->getLogger()->error(sprintf(
                'The secret passphrase was not found using $tenantIdentifier: `%s`.',
                $tenantIdentifier,
            ));

            Cipher::resetInstance();

            return false;
        }

        Cipher::resetInstance();
        Cipher::createInstance($passphrase);

        return true;
    }

    /**
     * @codeCoverageIgnore Too complex to test a singleton.
     *
     * @inheritDoc
     */
    public function withCurrentOrEmptyEncryptionKey(Closure $callback)
    {
        try {
            Cipher::getInstance();
            $reset = false;
        } catch (Exception $exception) {
            // if no cipher is instantiated, create an empty one to allow reading unencrypted values as-is
            Cipher::createInstance('');
            $reset = true;
        }

        $callbackResult = $callback();

        if ($reset) {
            Cipher::resetInstance();
        }

        return $callbackResult;
    }
}
