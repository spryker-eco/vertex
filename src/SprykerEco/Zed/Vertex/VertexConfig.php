<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex;

use Spryker\Shared\Vertex\VertexConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class VertexConfig extends AbstractBundleConfig
{
    protected string const CLIENT_ID = 'VERTEX:CLIENT_ID';

    protected string const CLIENT_SECRET = 'VERTEX:CLIENT_SECRET';

    protected string const SECURITY_URI = 'VERTEX:SECURITY_URI';

    protected string const TRANSACTION_CALLS_URI = 'VERTEX:TRANSACTION_CALLS_URI';

    public function getClientId(): string
    {
        return $this->get(static::CLIENT_ID, null);
    }

    public function getClientSecret(): string
    {
        return $this->get(static::CLIENT_SECRET, null);
    }

    public function getSecurityUri(): string
    {
        return $this->get(static::SECURITY_URI, null);
    }

    public function getTransactionCallsUri(): string
    {
        return $this->get(static::TRANSACTION_CALLS_URI, null);
    }
}
