<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Plugin\MessageBroker;

use Generated\Shared\Transfer\ConfigureVertexTransfer;
use Generated\Shared\Transfer\DeleteVertexTransfer;
use Generated\Shared\Transfer\VertexConfigConditionsTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\MessageBrokerExtension\Dependency\Plugin\MessageHandlerPluginInterface;

/**
 * @method \Spryker\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \Spryker\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 */
class VertexMessageHandlerPlugin extends AbstractPlugin implements MessageHandlerPluginInterface
{
    /**
     * {@inheritDoc}
     * - Handles `ConfigureVertex` message by saving given tax app config to the database.
     * - Maps `MessageAttributes`'s `apiUrl`, `isActive`, `vendorCode` and `tenantIdentifier` to the corresponding `VertexConfig`'s properties.
     * - Maps `MessageAttributes.actorId` to `VertexConfig.applicationId` if it is not null, otherwise use `MessageAttributes.emitter`.
     * - Executes {@link \Spryker\Zed\Vertex\Business\VertexFacadeInterface::saveVertexConfig()} method with mapped `VertexConfig`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ConfigureVertexTransfer $configureVertexTransfer
     *
     * @return void
     */
    public function onVertexConfigured(ConfigureVertexTransfer $configureVertexTransfer): void
    {
        $messageAttributesTransfer = $configureVertexTransfer->getMessageAttributesOrFail();

        $VertexConfigTransfer = (new VertexConfigTransfer())
            ->setApplicationId($messageAttributesTransfer->getEmitter())
            ->setApiUrls($configureVertexTransfer->getApiUrlsOrFail())
            ->setIsActive($configureVertexTransfer->getIsActiveOrFail())
            ->setVendorCode($configureVertexTransfer->getVendorCodeOrFail())
            ->setTenantIdentifier($messageAttributesTransfer->getTenantIdentifier());

        if ($messageAttributesTransfer->getEmitter() === null || $messageAttributesTransfer->getActorId() !== null) {
            $VertexConfigTransfer->setApplicationId($messageAttributesTransfer->getActorIdOrFail());
        }

        $this->getFacade()->saveVertexConfig($VertexConfigTransfer);
    }

    /**
     * {@inheritDoc}
     *  - Handles `DeleteVertex` message by saving given tax app config to the database.
     *  - Maps `MessageAttributes.vendorCode` to `VertexConfigCriteria.VertexConfigConditions.vendorCode`.
     *  - Executes {@link \Spryker\Zed\Vertex\Business\VertexFacadeInterface::deleteVertexConfig()} method with mapped `VertexConfig`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\DeleteVertexTransfer $deleteVertexTransfer
     *
     * @return void
     */
    public function onVertexDeleted(DeleteVertexTransfer $deleteVertexTransfer): void
    {
        $VertexConditionsTransfer = (new VertexConfigConditionsTransfer());
        $messageAttributesTransfer = $deleteVertexTransfer->getMessageAttributesOrFail();

        $VertexConditionsTransfer->addVendorCode($deleteVertexTransfer->getVendorCodeOrFail());

        if ($messageAttributesTransfer->getActorId() !== null) {
            $VertexConditionsTransfer->addApplicationId($messageAttributesTransfer->getActorIdOrFail());
        }

        $VertexConfigCriteria = (new VertexConfigCriteriaTransfer())->setVertexConfigConditions($VertexConditionsTransfer);

        $this->getFacade()->deleteVertexConfig($VertexConfigCriteria);
    }

    /**
     * {@inheritDoc}
     * - Adds new tax app endpoint to the config for the store.
     * - Returns an array where the key is the class name to be handled and the value is the callable that handles the message.
     *
     * @api
     *
     * @return array<string, callable>
     */
    public function handles(): iterable
    {
        return [
            ConfigureVertexTransfer::class => [$this, 'onVertexConfigured'],
            DeleteVertexTransfer::class => [$this, 'onVertexDeleted'],
        ];
    }
}
