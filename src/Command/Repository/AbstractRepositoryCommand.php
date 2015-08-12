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

namespace Baleen\Cli\Command\Repository;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use League\Flysystem\Filesystem;

/**
 * Class AbstractRepositoryCommand.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractRepositoryCommand extends AbstractCommand
{
    const COMMAND_GROUP = 'migrations';

    /** @var RepositoryInterface */
    protected $repository;

    /** @var LinkedVersions */
    protected $versions;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param RepositoryInterface $repository
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return LinkedVersions
     */
    public function getCollection()
    {
        if (!$this->versions) {
            $versions = $this->repository->fetchAll();
            if ($this->getComparator()) {
                $versions->sortWith($this->getComparator());
            }
            $this->versions = $versions;
        }

        return $this->versions;
    }
}
