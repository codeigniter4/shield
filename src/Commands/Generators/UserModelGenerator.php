<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a custom user model file.
 */
class UserModelGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * @var string
     */
    protected $group = 'Shield';

    /**
     * @var string
     */
    protected $name = 'shield:model';

    /**
     * @var string
     */
    protected $description = 'Generate a new UserModel file.';

    /**
     * @var string
     */
    protected $usage = 'shield:model [<name>] [options]';

    /**
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => 'The model class name. If not provided, this will default to `UserModel`.',
    ];

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserModel).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute the command.
     */
    public function run(array $params): int
    {
        $this->component = 'Model';
        $this->directory = 'Models';
        $this->template  = 'usermodel.tpl.php';

        $this->classNameLang = 'CLI.generator.className.model';
        $this->setHasClassName(false);

        $class = $params[0] ?? CLI::getSegment(2) ?? 'UserModel';

        if (! $this->verifyChosenModelClassName($class, $params)) {
            CLI::error('Cannot use `ShieldUserModel` as class name as this conflicts with the parent class.', 'light_gray', 'red');

            return 1;
        }

        $params[0] = $class;

        // @TODO execute() is deprecated in CI v4.3.0.
        $this->execute($params); // @phpstan-ignore-line suppress deprecated error.

        return 0;
    }

    /**
     * The chosen class name should not conflict with the alias of the parent class.
     */
    private function verifyChosenModelClassName(string $class, array $params): bool
    {
        helper('inflector');

        if (array_key_exists('suffix', $params) && ! strripos($class, 'Model')) {
            $class .= 'Model';
        }

        return strtolower(pascalize($class)) !== 'shieldusermodel';
    }
}
