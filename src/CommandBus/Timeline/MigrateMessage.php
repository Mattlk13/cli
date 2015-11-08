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

namespace Baleen\Cli\CommandBus\Timeline;

use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Version\VersionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateMessage.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateMessage extends AbstractTimelineCommand
{
    const ARG_TARGET = 'target';
    const OPT_STRATEGY = 'strategy';
    const OPT_NOPROGRESS = 'no-progress';

    /** @var array */
    private $strategies = [
        Options::DIRECTION_UP => 'upTowards',
        Options::DIRECTION_DOWN => 'downTowards',
        'both' => 'goTowards',
    ];

    /**
     * @inheritdoc
     */
    public static function configure(Command $command)
    {
        parent::configure($command);

        $command
            ->setName('timeline:migrate')
            ->setAliases(['migrate'])
            ->setDescription('Migrates all versions up (or down) to and including the specified target.')
            ->addArgument(self::ARG_TARGET, InputArgument::OPTIONAL, 'The target version to migrate to.', 'latest')
            ->addOption(
                self::OPT_NOPROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Show a more detailed log instead of a progress bar.'
            )->addOption(
                self::OPT_STRATEGY,
                's',
                InputOption::VALUE_REQUIRED,
                'Strategy to migrate with (up/down/both).',
                Options::DIRECTION_UP // 'up'
            );
    }

    /**
     * isDryRun
     * @return bool
     */
    public function isDryRun()
    {
        return (bool) $this->getInput()->getOption(MigrateMessage::OPT_DRY_RUN);
    }

    /**
     * isNoStorage
     * @return bool
     */
    public function isNoStorage()
    {
        return (bool) $this->getInput()->getOption(MigrateMessage::OPT_NO_STORAGE);
    }

    /**
     * isNoProgress
     *
     * @return bool
     */
    public function isNoProgress()
    {
        return (bool) $this->getInput()->getOption(MigrateMessage::OPT_NOPROGRESS);
    }

    /**
     * shouldSaveChanges
     *
     * @return bool
     */
    public function shouldSaveChanges()
    {
        return !$this->isNoStorage() && !$this->isDryRun();
    }

    /**
     * shouldTrackProgress
     *
     * @return bool
     */
    public function shouldTrackProgress()
    {
        return ($this->getOutput()->getVerbosity() !== OutputInterface::VERBOSITY_QUIET) && !$this->isNoProgress();
    }

    /**
     * @return string
     *
     * @throws CliException
     */
    public function getStrategy()
    {
        $strategy = strtolower($this->getInput()->getOption(MigrateMessage::OPT_STRATEGY));
        if (!isset($this->strategies[$strategy])) {
            throw new CliException(sprintf(
                'Unknown strategy "%s". Must be one of: %s',
                $strategy,
                implode(', ', array_keys($this->strategies))
            ));
        }

        return $this->strategies[$strategy];
    }

    /**
     * Returns the migration target
     *
     * @return VersionInterface
     *
     * @throws CliException
     */
    public function getTarget()
    {
        $targetArg = (string) $this->getInput()->getArgument(MigrateMessage::ARG_TARGET);
        $target = $this->getTimeline()->getVersions()->get($targetArg);
        if (!$target) {
            throw new CliException(sprintf(
                'Migration target with id "%s" could not be found.',
                $targetArg
            ));
        }
        return $target;
    }
}