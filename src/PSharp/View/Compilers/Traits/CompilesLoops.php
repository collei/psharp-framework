<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template loop constructs.
 *
 */
trait CompilesLoops
{
    /**
     * @var int
     * @access private
     */
    protected $forElseCount = 0;

    /**
     * Compiles '@foreach' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileForeach($expression)
    {
        preg_match(self::RGX_LOOP_START, $expression, $matches);

        $list = trim($matches[1]);
        $item = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$list} ?? []; \$__env->addLoop(\$__currentLoopData);";
        $iterateLoop = '$__env->increaseLoop(); $loop = $__env->getLastLoop();';

        return "<?php {$initLoop} foreach(\$__currentLoopData as {$item}): {$iterateLoop} ?>";
    }

    /**
     * Compiles '@endforeach' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndforeach($expression)
    {
        return '<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    }

    /**
     * Compiles '@forelse' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileForelse($expression)
    {
        $empty = '$__empty_'.(++$this->forElseCount);

        preg_match(self::RGX_LOOP_START, $expression, $matches);

        $list = trim($matches[1]);
        $item = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$list} ?? []; \$__env->addLoop(\$__currentLoopData);";
        $iterateLoop = '$__env->increaseLoop(); $loop = $__env->getLastLoop();';

        return "<?php {$empty} = true; {$initLoop} foreach(\$__currentLoopData as {$item}): {$iterateLoop} {$empty} = false; ?>";
    }

    /**
     * Compiles '@empty' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEmpty($expression)
    {
        if ($expression) {
            return "<?php if(empty{$expression}): ?>";
        }
        //
        $empty = '$__empty_'.($this->forElseCount--);
        //
        return "<?php endforeach; \$__env->popLoop(); \$loop = \$__env->getLastLoop(); if ({$empty}): ?>";
    }

    /**
     * Compiles '@endforelse' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndforelse($expression)
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@endempty' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndempty($expression)
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@while' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Compiles '@endwhile' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndwhile($expression)
    {
        return "<?php endwhile; ?>";
    }

    /**
     * Compiles '@for' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compiles '@break' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileBreak($expression)
    {
        if ($expression) {
            preg_match(self::RGX_LOOP_BREAK, $expression, $match);
            //
            return $matches
                ? ('<?php break '.max(1, $matches[1]).'; ?>')
                : "<?php if{$expression} break; ?>";
        }
        //
        return '<?php break; ?>';
    }

    /**
     * Compiles '@continue' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileContinue($expression)
    {
        if ($expression) {
            preg_match(self::RGX_LOOP_CONTINUE, $expression, $match);
            //
            return $matches
                ? ('<?php continue '.max(1, $matches[1]).'; ?>')
                : "<?php if{$expression} continue; ?>";
        }
        //
        return '<?php continue; ?>';
    }

    /**
     * Compiles '@endfor' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileEndfor($expression)
    {
        return "<?php endfor; ?>";
    }
}
