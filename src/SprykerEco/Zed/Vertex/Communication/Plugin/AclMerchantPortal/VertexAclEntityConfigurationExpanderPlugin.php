<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Plugin\AclMerchantPortal;

use Generated\Shared\Transfer\AclEntityMetadataConfigTransfer;
use Generated\Shared\Transfer\AclEntityMetadataTransfer;
use Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\AclEntityConfigurationExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * The Vertex tables hold integration state of the tax provider itself - an API access token cache
 * and an append-only tax ID validation log. They are not owned by any merchant, so they carry no
 * ACL entity rules and fall through to the default ACL scope, where writes are denied unless the
 * entity declares a default global operation mask. Without this plugin every Vertex-backed
 * calculation performed by a non-root user - a Merchant Portal refund, for example - fails with
 * `OperationNotAuthorizedException` the moment the access token has to be persisted.
 *
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 */
class VertexAclEntityConfigurationExpanderPlugin extends AbstractPlugin implements AclEntityConfigurationExpanderPluginInterface
{
    /**
     * @see \Spryker\Shared\AclEntity\AclEntityConstants::OPERATION_MASK_READ
     */
    protected const int OPERATION_MASK_READ = 0b1;

    /**
     * @see \Spryker\Shared\AclEntity\AclEntityConstants::OPERATION_MASK_CREATE
     */
    protected const int OPERATION_MASK_CREATE = 0b10;

    /**
     * @see \Spryker\Shared\AclEntity\AclEntityConstants::OPERATION_MASK_UPDATE
     */
    protected const int OPERATION_MASK_UPDATE = 0b100;

    protected const string ENTITY_VERTEX_API_ACCESS_TOKEN = 'Orm\Zed\Vertex\Persistence\SpyVertexApiAccessToken';

    protected const string ENTITY_VERTEX_TAX_ID_VALIDATION_HISTORY = 'Orm\Zed\Vertex\Persistence\SpyVertexTaxIdValidationHistory';

    /**
     * {@inheritDoc}
     * - Expands provided `AclEntityMetadataConfig` transfer object with Vertex API access token metadata.
     * - Expands provided `AclEntityMetadataConfig` transfer object with Vertex tax ID validation history metadata.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\AclEntityMetadataConfigTransfer $aclEntityMetadataConfigTransfer
     *
     * @return \Generated\Shared\Transfer\AclEntityMetadataConfigTransfer
     */
    public function expand(AclEntityMetadataConfigTransfer $aclEntityMetadataConfigTransfer): AclEntityMetadataConfigTransfer
    {
        $aclEntityMetadataConfigTransfer
            ->getAclEntityMetadataCollectionOrFail()
            ->addAclEntityMetadata(
                static::ENTITY_VERTEX_API_ACCESS_TOKEN,
                (new AclEntityMetadataTransfer())
                    ->setEntityName(static::ENTITY_VERTEX_API_ACCESS_TOKEN)
                    ->setDefaultGlobalOperationMask(
                        static::OPERATION_MASK_READ
                            | static::OPERATION_MASK_CREATE
                            | static::OPERATION_MASK_UPDATE,
                    ),
            )
            ->addAclEntityMetadata(
                static::ENTITY_VERTEX_TAX_ID_VALIDATION_HISTORY,
                (new AclEntityMetadataTransfer())
                    ->setEntityName(static::ENTITY_VERTEX_TAX_ID_VALIDATION_HISTORY)
                    ->setDefaultGlobalOperationMask(
                        static::OPERATION_MASK_READ
                            | static::OPERATION_MASK_CREATE,
                    ),
            );

        return $aclEntityMetadataConfigTransfer;
    }
}
