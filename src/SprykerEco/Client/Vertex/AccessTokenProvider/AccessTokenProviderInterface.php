<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\AccessTokenProvider;

use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;

interface AccessTokenProviderInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function provideVertexAccessToken(VertexConfigTransfer $vertexConfigTransfer): VertexApiAccessTokenTransfer;
}
