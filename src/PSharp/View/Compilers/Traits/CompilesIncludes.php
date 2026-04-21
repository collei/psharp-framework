<?php
namespace PSharp\View\Compilers\Traits;

use PSharp\Support\Str;

/**
 * Provides capabilities for compiling template includes.
 *
 */
trait CompilesIncludes
{
    /**
     * Compile the include statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileInclude($expression)
    {
        $expression = Str::stripParentheses($expression);

        return "<?php echo \$__env->make({$expression}, \PSharp\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

}