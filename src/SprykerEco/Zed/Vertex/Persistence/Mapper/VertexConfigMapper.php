<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence\Mapper;

use Generated\Shared\Transfer\VertexApiUrlsTransfer;
use Generated\Shared\Transfer\VertexConfigCollectionTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Orm\Zed\Vertex\Persistence\SpyVertexConfig;
use Orm\Zed\Vertex\Persistence\SpyTaxIdValidationHistory;
use Propel\Runtime\Collection\Collection;
use Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface;

class VertexConfigMapper
{
    /**
     * @var \Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface
     */
    protected VertexToUtilEncodingServiceInterface $utilEncodingService;

    /**
     * @param \Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(VertexToUtilEncodingServiceInterface $utilEncodingService)
    {
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     * @param \Orm\Zed\Vertex\Persistence\SpyVertexConfig $VertexConfigEntity
     *
     * @return \Orm\Zed\Vertex\Persistence\SpyVertexConfig
     */
    public function mapVertexConfigTransferToVertexConfigEntity(
        VertexConfigTransfer $VertexConfigTransfer,
        SpyVertexConfig $VertexConfigEntity
    ): SpyVertexConfig {
        $VertexApiUrlsJson = $this->utilEncodingService->encodeJson($VertexConfigTransfer->getApiUrlsOrFail()->toArray());
        $VertexConfigTransfer = $VertexConfigTransfer->toArray();
        unset($VertexConfigTransfer['api_urls']);

        $VertexConfigEntity = $VertexConfigEntity->fromArray($VertexConfigTransfer);
        $VertexConfigEntity->setApiUrls($VertexApiUrlsJson ?? '');

        return $VertexConfigEntity;
    }

    /**
     * @param \Orm\Zed\Vertex\Persistence\SpyVertexConfig $spyVertexConfigTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexConfigTransfer
     */
    public function mapVertexConfigEntityToVertexConfigTransfer(
        SpyVertexConfig $spyVertexConfigTransfer,
        VertexConfigTransfer $VertexConfigTransfer
    ): VertexConfigTransfer {
        $VertexApiUrlsArray = $this->utilEncodingService->decodeJson($spyVertexConfigTransfer->getApiUrls(), true);
        $VertexApiUrlsTransfer = (new VertexApiUrlsTransfer())->fromArray((array)($VertexApiUrlsArray ?? []), true);

        $spyVertexConfigTransfer = $spyVertexConfigTransfer->toArray();
        unset($spyVertexConfigTransfer['api_urls']);

        $VertexConfigTransfer = $VertexConfigTransfer->fromArray($spyVertexConfigTransfer, true);
        $VertexConfigTransfer->setApiUrls($VertexApiUrlsTransfer);

        return $VertexConfigTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection $VertexConfigEntities
     * @param \Generated\Shared\Transfer\VertexConfigCollectionTransfer $VertexConfigCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\VertexConfigCollectionTransfer
     */
    public function mapVertexConfigEntitiesToVertexConfigCollectionTransfer(
        Collection $VertexConfigEntities,
        VertexConfigCollectionTransfer $VertexConfigCollectionTransfer
    ): VertexConfigCollectionTransfer {
        foreach ($VertexConfigEntities as $VertexConfigEntity) {
            $VertexConfigCollectionTransfer->addVertexConfig(
                $this->mapVertexConfigEntityToVertexConfigTransfer($VertexConfigEntity, new VertexConfigTransfer()),
            );
        }

        return $VertexConfigCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     * @param \Orm\Zed\Vertex\Persistence\SpyTaxIdValidationHistory $taxIdValidationHistoryEntity
     *
     * @return \Orm\Zed\Vertex\Persistence\SpyTaxIdValidationHistory
     */
    public function mapTaxIdValidationHistoryTransferToTaxIdValidationHistoryEntity(
        TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer,
        SpyTaxIdValidationHistory $taxIdValidationHistoryEntity
    ): SpyTaxIdValidationHistory {
        return $taxIdValidationHistoryEntity->fromArray($taxIdValidationHistoryTransfer->toArray());
    }
}
