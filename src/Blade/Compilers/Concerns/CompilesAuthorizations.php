<?php

namespace Swoft\Blade\Compilers\Concerns;

trait CompilesAuthorizations
{
    /**
     * Compile the can statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileCan($expression)
    {
        return "<?php if (0): ?>";
    }

    /**
     * Compile the cannot statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileCannot($expression)
    {
        return "<?php if (0): ?>";
    }

    /**
     * Compile the canany statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileCanany($expression)
    {
        return "<?php if (0): ?>";
    }

    /**
     * Compile the else-can statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileElsecan($expression)
    {
        return "<?php elseif (0): ?>";
    }

    /**
     * Compile the else-cannot statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileElsecannot($expression)
    {
        return "<?php elseif (0): ?>";
    }

    /**
     * Compile the else-canany statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileElsecanany($expression)
    {
        return "<?php elseif (0): ?>";
    }

    /**
     * Compile the end-can statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndcan()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the end-cannot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndcannot()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the end-canany statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndcanany()
    {
        return '<?php endif; ?>';
    }
}
