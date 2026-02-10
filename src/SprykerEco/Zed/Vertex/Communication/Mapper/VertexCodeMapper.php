<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Mapper;

class VertexCodeMapper
{
    /**
     * @var array<string>
     */
    protected $customerCodes = [
        0 => 'VCCC_0',
        1 => 'VCCC_1',
        2 => 'VCCC_2',
        3 => 'VCCC_3',
        4 => 'VCCC_4',
        5 => 'VCCC_5',
        6 => 'VCCC_6',
        7 => 'VCCC_7',
        8 => 'VCCC_8',
        9 => 'VCCC_9',
    ];

    /**
     * @var array<string>
     */
    protected array $productCodes = [
        0 => 'VPCC_0',
        1 => 'VPCC_1',
        2 => 'VPCC_2',
        3 => 'VPCC_3',
        4 => 'VPCC_4',
        5 => 'VPCC_5',
        6 => 'VPCC_6',
        7 => 'VPCC_7',
        8 => 'VPCC_8',
        9 => 'VPCC_9',
    ];

    /**
     * @var array<string>
     */
    protected array $productOptionCodes = [
        0 => 'VPCC_0',
        1 => 'VPCC_1',
        2 => 'VPCC_2',
        3 => 'VPCC_3',
        4 => 'VPCC_4',
        5 => 'VPCC_5',
        6 => 'VPCC_6',
        7 => 'VPCC_7',
        8 => 'VPCC_8',
        9 => 'VPCC_9',
    ];

    /**
     * @var array<string>
     */
    protected array $expenseCodes = [
        0 => 'VPCC_0',
        1 => 'VPCC_1',
        2 => 'VPCC_2',
        3 => 'VPCC_3',
        4 => 'VPCC_4',
        5 => 'VPCC_5',
        6 => 'VPCC_6',
        7 => 'VPCC_7',
        8 => 'VPCC_8',
        9 => 'VPCC_9',
    ];

    /**
     * @var array<int, string>
     */
    protected $exemptionsCertificates = [
        0 => 'Vertex Exemption Certificate',
        1 => '',
    ];

    public function __construct()
    {
        $this->loadFixtures();
    }

    /**
     * @return void
     */
    protected function loadFixtures(): void
    {
        $fixturesPath = __DIR__ . '/../../../../../../../../data/import/vertex_codes.json';

        if (!is_file($fixturesPath) || !is_readable($fixturesPath)) {
            return;
        }

        $fixtures = file_get_contents($fixturesPath);
        
        if (!$fixtures) {
            return;
        }

        $fixturesData = json_decode($fixtures, true);
        if (!is_array($fixturesData)) {
            return;
        }

        foreach (array_keys($fixturesData) as $key) {
            $this->importFixtureData($fixturesData, (string)$key);
        }
    }

    /**
     * @param array<mixed> $fixturesData
     * @param string $field
     *
     * @return void
     */
    protected function importFixtureData(array $fixturesData, string $field): void
    {
        if (property_exists($this, $field) && is_array($fixturesData[$field]) && !empty($fixturesData[$field])) {
            $this->{$field} = $fixturesData[$field];
        }
    }

    /**
     * @param string|null $key
     *
     * @return string
     */
    public function getCustomerClassCode(?string $key): string
    {
        return $this->getClassCode($this->customerCodes, $key);
    }

    /**
     * @param string|null $key
     *
     * @return string
     */
    public function getProductOptionClassCode(?string $key): string
    {
        return $this->getClassCode($this->productOptionCodes, $key);
    }

    /**
     * @param string|null $key
     *
     * @return string
     */
    public function getProductClassCode(?string $key): string
    {
        return $this->getClassCode($this->productCodes, $key);
    }

    /**
     * @param string|null $key
     *
     * @return string
     */
    public function getExpenseCodes(?string $key): string
    {
        return $this->getClassCode($this->expenseCodes, $key);
    }

    /**
     * @param string|null $key
     *
     * @return string
     */
    public function getExemptionCertificate(?string $key): string
    {
        return (string)($this->exemptionsCertificates[$key] ?? '');
    }

    /**
     * @param array<mixed> $classCodes
     * @param string|null $key
     *
     * @return string
     */
    protected function getClassCode(array $classCodes, ?string $key): string
    {
        if (!$key) {
            return '';
        }

        if (isset($classCodes[$key])) {
            return (string)$classCodes[$key];
        }

        $keyHash = (hexdec(substr(sha1($key), 0, 10)) % 10);

        return (string)$classCodes[$keyHash];
    }
}
