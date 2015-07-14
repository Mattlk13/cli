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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Baleen\Container;

use Baleen\Baleen\Application;
use Baleen\Baleen\ApplicationFactory;
use Baleen\Baleen\Command\InitCommand;
use Baleen\Baleen\Command\Storage\LatestCommand;
use Baleen\Baleen\Command\TimelineCommand;
use Baleen\Baleen\Helper\ConfigHelper;
use Baleen\Migrations\Storage\StorageInterface;
use League\Container\ServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class CommandsServiceProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandsServiceProvider extends ServiceProvider
{
    const SERVICE_COMMANDS = 'commands';

    protected $provides = [
        self::SERVICE_COMMANDS,
        LatestCommand::class,
        InitCommand::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->add(LatestCommand::class)
            ->withMethodCall('setStorage', [DefaultServiceProvider::SERVICE_STORAGE]);

        $container->add(InitCommand::class)
            ->withMethodCall('setConfig', [DefaultServiceProvider::SERVICE_CONFIG]);

        $provides = $this->provides;
        $container->add(self::SERVICE_COMMANDS, function() use ($container, $provides) {
            $commands = [];
            foreach ($provides as $command) {
                if ($command !== self::SERVICE_COMMANDS) {
                    $commands[] = $container->get($command);
                }
            }
            return $commands;
        });
    }
}