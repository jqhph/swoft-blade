<?php

namespace Swoft\Blade;

use Swoft\Blade\Compilers\BladeCompiler;
use Swoft\Blade\Compilers\CompilerInterface;
use Swoft\Blade\Contracts\View;

/**
 * @method static void compile($path = null)
 * @method static string getPath()
 * @method static void setPath($path)
 * @method static string compileString($value)
 * @method static string stripParentheses($expression)
 * @method static void extend(callable $compiler)
 * @method static array getExtensions()
 * @method static void if($name, callable $callback)
 * @method static bool check($name, ...$parameters)
 * @method static void component($path, $alias = null)
 * @method static void include($path, $alias = null)
 * @method static void directive($name, callable $handler)
 * @method static array getCustomDirectives()
 * @method static void setEchoFormat($format)
 * @method static void withDoubleEncoding()
 * @method static void withoutDoubleEncoding()
 *
 * @see BladeCompiler
 */
class Blade
{
    /**
     * @return View
     */
    public static function view()
    {
        return bean('blade.view');
    }

    /**
     * @return CompilerInterface
     */
    public static function compiler()
    {
        return bean('blade.view')->getEngineResolver()->resolve('blade')->getCompiler();
    }

    public function __call($name, $arguments)
    {
        $compiler = bean('blade.view')->getEngineResolver()->resolve('blade')->getCompiler();

        return $compiler->$name(...$arguments);
    }
}
