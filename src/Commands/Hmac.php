<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Commands;

use CodeIgniter\Shield\Authentication\HMAC\HmacEncrypter;
use CodeIgniter\Shield\Commands\Exceptions\BadInputException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use ReflectionException;

class Hmac extends BaseCommand
{
    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'shield:hmac';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Encrypt/Decrypt secretKey for HMAC tokens. The encryption should only be run on existing raw secret keys (extremely rare).';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = <<<'EOL'
        shield:hmac <action>
            shield:hmac encrypt
            shield:hmac decrypt
        EOL;

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'action' => <<<'EOL'
                encrypt: Encrypt all raw HMAC Secret Keys
                decrypt: Decrypt all encrypted HMAC Secret Keys
            EOL,
    ];

    /**
     * HMAC Encrypter Object
     */
    private HmacEncrypter $encrypter;

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Run Encryption Methods
     */
    public function run(array $params): int
    {
        $action = $params[0] ?? null;

        $this->encrypter = new HmacEncrypter();

        try {
            switch ($action) {
                case 'encrypt':
                    $this->encrypt();
                    break;

                case 'decrypt':
                    $this->decrypt();
                    break;

                default:
                    throw new BadInputException('Unrecognized Command');
            }
        } catch (BadInputException|ReflectionException $e) {
            $this->write($e->getMessage(), 'red');

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    /**
     * Encrypt all Raw HMAC Secret Keys
     *
     * @throws ReflectionException
     */
    public function encrypt(): void
    {
        $uIdModel  = new UserIdentityModel();
        $encrypter = $this->encrypter;

        $uIdModel->where('type', 'hmac_sha256')->chunk(
            100,
            static function ($identity) use ($uIdModel, $encrypter): void {
                $identity->secret2 = $encrypter->encrypt($identity->secret2);

                $uIdModel->save($identity);
            }
        );
    }

    /**
     * Decrypt all encrypted HMAC Secret Keys
     *
     * @throws ReflectionException
     */
    public function decrypt(): void
    {
        $uIdModel  = new UserIdentityModel();
        $encrypter = $this->encrypter;

        $uIdModel->where('type', 'hmac_sha256')->chunk(
            100,
            static function ($identity) use ($uIdModel, $encrypter): void {
                $identity->secret2 = $encrypter->decrypt($identity->secret2);

                $uIdModel->save($identity);
            }
        );
    }
}
