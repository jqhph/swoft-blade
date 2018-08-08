<?php

namespace Swoft\Blade\Listeners;

use Swoft\App;
use Swoft\Bean\Annotation\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Http\Server\Event\HttpServerEvent;
use Swoft\Http\Server\ServerDispatcher;
use Swoft\Blade\Middlewares\AssetsMiddleware;
use Swoft\Support\HttpFileReader;

/**
 *
 * @Listener(HttpServerEvent::BEFORE_REQUEST)
 */
class BeforeRequest implements EventHandlerInterface
{
    /**
     * @var bool
     */
    protected static $registed;

    /**
     * @var array
     */
    protected $middlewares = [
        AssetsMiddleware::class
    ];

    /**
     * @param \Swoft\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        if (static::$registed) {
            return;
        }
        static::$registed = true;

        // 注册视图命名空间
        $factory = blade_factory();
        if ($namespaces = config('blade-view.namespaces')) {
            foreach ((array)$namespaces as $namespace => &$path) {
                $factory->addNamespace($namespace, App::getAlias($path));
            }
        }

        // 注册静态资源目录
        if ($paths = config('blade-view.assets')) {
            foreach ((array)$paths as &$path) {
                HttpFileReader::addAssetsPath($path);
            }
        }

        // 判断是否允许读取静态资源
        $readAssets = config('blade-view.read-assets');

        /* @var ServerDispatcher $serverDispatcher */
        $serverDispatcher = \Swoft::getBean('serverDispatcher');

        foreach ($this->middlewares as $name => $middleware) {
            if (!$readAssets && $middleware === AssetsMiddleware::class) {
                continue;
            }

            $serverDispatcher->addMiddleware(
                $middleware,
                is_string($name) && !empty($name) ? $name : null
            );
        }
    }
}
