<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Exception;

use Exception;
use Throwable;

class VertexConfigurationCouldNotBeDeleted extends Exception
{
    /**
     * @var string
     */
    protected const EXCEPTION_MESSAGE = 'Vertex configuration could not be deleted.';

    /**
     * @var string
     */
    protected const EXCEPTION_MESSAGE_ADDITIONAL_MESSAGE_TEMPLATE = self::EXCEPTION_MESSAGE . ' Details: %s';

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = self::EXCEPTION_MESSAGE, int $code = 0, ?Throwable $previous = null)
    {
        if ($message) {
            $message = sprintf(static::EXCEPTION_MESSAGE_ADDITIONAL_MESSAGE_TEMPLATE, $message);
        }

        parent::__construct($message, $code, $previous);
    }
}
