<?php



namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class RegisterRouterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        try {
            $chainRouter = $container->getDefinition('router.chainRequest');
        } catch (InvalidArgumentException $e) {
            return;
        }

        $router = $container->getDefinition('router.default');
        $chainRouter->addMethodCall('add', [new Reference('router.default'), 512]);

        foreach ($container->findTaggedServiceIds('router.register') as $id => $attributes) {
            $priority = $attributes[0]['priority'] ?? 0;
            $router   = $container->getDefinition($id);
            $router->addMethodCall('setOption', ['cache_dir', ENV_PATH . '/var/cache/' . $container->getParameter('kernel.environment') . '/routing/' . $id]);

            $chainRouter->addMethodCall('add', [new Reference($id), $priority]);
        }
    }
}
