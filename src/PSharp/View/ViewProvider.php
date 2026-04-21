<?php
namespace PSharp\View;

use PSharp\Core\{Application, Container, Config};
use PSharp\Core\Providers\ServiceProvider;
use PSharp\View\Interfaces\CompilerInterface;

/**
 * Encapsulates a service provider.
 */
class ViewProvider extends ServiceProvider
{
    /**
     * @var PSharp\View\ViewManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    public function register()
    {
        $conf = null;
        
        if ($driver = $this->config->get('view.default')) {
            $conf = $this->config->get("view.{$driver}");
        }

        if (empty($conf)) {
            throw new LogicException('Missing default view driver definition in \'appsettings.json\' under \'view\' section.');
        }

        $this->config = $conf;

        $this->registerFactory();
        $this->registerRepository($conf['source']);
        $this->registerCompiler($conf['compiler'], $conf['cache']);
    }

    /**
     * Register the view factory
     *
     * @return void
     */
    protected function registerFactory()
    {
        $this->container->configureBuilder(Factory::class, function(Container $container, Repository $repository) {
            return new Factory($container, $repository);
        });
    }

    /**
     * Register the view repository
     *
     * @return void
     */
    protected function registerRepository(string $source)
    {
        $this->container->configureBuilder(Repository::class, function(Container $container, CompilerInterface $compiler) use ($source) {
            $repository = new Repository($compiler);
            $repository->addLocation($source);
            return $repository;
        });
    }

    /**
     * Register the view compiler
     *
     * @return void
     */
    protected function registerCompiler(string $class, string $cache)
    {
        $this->container->addInterfaceImplementor($class, CompilerInterface::class);

        $this->container->configureBuilder($class, function() use ($class, $cache) {
            return (new $class())->withCachePath($cache);
        });
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot()
    {
        $this->container->make(Factory::class);
    }
}