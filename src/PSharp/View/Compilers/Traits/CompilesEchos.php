<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template echos.
 *
 */
trait CompilesEchos
{
    /**
     * @var array
     * @access private
     */
    protected $echoTypes = ['Raw','Regular','Escaped'];

    /**
     * Compiles echos to valid php.
     *
     * @param mixed $value
     * @return string
     */
    protected function compileEchos($value)
    {
        $result = $value;
        //
        foreach ($this->echoTypes as $type) {
            if (method_exists($this, $method = "compile{$type}Echos")) {
                $result = $this->{$method}($result);
            }
        }
        //
        return $result;
    }

    /**
     * Compiles '{!! !!}' echos to valid php.
     *
     * @param mixed $value
     * @return string
     */
    protected function compileRawEchos($value)
    {
        return preg_replace_callback(self::RGX_ECHO_RAW, function($match) {
            $whitespace = empty($match[3]) ? '' : $match[3].$match[3];
            //
            return $match[1]
                ? substr($match[0], 1)
                : "<?php echo {$match[2]}; ?>{$whitespace}";
        }, $value);
    }

    /**
     * Compiles '{{ }}' echos to valid php.
     *
     * @param mixed $value
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        return preg_replace_callback(self::RGX_ECHO_REGULAR, function($match) {
            $whitespace = empty($match[3]) ? '' : $match[3].$match[3];
            $wrapped = sprintf(self::STR_ECHO_FORMAT, $match[2]);
            //
            return $match[1]
                ? substr($match[0], 1)
                : "<?php echo {$wrapped}; ?>{$whitespace}";
        }, $value);
    }

    /**
     * Compiles '{{{ }}}' echos to valid php.
     *
     * @param mixed $value
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        return preg_replace_callback(self::RGX_ECHO_ESCAPED, function($match) {
            $whitespace = empty($match[3]) ? '' : $match[3].$match[3];
            //
            return $match[1]
                ? substr($match[0], 1)
                : "<?php echo e({$match[2]}); ?>{$whitespace}";
        }, $value);
    }
}