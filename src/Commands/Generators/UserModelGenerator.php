<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
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
    protected $usage = 'shield:model <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => 'The model class name.',
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
    public function run(array $params): void
    {
        $this->component = 'Model';
        $this->directory = 'Models';
        $this->template  = 'usermodel.tpl.php';

        $this->classNameLang = 'CLI.generator.className.model';

        $this->execute($params);
    }
}
