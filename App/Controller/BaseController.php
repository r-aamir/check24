<?php

namespace App\Controller;

use App\Factory\ViewRendererFactory;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use UnitEnum;

abstract class BaseController implements ControllerInterface
{
    use ContainerAwareTrait;

    protected const EXCEPTION_404_TEMPLATE = '404';

    protected ViewRendererFactory $viewRenderer;

    protected function getRoute(
        string $route,
        array $parameters = [],
        int $referenceType = Router::ABSOLUTE_URL
    ) {
        return $this->getRouter('router.main')
                    ->generate($route, $parameters, $referenceType);
    }

    protected function getRouter($routerName) : Router
    {
        return $this->container->get($routerName);
    }

    public function getContainer()
    {
        return $this->container;
    }

    protected function getRequest() : Request
    {
        return $this->getContainer()
                ->get('request_stack')
                ->getCurrentRequest();
    }

    protected function getSession() : Session
    {
        return $this->getRequest()
                ->getSession();
    }

    protected function renderView(string $action, array $args = [], int $status = 200) : Response
    {
        $template = $this->getControllerModule() . '/' . $action . '.html.twig';

        return $this->viewRenderer->render($template, $args, $status);
    }
}
