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

namespace Baleen\Cli\CommandBus\Config;

use Baleen\Migrations\Version;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Handles the config:status command.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StatusHandler
{
    const STYLE_INFO = 'info';
    const STYLE_COMMENT = 'comment';

    /** @var OutputInterface */
    protected $output;

    /** @var StatusMessage */
    protected $message;

    /**
     * Handles a StatusMessage, which prints the status of the migrations system in a developer-friendly format
     * (inspired by "git status").
     *
     * @param StatusMessage $message
     */
    public function handle(StatusMessage $message)
    {
        $this->message = $message;
        $this->output = $message->getOutput();
        $output = $this->output;

        $repository = $message->getRepository();
        $storage = $message->getStorage();

        $availableMigrations = $repository->fetchAll();
        $migratedVersions = $storage->fetchAll();

        $headVersion = $migratedVersions->last();

        $currentMessage = $headVersion ?
            "Current version: <comment>{$headVersion->getId()}</comment>" :
            'Nothing has been migrated yet.';
        $output->writeln($currentMessage);

        $diff = array_diff($availableMigrations->toArray(), $migratedVersions->toArray());

        $pendingCount = count($diff);

        if ($pendingCount > 0) {
            $executable = defined('MIGRATIONS_EXECUTABLE') ? MIGRATIONS_EXECUTABLE . ' ' : '';
            $output->writeln([
                "Your database is out-of-date by $pendingCount versions, and can be migrated.",
                sprintf(
                    '  (use "<comment>%smigrate</comment>" to execute all migrations)',
                    $executable
                ),
                ''
            ]);
            if ($headVersion) {
                list($beforeHead, $afterHead) = $this->splitDiff($diff, $message->getComparator(), $headVersion);
            } else {
                $beforeHead = [];
                $afterHead = $diff;
            }
            $this->printDiff(
                $beforeHead,
                [
                    'Old migrations still pending:',
                    sprintf("  (use \"<comment>{$executable}migrate HEAD</comment>\" to migrate them)", $executable),
                ],
                self::STYLE_COMMENT
            );
            $this->printDiff($afterHead, ['New migrations:'], self::STYLE_INFO);
        } else {
            $output->writeln('Your database is up-to-date.');
        }
    }

    /**
     * Formats and prints a pending version with the given style.
     *
     * @param Version $version The Version to print.
     * @param string $style One of the STYLE_* constants.
     */
    protected function printPendingVersion(Version $version, $style)
    {
        /** @var Version $version */
        $id = $version->getId();
        $reflectionClass = new \ReflectionClass($version->getMigration());
        $absolutePath = $reflectionClass->getFileName();
        $fileName = $absolutePath ? $this->getRelativePath(getcwd(), $absolutePath) : '';
        $this->output->writeln("\t<$style>[$id] $fileName</$style>");
    }

    /**
     * Returns the relative path between two known paths.
     *
     * @param $from
     * @param $to
     *
     * @return string
     *
     * @link http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
     */
    protected function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if (isset($to[$depth]) && $dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                }
            }
        }
        return implode('/', $relPath);
    }

    /**
     * Prints an array (group) of Versions all with the given style. If the array is empty then it prints nothing.
     *
     * @param array $versions
     * @param string|string[] $message Message(s) to print before the group of versions.
     * @param string $style One of the STYLE_* constants.
     */
    protected function printDiff(array $versions, $message, $style = self::STYLE_INFO)
    {
        if (empty($versions)) {
            return;
        }
        $this->output->writeln($message);
        $this->output->writeln('');

        foreach ($versions as $version) {
            $this->printPendingVersion($version, $style);
        }

        // if there was at least one version in the array
        $this->output->writeln('');
    }

    /**
     * Splits an array of Versions into two arrays and returns them. The first one contains all versions before the HEAD
     * (latest migrated) Version, and the second one contains all Versions after HEAD. Head is never included in either
     * of the arrays.
     *
     * @param Version[] $diff The array of Versions that should be split.
     * @param callable $comparator The comparator used to sort Versions.
     * @param Version $head The HEAD version.
     * @return array
     */
    protected function splitDiff(array $diff, callable $comparator, Version $head)
    {
        $beforeHead = [];
        $afterHead = [];
        foreach ($diff as $v) {
            $result = $comparator($v, $head);
            if ($result < 0) {
                $beforeHead[] = $v;
            } elseif ($result > 0) {
                $afterHead[] = $v;
            }
        }
        return [$beforeHead, $afterHead];
    }
}
