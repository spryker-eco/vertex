<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Constraint;

use Symfony\Component\Validator\Constraint;

class VertexConfigurationRemovalConstraint extends Constraint
{
    public string $message = 'Vertex is selected as the tax provider for {{ scope }}. Please resolve the following before saving: {{ reasons }} Alternatively, disable Vertex under Taxes → Tax Provider first.';

    public string $crossScopeMessage = 'This change to the global configuration would break {{ scope }}, which has Vertex selected as the tax provider and inherits it: {{ reasons }} Please configure Vertex directly for {{ scope }}, or disable Vertex there first.';

    public function validatedBy(): string
    {
        return VertexConfigurationRemovalConstraintValidator::class;
    }
}
