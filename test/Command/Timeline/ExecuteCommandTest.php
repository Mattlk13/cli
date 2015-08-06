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

namespace BaleenTest\Baleen\Command\Timeline;

use Baleen\Cli\Command\Timeline\ExecuteCommand;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Version;
use BaleenTest\Baleen\Command\CommandTestCase;
use Mockery as m;

/**
 * Class ExecuteCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ExecuteCommandTest extends CommandTestCase
{
    /** @var m\Mock|ExecuteCommand */
    protected $instance;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(ExecuteCommand::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    /**
     * testConfigure
     */
    public function testConfigure()
    {
        $instance = new ExecuteCommand();
        $this->assertHasArgument($instance, ExecuteCommand::ARG_VERSION);
        $this->assertHasArgument($instance, ExecuteCommand::ARG_DIRECTION);
        $this->assertHasOption($instance, ExecuteCommand::OPT_DOWN);
        $this->assertHasOption($instance, ExecuteCommand::OPT_DRY_RUN);
    }

    /**
     * @param $isInteractive
     * @param $isUp
     * @param $isDryRun
     * @param $askResult
     * @dataProvider executeProvider
     */
    public function testExecute($isInteractive, $isUp, $isDryRun, $askResult)
    {
        /** @var m\Mock|Timeline $timeline */
        $timeline = m::mock(Timeline::class);
        $this->input->shouldReceive('isInteractive')->once()->andReturn($isInteractive);
        $this->input->shouldReceive('getArgument')->with(ExecuteCommand::ARG_VERSION)->once()->andReturn('123');
        $this->input->shouldReceive('getArgument')->with(ExecuteCommand::ARG_DIRECTION)->once()->andReturn(!$isUp);
        $this->input->shouldReceive('getOption')->with(ExecuteCommand::OPT_DRY_RUN)->once()->andReturn($isDryRun);

        if ($isInteractive) {
            $this->output->shouldReceive('writeln')->once()->with('/WARNING/');
            $this->assertQuestionAsked($askResult, m::type('Symfony\Component\Console\Question\ConfirmationQuestion'));
        }

        if (!$isInteractive || $askResult) {
            $timeline->shouldReceive('runSingle')->with(
                m::on(function (Version $version) {
                    return $version->getId() === '123';
                }),
                m::on(function (Options $options) use ($isUp, $isDryRun) {
                    return $options->isDryRun() === $isDryRun
                        && $options->isDirectionUp() === $isUp;
                })
            );
            $this->output->shouldReceive('writeln')->once()->with('/successfully/');
        }

        $this->instance->setTimeline($timeline);
        $this->execute();
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return $this->combinations([
            [true, false], // isInteractive
            [true, false], // isUp
            [true, false], // isDryRun
            [true, false], // askResult
        ]);
    }
}