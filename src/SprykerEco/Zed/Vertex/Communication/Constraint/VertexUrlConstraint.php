<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Constraint;

use Symfony\Component\Validator\Constraint;

class VertexUrlConstraint extends Constraint
{
    public string $message = 'This value is not a valid URL.';

    public function validatedBy(): string
    {
        return VertexUrlConstraintValidator::class;
    }
}
