<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use \Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;

class InMemorySearchJobExecution implements SearchJobExecutionInterface
{
    private InMemoryJobExecutionRepository $jobExecutionRepository;

    public function __construct(InMemoryJobExecutionRepository $jobExecutionRepository)
    {
        $this->jobExecutionRepository = $jobExecutionRepository;
    }

    public function search(SearchJobExecutionQuery $query): array
    {
        $jobExecutions = $this->jobExecutionRepository->all();
        $paginatedJobExecution = array_slice($jobExecutions, $query->page * $query->size, $query->size);

        return array_map(static fn (array $normalizedJobExecution) => new JobExecutionRow(
            $normalizedJobExecution['id'],
            $normalizedJobExecution['jobName'],
            $normalizedJobExecution['type'],
            $normalizedJobExecution['startedAt'] ?? null,
            $normalizedJobExecution['username'] ?? null,
            $normalizedJobExecution['status'],
            $normalizedJobExecution['warningCount'],
            $normalizedJobExecution['errorCount'],
            $normalizedJobExecution['currentStep'],
            $normalizedJobExecution['totalStep'],
        ), $paginatedJobExecution);
    }

    public function count(SearchJobExecutionQuery $query): int
    {
        $jobExecutions = $this->jobExecutionRepository->all();

        return count($jobExecutions);
    }
}
