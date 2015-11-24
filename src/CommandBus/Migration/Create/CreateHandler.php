<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Baleen\Cli\CommandBus\Migration\Create;

use Baleen\Cli\CommandBus\Migration\Create\CreateMessage;
use Baleen\Cli\Config\Config;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\AbstractMigration;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\Reflection\DocBlock\Tag;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;

/**
 * Class CreateHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CreateHandler
{
    /**
     * handle.
     *
     * @param CreateMessage $message
     *
     * @return false|string
     *
     * @throws CliException
     */
    public function handle(CreateMessage $message)
    {
        $input = $message->getInput();
        $output = $message->getOutput();

        /** @var Config $config */
        $config = $message->getConfig();

        $directories = $config->getMigrationsConfig();
        if (empty($directories)) {
            throw new CliException(sprintf(
                'No migration directories configured. Please check your configuration definition to ensure at least ' .
                'one element is required to be present.'
            ));
        }
        $directory = reset($directories)['directory'];

        $filesystem = $message->getFilesystem();
        if (!$filesystem->has($directory)) {
            throw new CliException(sprintf(
                'Migrations directory "%s" does not exist.',
                $directory
            ));
        }

        $namespace = $input->getOption('namespace');
        $editorCmd = $input->getOption('editor-cmd');

        if (null === $namespace) {
            $namespace = reset($directories)['namespace'];
        }
        if (!empty($namespace)) {
            $namespace = rtrim($namespace, '\\');
            $namespace = preg_replace('{[^A-Za-z\d_\\\\]+}', '', $namespace);
        }

        $timestamp = date('YmdHis');
        $className = ['v'.$timestamp];
        $title = $input->getArgument('title');
        if (!empty($title)) {
            $title = preg_replace('{[^A-Za-z\d_]+}', '', $title);
            $className[] = $title;
        }

        $class = $this->generate(implode('_', $className), $namespace);
        $result = $this->writeClass($class, $filesystem, $directory);

        if ($result) {
            $output->writeln(sprintf(
                'Created new Migration file at "<info>./%s</info>"',
                $result
            ));
            if ($editorCmd) {
                $pipes = [];
                $baseDir = dirname($config->getDefaultMigrationsDirectoryPath());
                $message = $editorCmd . ' ' . escapeshellarg($baseDir . DIRECTORY_SEPARATOR . $result);
                proc_open($message, array(), $pipes);
            }
        } else {
            $output->writeln(
                'An error occurred creating a new Migration file. Please check directory permissions and configuration.'
            );
        }

        return $result;
    }

    /**
     * @param string $className
     * @param string $namespace
     *
     * @return ClassGenerator
     *
     * @throws CliException
     */
    protected function generate($className, $namespace = null)
    {
        $abstractMigration = AbstractMigration::class;
        $abstractMigrationName = substr($abstractMigration, strrpos($abstractMigration, "\\") + 1);
        $class = new ClassGenerator(
            $className,
            $namespace,
            null,
            $abstractMigrationName,
            [],
            [],
            [
                new MethodGenerator(
                    'up',
                    [],
                    'public',
                    null,
                    new DocBlockGenerator('Executed when migrating upwards.', 'TODO: implement')
                ),
                new MethodGenerator(
                    'down',
                    [],
                    'public',
                    null,
                    new DocBlockGenerator('Executed when migrating downwards / rolling back.', 'TODO: implement')
                ),
            ],
            new DocBlockGenerator($className . ' class', null, [new GenericTag('link', 'http://baleen.org')])
        );
        $class->addUse($abstractMigration);

        return $class;
    }

    /**
     * Function writeClass.
     *
     * @param ClassGenerator $class
     * @param FilesystemInterface $filesystem
     * @param $destinationDir
     *
     * @return string|false
     *
     * @throws CliException
     */
    protected function writeClass(ClassGenerator $class, FilesystemInterface $filesystem, $destinationDir)
    {
        $className = $class->getName();
        $file = new FileGenerator([
            'fileName' => $className.'.php',
            'classes' => [$class],
        ]);
        $contents = $file->generate();

        $relativePath = $destinationDir.DIRECTORY_SEPARATOR.$file->getFilename();
        if ($filesystem->has($relativePath)) {
            throw new CliException(sprintf(
                'Could not generate migration. File already exists: %s',
                $relativePath
            ));
        }

        $result = $filesystem->write($relativePath, $contents);

        return $result ? $relativePath : false;
    }
}
