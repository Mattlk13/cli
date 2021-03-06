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

namespace Baleen\Cli\CommandBus\Repository;

use Baleen\Migrations\Version\Collection\LinkedVersions;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ListHandler extends AbstractRepositoryListHandler
{
    /**
     * handle.
     *
     * @param ListMessage $command
     */
    public function handle(ListMessage $command)
    {
        $input = $command->getInput();
        $output = $command->getOutput();

        $reverse = $input->getOption('newest-first');
        $versions = $this->getCollection($command->getRepository(), $command->getComparator());

        if (count($versions) > 0) {
            if ($reverse) {
                $versions = $versions->getReverse();
            }
            $this->outputVersions($versions, $output);
        } else {
            $output->writeln('No available migrations were found. Please check your settings.');
        }
    }

    /**
     * @param LinkedVersions  $versions
     * @param OutputInterface $output
     */
    protected function outputVersions(LinkedVersions $versions, OutputInterface $output)
    {
        foreach ($versions as $version) {
            $output->writeln('<comment>('.$version->getId().')</comment> '.get_class($version->getMigration()));
        }
    }
}
