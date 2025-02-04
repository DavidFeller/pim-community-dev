<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\User;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RecomputeProductsScores extends Command
{
    private FeatureFlag $featureFlag;

    private JobLauncherInterface $queueJobLauncher;

    private JobInstanceRepository $jobInstanceRepository;

    public function __construct(
        FeatureFlag $featureFlag,
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository
    ) {
        parent::__construct();

        $this->featureFlag = $featureFlag;
        $this->queueJobLauncher = $queueJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:recompute-product-scores')
            ->setDescription('Launch the job that will re-compute all the products scores')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('Data Quality Insights feature is disabled');
            return 0;
        }

        $jobInstance = $this->getJobInstance();
        $user = new User(UserInterface::SYSTEM_USER_NAME, null);
        $this->queueJobLauncher->launch($jobInstance, $user, ['lastProductId' => 0]);

        $output->writeln('The job that re-compute products scores has been launched.');

        return 0;
    }

    private function getJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('data_quality_insights_recompute_products_scores');

        if (!$jobInstance instanceof JobInstance) {
            throw new \RuntimeException('The job instance "data_quality_insights_recompute_products_scores" does not exist. Please contact your administrator.');
        }

        return $jobInstance;
    }
}
