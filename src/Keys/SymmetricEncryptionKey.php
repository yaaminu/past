<?php
declare(strict_types=1);
namespace ParagonIE\PAST\Keys;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\PAST\{
    KeyInterface,
    Protocol\Version2,
    Util
};

/**
 * Class SymmetricEncryptionKey
 * @package ParagonIE\PAST\Keys
 */
class SymmetricEncryptionKey implements KeyInterface
{
    const INFO_ENCRYPTION = 'past-encryption-key';
    const INFO_AUTHENTICATION = 'past-auth-key-for-aead';

    /** @var string $key */
    protected $key = '';

    /** @var string $protocol */
    protected $protocol = Version2::HEADER;

    /**
     * SymmetricEncryptionKey constructor.
     *
     * @param string $keyMaterial
     * @param string $protocol
     */
    public function __construct(string $keyMaterial, string $protocol = Version2::HEADER)
    {
        $this->key = $keyMaterial;
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function encode(): string
    {
        return Base64UrlSafe::encode($this->key);
    }

    /**
     * @param string $encoded
     * @return self
     */
    public static function fromEncodedString(string $encoded): self
    {
        $decoded = Base64UrlSafe::decode($encoded);
        return new self($decoded);
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        return $this->key;
    }

    /**
     * @param string|null $salt
     * @return array<int, string>
     *
     * @throws \Error
     * @throws \TypeError
     */
    public function split(string $salt = null): array
    {
        $encKey = Util::HKDF('sha384', $this->key, 32, self::INFO_ENCRYPTION, $salt);
        $authKey = Util::HKDF('sha384', $this->key, 32, self::INFO_AUTHENTICATION, $salt);
        return [$encKey, $authKey];
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }
}
