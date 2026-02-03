<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEcoTest\Client\Vertex;

use Codeception\Test\Unit;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Vertex
 * @group VertexClientAuthenticateMethodTest
 * Add your own group annotations below this line
 */
class VertexClientAuthenticateMethodTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    public function testAuthenticateMethodReturnsAccessTokenWhenVertexAPIRequestIsSuccessful(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexApiCredentialTransfer = $this->tester->haveVertexApiCredentialTransfer($vertexConfigTransfer->toArray());
        $mockClient = $this->tester->mockClientForVertexApiCredentialWithValidResponse($vertexApiCredentialTransfer);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $vertexApiAuthResponseTransfer = $vertexClient->authenticate($vertexConfigTransfer);

        // Assert
        $this->assertEquals('access-token', $vertexApiAuthResponseTransfer->getAccessToken());
        $this->assertEmpty($vertexApiAuthResponseTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testAuthenticateMethodReturnsErrorResponseWhenVertexAPIRequestHasFailed(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexApiCredentialTransfer = $this->tester->haveVertexApiCredentialTransfer($vertexConfigTransfer->toArray());
        $mockClient = $this->tester->mockClientForVertexApiCredentialWithFailedResponse($vertexApiCredentialTransfer);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $vertexApiAccessTokenTransfer = $vertexClient->authenticate($vertexConfigTransfer);

        // Assert
        $this->assertEmpty($vertexApiAccessTokenTransfer->getAccessToken());
        $this->assertEquals(['Request to Vertex API failed.'], $vertexApiAccessTokenTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testAuthenticateMethodReturnsErrorResponseWhenVertexAPIRequestDoesHaveEmptyAccessToken(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexApiCredentialTransfer = $this->tester->haveVertexApiCredentialTransfer($vertexConfigTransfer->toArray());
        $mockClient = $this->tester->mockClientForVertexApiCredentialResponseWithEmptyAccessToken($vertexApiCredentialTransfer);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        
        // Act
        $vertexApiAccessTokenTransfer = $vertexClient->authenticate($vertexConfigTransfer);

        // Assert
        $this->assertEmpty($vertexApiAccessTokenTransfer->getAccessToken());
        $this->assertEquals(['Invalid response from Vertex API.'], $vertexApiAccessTokenTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testAuthenticateMethodReturnsErrorResponseWhenVertexAPIRequestDoesNotContainAccessToken(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexApiCredentialTransfer = $this->tester->haveVertexApiCredentialTransfer($vertexConfigTransfer->toArray());
        $mockClient = $this->tester->mockClientForVertexApiCredentialResponseWithMissingAccessToken($vertexApiCredentialTransfer);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $vertexApiAccessTokenTransfer = $vertexClient->authenticate($vertexConfigTransfer);

        // Assert
        $this->assertEmpty($vertexApiAccessTokenTransfer->getAccessToken());
        $this->assertEquals(['Request to Vertex API failed.'], $vertexApiAccessTokenTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testAuthenticateMethodReturnsErrorResponseWhenVertexAPIRequestResponseWith401InvalidCredentials(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexApiCredentialTransfer = $this->tester->haveVertexApiCredentialTransfer($vertexConfigTransfer->toArray());
        $mockClient = $this->tester->mockClientForVertexApiCredentialResponseWithInvalidCredentials($vertexApiCredentialTransfer);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $vertexApiAccessTokenTransfer = $vertexClient->authenticate($vertexConfigTransfer);

        // Assert
        $this->assertEmpty($vertexApiAccessTokenTransfer->getAccessToken());
        $this->assertStringContainsString('Invalid credentials.', $vertexApiAccessTokenTransfer->getErrors()[0]);
    }
}
