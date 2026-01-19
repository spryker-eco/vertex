<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\AccessTokenProvider;

use Generated\Shared\Transfer\AccessTokenRequestOptionsTransfer;
use Generated\Shared\Transfer\AccessTokenRequestTransfer;
use Spryker\Zed\Vertex\Business\Exception\AccessTokenNotFoundException;
use Spryker\Zed\Vertex\Dependency\Facade\VertexToOauthClientFacadeInterface;
use Spryker\Zed\Vertex\VertexConfig;

class AccessTokenProvider implements AccessTokenProviderInterface
{
    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToOauthClientFacadeInterface
     */
    protected VertexToOauthClientFacadeInterface $oauthClientFacade;

    /**
     * @var \Spryker\Zed\Vertex\VertexConfig
     */
    protected VertexConfig $VertexConfig;

    /**
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToOauthClientFacadeInterface $oauthClientFacade
     * @param \Spryker\Zed\Vertex\VertexConfig $VertexConfig
     */
    public function __construct(
        VertexToOauthClientFacadeInterface $oauthClientFacade,
        VertexConfig $VertexConfig
    ) {
        $this->oauthClientFacade = $oauthClientFacade;
        $this->VertexConfig = $VertexConfig;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        $accessTokenRequestOptionsTransfer = (new AccessTokenRequestOptionsTransfer())
            ->setAudience($this->VertexConfig->getOauthOptionAudienceForTaxCalculation());

        $accessTokenRequestTransfer = (new AccessTokenRequestTransfer())
            ->setGrantType($this->VertexConfig->getOauthGrantTypeForTaxCalculation())
            ->setProviderName($this->VertexConfig->getOauthProviderNameForTaxCalculation())
            ->setAccessTokenRequestOptions($accessTokenRequestOptionsTransfer);

        return $this->getAuthorizationValue($accessTokenRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\AccessTokenRequestTransfer $accessTokenRequestTransfer
     *
     * @throws \Spryker\Zed\Vertex\Business\Exception\AccessTokenNotFoundException
     *
     * @return string
     */
    protected function getAuthorizationValue(AccessTokenRequestTransfer $accessTokenRequestTransfer): string
    {
        $accessTokenResponseTransfer = $this->oauthClientFacade->getAccessToken($accessTokenRequestTransfer);

        if (!$accessTokenResponseTransfer->getIsSuccessful()) {
            throw new AccessTokenNotFoundException(
                $accessTokenResponseTransfer->getAccessTokenErrorOrFail()->getErrorOrFail(),
            );
        }

        return sprintf('Bearer %s', $accessTokenResponseTransfer->getAccessToken());
    }
}
