<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template conditionals.
 *
 */
trait CompilesConditionals
{
    /**
     * @var bool
     * @access private
     */
    protected $firstCaseInSwitch;

    /**
     * Compiles '@if' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compiles '@elseif' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compiles '@else' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileElse($expression)
    {
        return "<?php else: ?>";
    }

    /**
     * Compiles '@endif' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileEndif($expression)
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@unless' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileUnless($expression)
    {
        return "<?php if (! {$expression}): ?>";
    }
    
    /**
     * Compiles '@endunless' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileEndunless($expression)
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@isset' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileIsset($expression)
    {
        return "<?php if (isset{$expression}): ?>";
    }
    
    /**
     * Compiles '@endisset' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileEndisset($expression)
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@notempty' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileNotempty($expression)
    {
        return "<?php if (! empty{$expression}): ?>";
    }
    
    /**
     * Compiles '@endnotempty' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileEndnotempty($expression)
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@switch' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileSwitch($expression)
    {
        $this->firstCaseInSwitch = true;
        //
        return "<?php switch{$expression}:";
    }

    /**
     * Compiles '@case' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileCase($expression)
    {
        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;
            //
            return "case {$expression}: ?>";
        }
        //
        return "<?php case {$expression}: ?>";
    }

    /**
     * Compiles '@default' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileDefault($expression)
    {
        return "<?php default: ?>";
    }

    /**
     * Compiles '@endswitch' to valid php.
     *
     * @param string $expression
     * @return string
     */
    public function compileEndswitch($expression)
    {
        return "<?php endswitch; ?>";
    }

    /**
     * Compiles '@auth' to valid php.
     *
     * @param string $guard
     * @return string
     */
    public function compileAuth($guard = null)
    {
        $guard = empty($guard) ? '()' : $guard;
        //
        return "<?php if(auth()->guard{$guard}->check()): ?>";
    }

    /**
     * Compiles '@elseauth' to valid php.
     *
     * @param string $guard
     * @return string
     */
    public function compileElseauth($guard = null)
    {
        $guard = empty($guard) ? '()' : $guard;
        //
        return "<?php elseif(auth()->guard{$guard}->check()): ?>";
    }

    /**
     * Compiles '@endauth' to valid php.
     *
     * @return string
     */
    public function compileEndauth()
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@auths' to valid php.
     *
     * @param string $guards
     * @return string
     */
    public function compileAuths($guards = null)
    {
        $guards = empty($guards) ? '()' : $guards;
        //
        return "<?php if(auth()->authorizes{$guards}): ?>";
    }

    /**
     * Compiles '@elseauths' to valid php.
     *
     * @param string $guards
     * @return string
     */
    public function compileElseauths($guards = null)
    {
        $guards = empty($guards) ? '()' : $guards;
        //
        return "<?php elseif(auth()->authorizes{$guards}): ?>";
    }

    /**
     * Compiles '@endauths' to valid php.
     *
     * @return string
     */
    public function compileEndauths()
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@userwith' to valid php.
     *
     * @param string $params
     * @return string
     */
    public function compileUserwith($params = null)
    {
        $params = empty($params) ? '()' : $params;
        //
        return "<?php if(auth()->userHas{$guards}): ?>";
    }

    /**
     * Compiles '@elseuserwith' to valid php.
     *
     * @param string $params
     * @return string
     */
    public function compileElseuserwith($params = null)
    {
        $params = empty($params) ? '()' : $params;
        //
        return "<?php elseif(auth()->userHas{$params}): ?>";
    }

    /**
     * Compiles '@enduserwith' to valid php.
     *
     * @return string
     */
    public function compileEnduserwith()
    {
        return "<?php endif; ?>";
    }

    /**
     * Compiles '@guest' to valid php.
     *
     * @param string $guard
     * @return string
     */
    public function compileGuest($guard = null)
    {
        $guard = empty($guard) ? '()' : $guard;
        //
        return "<?php if(auth()->guard{$guard}->guest()): ?>";
    }

    /**
     * Compiles '@elseguest' to valid php.
     *
     * @param string $guard
     * @return string
     */
    public function compileElseguest($guard = null)
    {
        $guard = empty($guard) ? '()' : $guard;
        //
        return "<?php elseif(auth()->guard{$guard}->guest()): ?>";
    }

    /**
     * Compiles '@endguest' to valid php.
     *
     * @return string
     */
    public function compileEndguest()
    {
        return "<?php endif; ?>";
    }
}