<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton command file.
 */
class UserModelGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Shield';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'shield:make';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new UserModel file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'shield:make <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The model class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserModel).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params): void
    {
        $this->component = 'Model';
        $this->directory = 'Models';
        $this->template  = 'usermodel.tpl.php';

        $this->classNameLang = 'CLI.generator.className.model';
        $this->execute($params);
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        return $this->parseTemplate($class);
    }
}
