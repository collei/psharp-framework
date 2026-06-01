<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template dumps.
 *
 */
trait CompilesDumps
{
    /**
     * Compiles '@dump' to valid php.
     *
     * @param string $expression
     * @return string
     */
    protected function compileDump($expression)
    {
        return '<?php echo pretty_dump([\'@dump\' => ('.$expression.')], true); ?>';
    }
}