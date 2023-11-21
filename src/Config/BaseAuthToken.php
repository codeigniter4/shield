<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;

class BaseAuthToken extends BaseConfig
{
    /**
     * List of HMAC Encryption Keys
     *
     * @var array|string
     */
    public $hmacEncryptionKeys;

    /**
     * AuthToken Config Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (is_string($this->hmacEncryptionKeys)) {
            $array = json_decode($this->hmacEncryptionKeys, true);
            if (is_array($array)) {
                $this->hmacEncryptionKeys = $array;
            }
        }
    }

    /**
     * Override parent initEnvValue() to allow for direct setting to array properties values from ENV
     *
     * In order to set array properties via ENV vars we need to set the property to a string value first.
     *
     * @param mixed $property
     */
    protected function initEnvValue(&$property, string $name, string $prefix, string $shortPrefix): void
    {
        switch ($name) {
            case 'hmacEncryptionKeys':
                // if attempting to set property from ENV, first set to empty string
                if ($this->getEnvValue($name, $prefix, $shortPrefix) !== null) {
                    $property = '';
                }
        }

        parent::initEnvValue($property, $name, $prefix, $shortPrefix);
    }
}
