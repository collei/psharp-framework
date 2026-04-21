<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template injections.
 *
 */
trait CompilesInjections
{
    /**
     * Compiles '@inject' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileInject($expression)
    {
        $segments = explode(',', preg_replace('/[\(\)\"\']/', '', $expression.','));

        [$variable, $service] = $segments;

        return sprintf('<?php $%s = app(\'%s\'); ?>', trim($variable), trim($service));
    }
}