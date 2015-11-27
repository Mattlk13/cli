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

namespace BaleenTest\Baleen\CommandBus\Config;

use Baleen\Cli\CommandBus\Config\AbstractConfigMessage;
use Baleen\Cli\CommandBus\Migration\Status\StatusMessage;
use Baleen\Cli\CommandBus\Util\ComparatorAwareInterface;
use Baleen\Cli\CommandBus\Util\RepositoriesAwareInterface;
use Baleen\Cli\CommandBus\Util\StorageAwareInterface;
use BaleenTest\Cli\CommandBus\MessageTestCase;
use Mockery as m;

/**
 * Class StatusMessageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StatusMessageTest extends MessageTestCase
{

    /**
     * Must test the constructor and assert implemented interfaces
     */
    public function testConstructor()
    {
        $instance = new StatusMessage();
        $list = [
            AbstractConfigMessage::class,
            RepositoriesAwareInterface::class,
            StorageAwareInterface::class,
            ComparatorAwareInterface::class,
        ];
        foreach ($list as $expected) {
            $this->assertInstanceOf($expected, $instance);
        }
    }

    /**
     * getClassName must return a string with the FQN of the command class being tested
     * @return string
     */
    protected function getClassName()
    {
        return StatusMessage::class;
    }

    /**
     * Must return an array in the format:
     *
     *      [
     *          'name' => 'functionName', // required
     *          'with' => [arguments for with] // optional
     *          'return' => return value // optional, defaults to return self
     *          'times' => number of times it will be invoked
     *      ]
     *
     * @return array
     */
    protected function getExpectations()
    {
        return [
            [
                'name' => 'setName',
                'with' => 'config:status',
            ],
            [
                'name' => 'setAliases',
                'with' => [['status']],
            ],
            [
                'name' => 'setDescription',
                'with' => m::type('string'),
            ]
        ];
    }
}
