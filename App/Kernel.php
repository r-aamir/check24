<?php

namespace App;

use App\Controller\ControllerInterface;
use App\DependencyInjection\Loader\CoreBundleLoader;
use App\DependencyInjection\Loader\XmlFileLoader;
use App\Form\Base\BaseFormInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected $cacheRefresh;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, (bool) $debug);

        if ($debug) {
            Debug::enable();
        }
    }

    protected function configureContainer(ContainerConfigurator $container) : void
    {
        $container->import(APPLICATION_PATH . 'Config/*.yaml');
        $container->import(APPLICATION_PATH . 'Config/{packages}/*.yaml');
        $container->import(APPLICATION_PATH . 'Config/{packages}/' . $this->environment . '/*.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes) : void
    {
    }

    private function preBoot() : ContainerInterface
    {
        if ($this->debug) {
            $this->startTime = microtime(true);
        }
        if ($this->debug && ! isset($_ENV['SHELL_VERBOSITY']) && ! isset($_SERVER['SHELL_VERBOSITY'])) {
            putenv('SHELL_VERBOSITY=3');
            $_ENV['SHELL_VERBOSITY']    = 3;
            $_SERVER['SHELL_VERBOSITY'] = 3;
        }

        $this->initializeBundles();
        $this->initializeContainer();

        return $this->container;
    }

    /**
     * @throws Exception
     */
    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true) : Response
    {
        if (! $this->booted) {
            $container = $this->container ?? $this->preBoot();

            if ($container->has('http_cache')) {
                return $container->get('http_cache')->handle($request, $type, $catch);
            }
        }

        $this->boot();

        return parent::handle($request, $type, $catch);
    }

    protected function loadConfiguration(ContainerBuilder $container) : void
    {
        $phpLoader = new PhpFileLoader($container, new FileLocator(APPLICATION_PATH . 'Config'));
        $phpLoader->load('services.php');

        $autoconfiguredInterfaces = [
            BaseFormInterface::class   => 'form.class',
            ControllerInterface::class => 'controller.service_arguments',
        ];

        foreach ($autoconfiguredInterfaces as $interfaceClass => $tag) {
            $container->registerForAutoconfiguration($interfaceClass)
                ->addTag($tag);
        }

        $loader = new XmlFileLoader($container, $phpLoader->getLocator());
        $finder = Finder::create()
            ->name('*.xml')
            ->depth(0)
            ->in(APPLICATION_PATH . 'Config');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $loader->load($file->getBaseName());
        }
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     * @throws Exception
     */
    protected function buildContainer() : ContainerBuilder
    {
        $container = parent::buildContainer();

        $this->loadConfiguration($container);

        return $container;
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     */
    public function getCacheDir() : string
    {
            return ENV_PATH . '/var/cache/' . $this->environment;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir() : string
    {
        return ENV_PATH . '/var/log/';
    }

    /**
     * Returns the kernel parameters.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters() : array
    {
        $parameters = parent::getKernelParameters();

        $parameters['kernel.runtime_environment'] = $this->environment;
        $parameters['app.root_dir']               = APPLICATION_PATH;

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles() : iterable
    {
        $contents = [
            CoreBundleLoader::class => ['all' => true],
        ];

        if (file_exists(ENV_PATH . '/config/bundles.php')) {
            $contents = array_merge($contents, require ENV_PATH . '/config/bundles.php');
        }

        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }
}
