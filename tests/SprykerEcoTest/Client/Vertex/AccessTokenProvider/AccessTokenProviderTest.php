<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEcoTest\Client\Vertex\AccessTokenProvider;

use Codeception\Test\Unit;
use DateTime;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexApiAuthResponseTransfer;
use Pyz\Zed\VertexApi\Business\Authenticator\VertexApiAuthenticatorInterface;
use PyzTest\Zed\VertexApi\VertexApiBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group VertexApi
 * @group Business
 * @group AccessTokenProvider
 * @group AccessTokenProviderTest
 * Add your own group annotations below this line
 */
class AccessTokenProviderTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    // public function testProvideVertexAccessTokenRetrievesValidAccessTokenFromDatabaseCache(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer();
    //     $vertexApiAccessTokenTransfer = $this->tester->haveVertexApiAccessTokenPersisted([
    //         VertexApiAccessTokenTransfer::CREDENTIAL_HASH => $vertexConfigTransfer->getCredentialHash(),
    //         VertexApiAccessTokenTransfer::EXPIRATION_DATE => (new DateTime('now'))->modify('+1 year'),
    //     ]);

    //     /** @var \Pyz\Zed\VertexApi\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider */
    //     $accessTokenProvider = $this->tester->getFactory()->createAccessTokenProvider();

    //     // Act
    //     $providedApiAccessTokenTransfer = $accessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

    //     // Assert
    //     $this->assertEquals($vertexApiAccessTokenTransfer->getAccessToken(), $providedApiAccessTokenTransfer->getAccessTokenOrFail());
    // }

    // /**
    //  * @return void
    //  */
    // public function testProvideVertexAccessTokenRetrievesValidAccessTokenUsingOauthWhenCachedTokenIsExpired(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer();
    //     $vertexApiAccessTokenTransfer = $this->tester->haveVertexApiAccessTokenPersisted([
    //         VertexApiAccessTokenTransfer::CREDENTIAL_HASH => $vertexConfigTransfer->getCredentialHash(),
    //         VertexApiAccessTokenTransfer::EXPIRATION_DATE => (new DateTime('now'))->modify('-1 year'),
    //     ]);

    //     $this->mockVertexApiAuthenticator(
    //         (new VertexApiAuthResponseTransfer())
    //             ->setAccessToken($vertexApiAccessTokenTransfer->getAccessToken()),
    //     );

    //     /** @var \Pyz\Zed\VertexApi\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider */
    //     $accessTokenProvider = $this->tester->getFactory()->createAccessTokenProvider();

    //     // Act
    //     $providedApiAccessTokenTransfer = $accessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

    //     // Assert
    //     $this->assertEquals($vertexApiAccessTokenTransfer->getAccessToken(), $providedApiAccessTokenTransfer->getAccessTokenOrFail());
    // }

    // /**
    //  * @return void
    //  */
    // public function testProvideVertexAccessTokenRetrievesValidAccessTokenUsingOauthWhenDatabaseCacheMisses(): void
    // {
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer();
    //     $vertexApiAccessTokenTransfer = $this->tester->haveVertexApiAccessToken([
    //         VertexApiAccessTokenTransfer::CREDENTIAL_HASH => $vertexConfigTransfer->getCredentialHash(),
    //     ]);

    //     $this->mockVertexApiAuthenticator(
    //         (new VertexApiAuthResponseTransfer())
    //             ->setAccessToken($vertexApiAccessTokenTransfer->getAccessToken()),
    //     );

    //     /** @var \Pyz\Zed\VertexApi\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider */
    //     $accessTokenProvider = $this->tester->getFactory()->createAccessTokenProvider();

    //     // Act
    //     $providedApiAccessTokenTransfer = $accessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

    //     // Assert
    //     $this->assertEquals($vertexApiAccessTokenTransfer->getAccessToken(), $providedApiAccessTokenTransfer->getAccessTokenOrFail());
    // }

    // /**
    //  * @return void
    //  */
    // public function testProvideVertexAccessTokenFailsWhenAccessTokenCantBeRetrievedUsingOauth(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer();

    //     $testError = 'TestError';
    //     $this->mockVertexApiAuthenticator(
    //         (new VertexApiAuthResponseTransfer())
    //             ->setErrors([$testError]),
    //     );

    //     /** @var \Pyz\Zed\VertexApi\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider */
    //     $accessTokenProvider = $this->tester->getFactory()->createAccessTokenProvider();

    //     // Act
    //     $providedApiAccessTokenTransfer = $accessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

    //     // Assert
    //     $this->assertEmpty($providedApiAccessTokenTransfer->getAccessToken());
    // }

    // /**
    //  * @param \Generated\Shared\Transfer\VertexApiAuthResponseTransfer $vertexApiAuthResponseTransfer
    //  *
    //  * @return void
    //  */
    // protected function mockVertexApiAuthenticator(VertexApiAuthResponseTransfer $vertexApiAuthResponseTransfer): void
    // {
    //     $vertexApiAuthenticatorMock = $this->createMock(VertexApiAuthenticatorInterface::class);

    //     $vertexApiAuthenticatorMock->method('authenticate')
    //         ->willReturn($vertexApiAuthResponseTransfer);

    //     $this->tester->mockFactoryMethod('createVertexApiAuthenticator', $vertexApiAuthenticatorMock);
    // }
}
