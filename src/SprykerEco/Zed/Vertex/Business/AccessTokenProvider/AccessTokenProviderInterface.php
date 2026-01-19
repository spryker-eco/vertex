<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\AccessTokenProvider;

interface AccessTokenProviderInterface
{
    /**
     * @return string
     */
    public function getAccessToken(): string;
}
