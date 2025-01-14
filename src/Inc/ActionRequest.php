<?php
/**
 * ActionRequest. Perform most of functionalities of signing/encryption et.
 *
 * @since 2.0.4
 */

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\KeyManagement\JWKFactory as JWKFactory;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Easy\ContentEncryptionAlgorithmChecker;
use Psr\Http\Message\RequestInterface;

abstract class ActionRequest {

	private JWSCompactSerializer $jwsCompactSerializer;
	private JWSBuilder $jwsBuilder;
	private JWSLoader $jwsLoader;
	private ClaimCheckerManager $claimCheckerManager;

	private JWECompactSerializer $jweCompactSerializer;
	private JWEBuilder $jweBuilder;
	private JWELoader $jweLoader;

	public function __construct() {
		 $this->jwsCompactSerializer = new JWSCompactSerializer();
		$this->jwsBuilder            = new JWSBuilder(
			signatureAlgorithmManager: new AlgorithmManager(
				algorithms: array(
					new PS256(),
				)
			)
		);
		$this->jwsLoader             = new JWSLoader(
			serializerManager: new JWSSerializerManager(
				serializers: array(
					new JWSCompactSerializer(),
				)
			),
			jwsVerifier: new JWSVerifier(
				signatureAlgorithmManager: new AlgorithmManager(
					algorithms: array(
						new PS256(),
					)
				)
			),
			headerCheckerManager: new HeaderCheckerManager(
				checkers: array(
					new AlgorithmChecker(
						supportedAlgorithms: array( SecurityData::$JWSAlgorithm ),
						protectedHeader: true
					),
				),
				tokenTypes: array(
					new JWSTokenSupport(),
				)
			),
		);
		$this->claimCheckerManager   = new ClaimCheckerManager(
			checkers: array(
				new NotBeforeChecker(),
				new ExpirationTimeChecker(),
				new AudienceChecker( SecurityData::accessToken() ),
				new IssuerChecker( array( 'PacoIssuer' ) ),
			)
		);

		$this->jweCompactSerializer = new JWECompactSerializer();
		$this->jweBuilder           = new JWEBuilder(
			keyEncryptionAlgorithmManager: new AlgorithmManager(
				algorithms: array(
					new RSAOAEP(),
				)
			),
			contentEncryptionAlgorithmManager: new AlgorithmManager(
				algorithms: array(
					new A128CBCHS256(),
				)
			),
			compressionManager: new CompressionMethodManager(
				methods: array()
			)
		);
		$this->jweLoader            = new JWELoader(
			serializerManager: new JWESerializerManager(
				serializers: array(
					new JWECompactSerializer(),
				)
			),
			jweDecrypter: new JWEDecrypter(
				keyEncryptionAlgorithmManager: new AlgorithmManager(
					algorithms: array(
						new RSAOAEP(),
					)
				),
				contentEncryptionAlgorithmManager: new AlgorithmManager(
					algorithms: array(
						new A128CBCHS256(),
					)
				),
				compressionMethodManager: new CompressionMethodManager(
					methods: array(),
				)
			),
			headerCheckerManager: new HeaderCheckerManager(
				checkers: array(
					new AlgorithmChecker(
						supportedAlgorithms: array( SecurityData::$JWEAlgorithm ),
						protectedHeader: true
					),
					new ContentEncryptionAlgorithmChecker(
						supportedAlgorithms: array( SecurityData::$JWEEncrptionAlgorithm ),
						protectedHeader: true
					),
				),
				tokenTypes: array(
					new JWETokenSupport(),
				)
			)
		);
	}

	/**
	 * Creates a JWK Private Key from PKCS#8 Encoded Private Key
	 *
	 * @param string      $key
	 * @param string|null $password
	 * @param array       $additional_values
	 * @return JWK
	 */
	protected function GetPrivateKey( string $key, ?string $password = null, array $additional_values = array() ): JWK {
		$privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . $key . "\n-----END RSA PRIVATE KEY-----";
		return JWKFactory::createFromKey( $privateKey, $password, $additional_values );
	}

	/**
	 * Creates a JWK Public Key from PKCS#8 Encoded Public Key
	 *
	 * @param string      $key
	 * @param string|null $password
	 * @param array       $additional_values
	 * @return JWK
	 */
	protected function GetPublicKey( string $key, ?string $password = null, array $additional_values = array() ): JWK {
		$publicKey = "-----BEGIN PUBLIC KEY-----\n" . $key . "\n-----END PUBLIC KEY-----";
		return JWKFactory::createFromKey( $publicKey, $password, $additional_values );
	}

	/**
	 * Creates an encrypted JOSE Token from given payload
	 *
	 * @param string $payload
	 * @param JWK    $signingKey
	 * @param JWK    $encryptingKey
	 * @return string
	 */
	protected function EncryptPayload( string $payload, JWK $signingKey, JWK $encryptingKey ): string {
		// used third-party php jwt framework : https://github.com/web-token/jwt-framework
		$jws = $this->jwsBuilder
			->create()
			->withPayload( $payload )
			->addSignature(
				$signingKey,
				array(
					'alg' => SecurityData::$JWSAlgorithm,
					'typ' => SecurityData::$TokenType,
				)
			)
			->build();

		// used third-party php jwt framework : https://github.com/web-token/jwt-framework
		$jwe = $this->jweBuilder
			->create()
			->withPayload( $this->jwsCompactSerializer->serialize( $jws ) )
			->withSharedProtectedHeader(
				array(
					'alg' => SecurityData::$JWEAlgorithm,
					'enc' => SecurityData::$JWEEncrptionAlgorithm,
					'kid' => SecurityData::$EncryptionKeyId,
					'typ' => SecurityData::$TokenType,
				)
			)
			->addRecipient( $encryptingKey )
			->build();

		return $this->jweCompactSerializer->serialize( $jwe, 0 );
	}

	/**
	 * Decrypts a JOSE Token and returns plain text payload
	 *
	 * @param string $token
	 * @param JWK    $decryptingKey
	 * @param JWK    $signatureVerificationKey
	 * @return string
	 * @throws InvalidClaimException
	 * @throws MissingMandatoryClaimException
	 * @throws Exception
	 */
	protected function DecryptToken( string $token, JWK $decryptingKey, JWK $signatureVerificationKey ): string {
		$jwe = $this->jweLoader->loadAndDecryptWithKey( $token, $decryptingKey, $recipient );

		$jws = $this->jwsLoader->loadAndVerifyWithKey( $jwe->getPayload(), $signatureVerificationKey, $signature );

		$token = $jws->getPayload();

		$claims = json_decode( $token, true );

		$this->claimCheckerManager->check( $claims );

		return $token;
	}
}
