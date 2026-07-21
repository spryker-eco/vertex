<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Constraint;

use Symfony\Component\Validator\Constraint;

class VertexTaxProviderConstraint extends Constraint
{
    public string $message = 'Cannot enable Vertex as the tax provider without a complete Vertex configuration for the selected scope. Please resolve the following under Integrations → Vertex before saving: {{ reasons }}';

    public function validatedBy(): string
    {
        return VertexTaxProviderConstraintValidator::class;
    }
}
