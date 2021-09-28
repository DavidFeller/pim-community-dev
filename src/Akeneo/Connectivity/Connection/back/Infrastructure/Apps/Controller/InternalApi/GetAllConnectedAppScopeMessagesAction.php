<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapper;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetAllConnectedAppScopeMessagesAction
{
    private FeatureFlag $featureFlag;
    private SecurityFacade $security;
    private ConnectedAppRepositoryInterface $connectedAppRepository;
    private ScopeMapper $scopeMapper;

    public function __construct(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        ConnectedAppRepositoryInterface $connectedAppRepository,
        ScopeMapper $scopeMapper
    ) {
        $this->featureFlag = $featureFlag;
        $this->security = $security;
        $this->connectedAppRepository = $connectedAppRepository;
        $this->scopeMapper = $scopeMapper;
    }

    public function __invoke(Request $request, string $connectedAppId): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        $connectedApp = $this->connectedAppRepository->findOneById($connectedAppId);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with id $connectedAppId does not exist.");
        }

        $scopeMessages = $this->scopeMapper->getMessages($connectedApp->getScopes());

        return new JsonResponse($scopeMessages);
    }
}
