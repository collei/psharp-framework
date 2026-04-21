<?php
namespace PSharp\View\Compilers\Traits;

use PSharp\Support\Str;
use PSharp\View\Factory as ViewFactory;

/**
 * Provides capabilities for compiling template layout methods.
 *
 */
trait CompilesLayouts
{
    /**
     * @var string
     * @access private
     */
    protected $lastSection;

    /**
     * Compiles '@extends' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileExtends($expression)
    {
        $expression = Str::stripParentheses($expression);
        //
        $echo = "<?php echo \$__env->make({$expression}, \PSharp\Support\Arr::except(get_defined_vars(), ['__data','__path']))->render(); ?>";
        //
        $this->footer[] = $echo;
        //
        return '';
    }

    /**
     * Compiles '@section' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileSection($expression)
    {
        $this->lastSection = trim($expression, "()'\" ");
        //
        return "<?php \$__env->startSection{$expression}; ?>";
    }

    /**
     * Compiles '@parent' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileParent()
    {
        return ViewFactory::parentPlaceholder($this->lastSection ?: '');
    }

    /**
     * Compiles '@yield' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileYield($expression)
    {
        return "<?php echo \$__env->yieldContent{$expression}; ?>";
    }

    /**
     * Compiles '@show' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileShow()
    {
        return "<?php echo \$__env->yieldSection(); ?>";
    }

    /**
     * Compiles '@append' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileAppend()
    {
        return "<?php \$__env->appendSection(); ?>";
    }

    /**
     * Compiles '@overwrite' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileOverwrite()
    {
        return "<?php \$__env->stopSection(true); ?>";
    }

    /**
     * Compiles '@stop' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileStop()
    {
        return "<?php \$__env->stopSection(); ?>";
    }

    /**
     * Compiles '@endsection' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndsection()
    {
        return "<?php \$__env->stopSection(); ?>";
    }
}