<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\RunUniqueProcessJob;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\EvaluationsParameters;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaunchEvaluationsCommand extends Command
{
    /** @var FeatureFlag */
    private $featureFlag;

    /** @var RunUniqueProcessJob */
    private $runUniqueProcessJob;

    public function __construct(
        RunUniqueProcessJob $runUniqueProcessJob,
        FeatureFlag $featureFlag
    ) {
        parent::__construct();

        $this->featureFlag = $featureFlag;
        $this->runUniqueProcessJob = $runUniqueProcessJob;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:evaluations')
            ->setDescription('Launch the evaluations of products and structure');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('<info>Data Quality Insights feature is disabled</info>');
            return 0;
        }

        try {
            $this->runUniqueProcessJob->run('data_quality_insights_evaluations', function (?JobExecution $lastJobExecution) {
                return [];
            });
        } catch (AnotherJobStillRunningException $e) {
            exit(0);
        }

        return 0;
    }
}
