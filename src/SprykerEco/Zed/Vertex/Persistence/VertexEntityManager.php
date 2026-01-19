<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Orm\Zed\Vertex\Persistence\SpyVertexConfig;
use Orm\Zed\Vertex\Persistence\SpyTaxIdValidationHistory;
use Propel\Runtime\Collection\Collection;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

/**
 * @method \Spryker\Zed\Vertex\Persistence\VertexPersistenceFactory getFactory()
 */
class VertexEntityManager extends AbstractEntityManager implements VertexEntityManagerInterface
{
    use ActiveRecordBatchProcessorTrait;

    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     *
     * @return void
     */
    public function saveTaxIdValidationHistory(TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer): void
    {
        $taxIdValidationHistoryEntity = new SpyTaxIdValidationHistory();
        $this->getFactory()
            ->createVertexConfigMapper()
            ->mapTaxIdValidationHistoryTransferToTaxIdValidationHistoryEntity($taxIdValidationHistoryTransfer, $taxIdValidationHistoryEntity);

        $taxIdValidationHistoryEntity->save();
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     * @param array<\Generated\Shared\Transfer\StoreTransfer> $storeTransfers
     *
     * @return void
     */
    public function saveVertexConfig(
        VertexConfigTransfer $VertexConfigTransfer,
        array $storeTransfers
    ): void {
        foreach ($storeTransfers as $storeTransfer) {
            $VertexConfigTransfer->requireApiUrls()->requireApplicationId()->requireVendorCode();
            $VertexConfigEntityCollection = $this->getVertexConfigEntityCollectionByVertexConfigAndStore($VertexConfigTransfer, $storeTransfer);

            if ($VertexConfigEntityCollection->count() === 0) {
                $VertexConfigEntity = new SpyVertexConfig();
                $VertexConfigEntity = $this->getFactory()
                    ->createVertexConfigMapper()
                    ->mapVertexConfigTransferToVertexConfigEntity($VertexConfigTransfer, $VertexConfigEntity);

                $VertexConfigEntity->setFkStore($storeTransfer->getIdStore());

                $VertexConfigEntity->save();

                continue;
            }

            foreach ($VertexConfigEntityCollection as $VertexConfigEntity) {
                $VertexConfigEntity = $this->getFactory()
                    ->createVertexConfigMapper()
                    ->mapVertexConfigTransferToVertexConfigEntity($VertexConfigTransfer, $VertexConfigEntity);

                $VertexConfigEntity->setFkStore($storeTransfer->getIdStore());

                $this->persist($VertexConfigEntity);
            }

            $this->commit();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return void
     */
    public function deleteVertexConfig(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): void
    {
        $VertexConfigCriteriaTransfer->getVertexConfigConditionsOrFail()->requireVendorCodes();

        $VertexConfigEntityCollection = $this->getVertexConfigEntityCollectionByVertexConfigCriteria($VertexConfigCriteriaTransfer);

        foreach ($VertexConfigEntityCollection as $VertexConfigEntity) {
            $VertexConfigEntity->delete();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return \Propel\Runtime\Collection\Collection
     */
    protected function getVertexConfigEntityCollectionByVertexConfigAndStore(
        VertexConfigTransfer $VertexConfigTransfer,
        StoreTransfer $storeTransfer
    ): Collection {
        if ($storeTransfer->getIdStore() === null) {
            return $this->getFactory()
                ->createVertexConfigQuery()
                ->filterByVendorCode($VertexConfigTransfer->getVendorCode())
                ->find();
        }

        return $this->getFactory()
            ->createVertexConfigQuery()
            ->filterByFkStore($storeTransfer->getIdStoreOrFail())
            ->filterByVendorCode($VertexConfigTransfer->getVendorCode())
            ->find();
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return \Propel\Runtime\Collection\Collection
     */
    protected function getVertexConfigEntityCollectionByVertexConfigCriteria(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): Collection
    {
        $VertexConfigConditionTransfer = $VertexConfigCriteriaTransfer->getVertexConfigConditionsOrFail();

        return $this->getFactory()
            ->createVertexConfigQuery()
            ->filterByVendorCode_In($VertexConfigConditionTransfer->getVendorCodes())
            ->find();
    }
}
