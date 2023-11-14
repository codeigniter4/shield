<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;

class BaseAuthToken extends BaseConfig
{
    public array $hmacEncryption;

    /**
     * AuthToken Config Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $overwriteHmacEncryptionFields = [
            'key',
            'driver',
            'digest',
        ];

        foreach ($overwriteHmacEncryptionFields as $fieldName) {
            if (is_string($this->hmacEncryption[$fieldName])) {
                $array = json_decode($this->hmacEncryption[$fieldName], true);
                if (is_array($array)) {
                    $this->hmacEncryption[$fieldName] = $array;
                }
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
            case 'hmacEncryption.key':
            case 'hmacEncryption.driver':
            case 'hmacEncryption.digest':
                // if attempting to set property from ENV, first set to empty string
                if ($this->getEnvValue($name, $prefix, $shortPrefix) !== null) {
                    $property = '';
                }
        }

        parent::initEnvValue($property, $name, $prefix, $shortPrefix);
    }
}
