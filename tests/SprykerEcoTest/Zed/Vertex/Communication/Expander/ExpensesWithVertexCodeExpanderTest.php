<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex\Communication\Expander;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use SprykerEco\Zed\Vertex\Communication\Expander\ExpensesWithVertexCodeExpander;
use SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Communication
 * @group Expander
 * @group ExpensesWithVertexCodeExpanderTest
 * Add your own group annotations below this line
 */
class ExpensesWithVertexCodeExpanderTest extends Unit
{
    /**
     * @var string
     */
    protected const EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';

    /**
     * @var string
     */
    protected const EXPENSE_NAME = 'Standard Shipping';

    /**
     * @var string
     */
    protected const MERCHANT_REFERENCE = 'MER000001';

    /**
     * @var string
     */
    protected const PRODUCT_CLASS_CODE = 'VPCC_1';

    /**
     * @var array<string>
     */
    protected array $requestedProductClassCodeKeys = [];

    /**
     * @return void
     */
    public function testExpandBuildsExpenseKeyWithoutTrailingSeparatorWhenMerchantReferenceIsNotSet(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->createCalculableObjectTransferWithExpense(null);

        // Act
        (new ExpensesWithVertexCodeExpander($this->createVertexCodeMapperStub()))->expand($calculableObjectTransfer);

        // Assert
        $this->assertSame(
            [static::EXPENSE_TYPE . '|' . static::EXPENSE_NAME],
            $this->requestedProductClassCodeKeys,
        );
    }

    /**
     * @return void
     */
    public function testExpandBuildsExpenseKeyWithoutTrailingSeparatorWhenMerchantReferenceIsEmptyString(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->createCalculableObjectTransferWithExpense('');

        // Act
        (new ExpensesWithVertexCodeExpander($this->createVertexCodeMapperStub()))->expand($calculableObjectTransfer);

        // Assert
        $this->assertSame(
            [static::EXPENSE_TYPE . '|' . static::EXPENSE_NAME],
            $this->requestedProductClassCodeKeys,
        );
    }

    /**
     * @return void
     */
    public function testExpandBuildsExpenseKeyWithMerchantReferenceWhenItIsSet(): void
    {
        // Arrange
        $this->skipTestIfMerchantReferenceIsNotAvailable();

        $calculableObjectTransfer = $this->createCalculableObjectTransferWithExpense(static::MERCHANT_REFERENCE);

        // Act
        (new ExpensesWithVertexCodeExpander($this->createVertexCodeMapperStub()))->expand($calculableObjectTransfer);

        // Assert
        $this->assertSame(
            [static::EXPENSE_TYPE . '|' . static::EXPENSE_NAME . '|' . static::MERCHANT_REFERENCE],
            $this->requestedProductClassCodeKeys,
        );
    }

    /**
     * @return void
     */
    public function testExpandBuildsExpenseKeyWhenMerchantReferencePropertyIsNotDeclared(): void
    {
        // Arrange
        $this->skipTestIfMerchantReferenceIsAvailable();

        $calculableObjectTransfer = $this->createCalculableObjectTransferWithExpense(null);

        // Act
        (new ExpensesWithVertexCodeExpander($this->createVertexCodeMapperStub()))->expand($calculableObjectTransfer);

        // Assert
        $this->assertSame(
            [static::EXPENSE_TYPE . '|' . static::EXPENSE_NAME],
            $this->requestedProductClassCodeKeys,
        );
    }

    /**
     * @return void
     */
    public function testExpandSetsProductClassCodeToExpenseTaxMetadata(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->createCalculableObjectTransferWithExpense(null);

        // Act
        $calculableObjectTransfer = (new ExpensesWithVertexCodeExpander($this->createVertexCodeMapperStub()))
            ->expand($calculableObjectTransfer);

        // Assert
        $expenseTransfer = $calculableObjectTransfer->getExpenses()->offsetGet(0);

        $this->assertSame(
            ['productClass' => static::PRODUCT_CLASS_CODE],
            $expenseTransfer->getTaxMetadataOrFail()->getProduct(),
        );
    }

    /**
     * @param string|null $merchantReference
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    protected function createCalculableObjectTransferWithExpense(?string $merchantReference): CalculableObjectTransfer
    {
        $expenseTransfer = (new ExpenseTransfer())
            ->setType(static::EXPENSE_TYPE)
            ->setName(static::EXPENSE_NAME);

        if ($merchantReference !== null && method_exists($expenseTransfer, 'setMerchantReference')) {
            $expenseTransfer->setMerchantReference($merchantReference);
        }

        return (new CalculableObjectTransfer())->addExpense($expenseTransfer);
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper
     */
    protected function createVertexCodeMapperStub(): VertexCodeMapper
    {
        $this->requestedProductClassCodeKeys = [];

        return Stub::makeEmpty(VertexCodeMapper::class, [
            'getProductClassCode' => function (?string $key): string {
                $this->requestedProductClassCodeKeys[] = $key;

                return static::PRODUCT_CLASS_CODE;
            },
        ]);
    }

    /**
     * @return void
     */
    protected function skipTestIfMerchantReferenceIsAvailable(): void
    {
        if (!method_exists(new ExpenseTransfer(), 'getMerchantReference')) {
            return;
        }

        $this->markTestSkipped(
            'ExpenseTransfer.merchantReference is declared by an installed Marketplace module.',
        );
    }

    /**
     * @return void
     */
    protected function skipTestIfMerchantReferenceIsNotAvailable(): void
    {
        if (method_exists(new ExpenseTransfer(), 'setMerchantReference')) {
            return;
        }

        $this->markTestSkipped(
            'ExpenseTransfer.merchantReference is only declared when a Marketplace module is installed.',
        );
    }
}
