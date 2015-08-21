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

namespace Baleen\Cli\Container\ServiceProvider;

use Baleen\Cli\Container\Services;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Storage\StorageInterface;
use Baleen\Migrations\Timeline\TimelineFactory;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use League\Container\ServiceProvider;

/**
 * Class TimelineProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineProvider extends ServiceProvider
{
    protected $provides = [
        Services::TIMELINE,
        Services::TIMELINE_COMPARATOR,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();

        if (!$container->isRegistered(Services::TIMELINE_COMPARATOR)) {
            $container->singleton(Services::TIMELINE_COMPARATOR, DefaultComparator::class);
        }

        $container->singleton(
            Services::TIMELINE,
            function (RepositoryInterface $repository, StorageInterface $storage, ComparatorInterface $comparator) {
                $available = $repository->fetchAll();
                $migrated = $storage->fetchAll();
                $factory = new TimelineFactory($available, $migrated);

                return $factory->create($comparator);
            }
        )->withArguments([
            Services::REPOSITORY,
            Services::STORAGE,
            DefaultComparator::class,
        ]);
    }
}
