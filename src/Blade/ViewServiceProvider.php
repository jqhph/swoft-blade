<?php

namespace Swoft\Blade;

use Swoft\Bean\Annotation\BootBean;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Container;
use Swoft\Blade\Compilers\BladeCompiler;
use Swoft\Blade\Engines\CompilerEngine;
use Swoft\Blade\Engines\EngineResolver;
use Swoft\Blade\Engines\FileEngine;
use Swoft\Blade\Engines\PhpEngine;
use Swoft\Core\BootBeanInterface;

/**
 * @BootBean()
 */
class ViewServiceProvider implements BootBeanInterface
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct()
    {
        $this->container = BeanFactory::getContainer();
    }

    public function beans()
    {
        $this->register();

        return [];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerViewFinder();

        $this->registerEngineResolver();

        $this->registerFactory();
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->container->addDefinitions([
            'blade.view' => [
                'class' => Factory::class,
                'name'  => 'blade.view',
            ]
        ]);

        /* @var Factory $factory */
        $factory = $this->container->get('blade.view');

        $factory->setEngineResolver(
            $this->container->get('view.engine.resolver')
        );
        $factory->setViewFinder(
            $this->container->get('view.finder')
        );
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->container->addDefinitions([
            'view.finder' => [
                'class' => FileViewFinder::class,
                'name'  => 'view.finder',
            ]
        ]);
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->container->addDefinitions([
            'view.engine.resolver' => [
                'class' => EngineResolver::class,
                'name'  => 'view.engine.resolver',
            ]
        ]);

        $resolver = $this->container->get('view.engine.resolver');


        foreach (['file', 'php', 'blade'] as $engine) {
            $this->{'register'.ucfirst($engine).'Engine'}($resolver);
        }
    }

    /**
     * Register the file engine implementation.
     *
     * @param  \Swoft\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerFileEngine($resolver)
    {
        $resolver->register('file', function () {
            return new FileEngine();
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Swoft\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Swoft\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $this->container->addDefinitions([
            'blade.compiler' => [
                'class' => BladeCompiler::class,
                'name'  => 'blade.compiler',
            ]
        ]);

        $resolver->register('blade', function () {
            return new CompilerEngine($this->container->get('blade.compiler'));
        });
    }
}
