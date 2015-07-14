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

namespace Baleen\Baleen\Command\Storage;

use Baleen\Migrations\Version\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LatestCommand
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LatestCommand extends StorageCommand
{
    const COMMAND_NAME = 'migrations:latest';

    public function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Outputs the name of the latest migrated version.')
            ;
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $migrated = $this->storage->readMigratedVersions();
        if (count($migrated) === 0) {
            $output->writeln('No migrated versions found in storage.');
            return;
        }
        end($migrated);
        /** @var \Baleen\Migrations\Version $last */
        $last = current($migrated);
        $output->writeln($last->getId());
    }
}