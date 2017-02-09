<?php declare(strict_types=1);

namespace Symplify\MultiCodingStandard\PhpCsFixer\Application;

use Symplify\MultiCodingStandard\Application\Command\RunApplicationCommand;
use Symplify\MultiCodingStandard\Contract\Application\ApplicationInterface;
use Symplify\MultiCodingStandard\PhpCsFixer\Runner\RunnerFactory;

final class Application implements ApplicationInterface
{
    /**
     * @var RunnerFactory
     */
    private $runnerFactory;

    public function __construct(RunnerFactory $runnerFactory)
    {
        $this->runnerFactory = $runnerFactory;
    }

    public function runCommand(RunApplicationCommand $command) : void
    {
        foreach ($command->getSources() as $source) {
            $this->runForSource($source, $command);
        }
    }

    private function runForSource(string $source, RunApplicationCommand $command)
    {
        $runner = $this->runnerFactory->create(
            $command->getRules(),
            $command->getExcludedRules(),
            $source,
            $command->isFixer()
        );

        $runner->fix();
    }
}
