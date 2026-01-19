<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\VertexConfigCollectionTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\Vertex\Persistence\VertexPersistenceFactory getFactory()
 */
class VertexRepository extends AbstractRepository implements VertexRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\VertexConfigCollectionTransfer
     */
    public function getVertexConfigCollection(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): VertexConfigCollectionTransfer
    {
        $VertexCollectionTransfer = new VertexConfigCollectionTransfer();
        $VertexConfigQuery = $this->getFactory()->createVertexConfigQuery();
        $VertexConfigQuery = $this->applyVertexConfigFilters($VertexConfigQuery, $VertexConfigCriteriaTransfer);

        $paginationTransfer = $VertexConfigCriteriaTransfer->getPagination();

        if ($paginationTransfer) {
            $VertexConfigQuery = $this->applyVertexConfigPagination($VertexConfigQuery, $paginationTransfer);
        }

        $hasSortCollection = count($VertexConfigCriteriaTransfer->getSortCollection());
        if ($hasSortCollection) {
            $VertexConfigQuery = $this->applyVertexConfigSorting($VertexConfigQuery, $VertexConfigCriteriaTransfer);
        }

        $VertexConfigEntities = $VertexConfigQuery->find();

        return $this->getFactory()->createVertexConfigMapper()
            ->mapVertexConfigEntitiesToVertexConfigCollectionTransfer($VertexConfigEntities, $VertexCollectionTransfer);
    }

    /**
     * @param \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery $VertexConfigQuery
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery
     */
    protected function applyVertexConfigFilters(
        SpyVertexConfigQuery $VertexConfigQuery,
        VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
    ): SpyVertexConfigQuery {
        if (!$VertexConfigCriteriaTransfer->getVertexConfigConditions()) {
            return $VertexConfigQuery;
        }

        if ($VertexConfigCriteriaTransfer->getVertexConfigConditions()->getFkStores()) {
            $VertexConfigQuery->filterByFkStore_In($VertexConfigCriteriaTransfer->getVertexConfigConditions()->getFkStores());
        }

        if ($VertexConfigCriteriaTransfer->getVertexConfigConditions()->getVendorCodes()) {
            $VertexConfigQuery->filterByVendorCode_In($VertexConfigCriteriaTransfer->getVertexConfigConditions()->getVendorCodes());
        }

        if ($VertexConfigCriteriaTransfer->getVertexConfigConditions()->getApplicationIds()) {
            $VertexConfigQuery->filterByIdVertexConfig_In($VertexConfigCriteriaTransfer->getVertexConfigConditions()->getApplicationIds());
        }

        return $VertexConfigQuery;
    }

    /**
     * @param \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery $VertexConfigQuery
     * @param \Generated\Shared\Transfer\PaginationTransfer $paginationTransfer
     *
     * @return \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery
     */
    protected function applyVertexConfigPagination(
        SpyVertexConfigQuery $VertexConfigQuery,
        PaginationTransfer $paginationTransfer
    ): SpyVertexConfigQuery {
        $paginationTransfer->setNbResults($VertexConfigQuery->count());

        if ($paginationTransfer->getLimit() !== null && $paginationTransfer->getOffset() !== null) {
            return $VertexConfigQuery
                ->limit($paginationTransfer->getLimit())
                ->offset($paginationTransfer->getOffset());
        }

        return $VertexConfigQuery;
    }

    /**
     * @param \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery $VertexConfigQuery
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery
     */
    protected function applyVertexConfigSorting(
        SpyVertexConfigQuery $VertexConfigQuery,
        VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
    ): SpyVertexConfigQuery {
        $sortCollection = $VertexConfigCriteriaTransfer->getSortCollection();
        foreach ($sortCollection as $sortTransfer) {
            $VertexConfigQuery->orderBy(
                $sortTransfer->getFieldOrFail(),
                $sortTransfer->getIsAscending() ? Criteria::ASC : Criteria::DESC,
            );
        }

        return $VertexConfigQuery;
    }
}
