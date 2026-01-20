<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Authenticator;

use DateInterval;
use DateTime;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexApiAuthResponseTransfer;
use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Pyz\Zed\VertexApi\Business\Api\V2\Client\SecurityApiInterface;
use Pyz\Zed\VertexApi\Persistence\VertexApiEntityManagerInterface;

class VertexApiAuthenticator implements VertexApiAuthenticatorInterface
{
    protected SecurityApiInterface $securityApi;

    protected VertexApiEntityManagerInterface $vertexApiEntityManager;

    /**
     * @param \Pyz\Zed\VertexApi\Business\Api\V2\Client\SecurityApiInterface $securityApi
     * @param \Pyz\Zed\VertexApi\Persistence\VertexApiEntityManagerInterface $vertexApiEntityManager
     */
    public function __construct(
        SecurityApiInterface $securityApi,
        VertexApiEntityManagerInterface $vertexApiEntityManager
    ) {
        $this->securityApi = $securityApi;
        $this->vertexApiEntityManager = $vertexApiEntityManager;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAuthResponseTransfer
     */
    public function authenticate(VertexConfigTransfer $vertexConfigTransfer): VertexApiAuthResponseTransfer
    {
        $vertexApiCredentialTransfer = (new VertexApiCredentialTransfer())
            ->fromArray($vertexConfigTransfer->toArray(), true);

        $vertexApiAuthResponseTransfer = $this->securityApi->requestAccessToken($vertexApiCredentialTransfer);

        if ($vertexApiAuthResponseTransfer->getErrors() !== []) {
            return $vertexApiAuthResponseTransfer;
        }

        $vertexApiAccessTokenTransfer = (new VertexApiAccessTokenTransfer())
            ->setCredentialHash($vertexConfigTransfer->getCredentialHash())
            ->setAccessToken($vertexApiAuthResponseTransfer->getAccessToken())
            ->setExpirationDate($this->calculateExpirationDate($vertexApiAuthResponseTransfer->getExpiresInOrFail()));

        $this->vertexApiEntityManager->saveAccessToken($vertexApiAccessTokenTransfer);

        return $vertexApiAuthResponseTransfer;
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
