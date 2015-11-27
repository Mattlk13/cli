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

namespace Baleen\Cli\CommandBus\Run\Execute;

use Baleen\Cli\CommandBus\Run\AbstractRunMessage;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Timeline\TimelineFactory;
use Baleen\Migrations\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ExecuteMessage.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ExecuteMessage extends AbstractRunMessage
{
    const COMMAND_NAME = 'execute';
    const COMMAND_ALIAS_SHORT = 'exec';
    const COMMAND_ALIAS = 'execute';
    const ARG_VERSION = 'version';
    const ARG_DIRECTION = 'direction';

    /**
     * @inheritDoc
     */
    public static function configure(Command $command)
    {
        parent::configure($command);
        $command->setName('run:execute')
            ->setAliases([self::COMMAND_ALIAS_SHORT, self::COMMAND_ALIAS])
            ->setDescription('Execute a single migration version up or down manually.')
            ->addArgument(self::ARG_VERSION, InputArgument::REQUIRED, 'The version to execute.')
            ->addArgument(
                self::ARG_DIRECTION,
                InputArgument::OPTIONAL,
                'Direction in which to execute the migration.',
                Direction::UP
            );
    }
}
