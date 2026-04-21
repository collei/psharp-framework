<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template helpers.
 *
 */
trait CompilesHelpers
{
    /**
     * Compiles '@csrf' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileCsrf($expression)
    {
        return '<?php echo e(csrf_field()); ?>';
    }

    /**
     * Compiles '@method' to valid php.
     *
     * @param string $method
     * @return string
     */
    protected function compileMethod($method)
    {
        return '<?php echo method_field({$method}); ?>';
    }
}