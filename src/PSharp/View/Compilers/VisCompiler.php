<?php
namespace PSharp\View\Compilers;

use PSharp\Support\Str;
use PSharp\View\Interfaces\CompilerInterface;

/**
 * Embodies the Vis template compiler.
 */
class VisCompiler extends Compiler implements CompilerInterface
{
    use Traits\CompilesRaws;
    use Traits\CompilesEchos;
    use Traits\CompilesConditionals;
    use Traits\CompilesHelpers;
    use Traits\CompilesIncludes;
    use Traits\CompilesLoops;
    use Traits\CompilesLayouts;
    use Traits\CompilesInjections;

    /**
     * @var string RGX_VERBATIM
     * @access protected
     */
    protected const RGX_VERBATIM = '/(?<!@)@verbatim(.*?)@endverbatim/s';

    /**
     * @var string RGX_PHP_RAW
     * @access protected
     */
    protected const RGX_PHP_RAW = '/(?<!@)@php(.*?)@endphp/s';

    /**
     * @var string RGX_STATEMENT
     * @access protected
     */
    protected const RGX_STATEMENT = '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x';

    /**
     * @var string RGX_COMMENT
     * @access protected
     */
    protected const RGX_COMMENT = '/{{--(.*?)--}}/s';

    /**
     * @var string RGX_ECHO_RAW
     * @access protected
     */
    protected const RGX_ECHO_RAW = '/(@)?{!!\s*(.+?)\s*!!}(\r?\n)?/s';

    /**
     * @var string RGX_ECHO_REGULAR
     * @access protected
     */
    protected const RGX_ECHO_REGULAR = '/(@)?{{\s*(.+?)\s*}}(\r?\n)?/s';

    /**
     * @var string RGX_ECHO_ESCAPED
     * @access protected
     */
    protected const RGX_ECHO_ESCAPED = '/(@)?{{{\s*(.+?)\s*}}}(\r?\n)?/s';

    /**
     * @var string RGX_VALID_DIRECTIVE_NAME
     * @access protected
     */
    protected const RGX_VALID_DIRECTIVE_NAME = '/^\w+(?:::\w+)?$/x'; 

    /**
     * @var string RGX_LOOP_START
     * @access protected
     */
    protected const RGX_LOOP_START = '/\( *(.*) +as *(.*)\)$/is';

    /**
     * @var string RGX_LOOP_CONTINUE
     * @access protected
     */
    protected const RGX_LOOP_CONTINUE = '/\(\s*(-?\d+)\s*\)$/';

    /**
     * @var string RGX_LOOP_BREAK
     * @access protected
     */
    protected const RGX_LOOP_BREAK = '/\(\s*(-?\d+)\s*\)$/';

    /**
     * @var string STR_RAW_PLACEHOLDER
     * @access protected
     */
    protected const STR_RAW_PLACEHOLDER = '@__raw_block_#__@';

    /**
     * @var string STR_ECHO_FORMAT
     * @access protected
     */
    protected const STR_ECHO_FORMAT = 'e(%s)';

    /**
     * @var array
     * @access protected
     */
    protected $extensions = [];

    /**
     * @var array
     * @access protected
     */
    protected $customDirectives = [];

    /**
     * @var array
     * @access protected
     */
    protected $footer = [];

    /**
     * Compiles the source.
     *
     * @param string $value
     * @return string
     */
    public function compile($value)
    {
        if (false !== strpos($value, '@verbatim')) {
            $value = $this->storeVerbatim($value);
        }
        //
        $this->footer = [];
        //
        if (false !== strpos($value, '@php')) {
            $value = $this->storeRawPhp($value);
        }
        //
        $result = '';
        //
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }
        //
        if (! empty($this->rawBlocks)) {
            $result = $this->restoreRawBlock($result);
        }
        //
        if (count($this->footer) > 0) {
            $result = $this->addFooters($result);
        }
        //
        return $result;
    }

    /**
     * Parses the token.
     *
     * @param string $value
     * @return string
     * @access protected
     */
    protected function parseToken($token)
    {
        [$id, $content] = $token;
        //
        if ($id == T_INLINE_HTML) {
            $content = $this->compileComments($content);
            $content = $this->compileExtensions($content);
            $content = $this->compileStatements($content);
            $content = $this->compileEchos($content);
        }
        //
        return $content;
    }

    /**
     * Compiles comments.
     *
     * @param string $value
     * @return string
     * @access protected
     */
    protected function compileComments($value)
    {
        return preg_replace(self::RGX_COMMENT, '', $value);
    }

    /**
     * Compiles extensions.
     *
     * @param string $value
     * @return string
     * @access protected
     */
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler) {
            $value = call_user_func($compiler, $value, $this);
        }
        //
        return $value;
    }

    /**
     * Compiles statements.
     *
     * @param string $value
     * @return string
     * @access protected
     */
    protected function compileStatements($value)
    {
        return preg_replace_callback(self::RGX_STATEMENT, function ($match) {
            return $this->compileStatement($match);
        }, $value);
    }

    /**
     * Compiles a custom statement.
     *
     * @param string $value
     * @return string
     * @access protected
     */
    protected function compileStatement($match)
    {
        if (false !== strpos($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1]; 
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->callDirective($match[1], ($match[3] ?? ''));
        } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
            $match[0] = $this->$method($match[3] ?? '');
        }
        //
        return isset($match[3]) ? $match[0] : $match[0].$match[2];
    }

    /**
     * Call custom directives.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @access protected
     */
    protected function callDirective($name, $value)
    {
        $value = Str::stripParentheses($value);
        //
        return call_user_func($this->customDirectives[$name], trim($value));
    }

    /**
     * Add all footers in reverse order.
     *
     * @param string $result
     * @return string
     * @access protected
     */
    protected function addFooters($result)
    {
        return ltrim($result, PHP_EOL)
            . PHP_EOL
            . implode(PHP_EOL, array_reverse($this->footer));
    }

    /**
     * Register extensions with the compiler.
     *
     * @param callable $compiler
     * @return void
     */
    public function extend(callable $compiler)
    {
        $this->extensions[] = $compiler;
    }

    /**
     * Retrieves all registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Register custom 'if' statements.
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function if($name, callable $callback)
    {
        $this->conditions[$name] = $callback;
        //
        $this->customDirective($name, function ($expression) use ($name) {
            return ($expression !== '')
                    ? "<?php if (\\Vis::check('{$name}', {$expression})): ?>"
                    : "<?php if (\\Vis::check('{$name}')): ?>";
        });
        //
        $this->customDirective('else'.$name, function ($expression) use ($name) {
            return ($expression !== '')
                    ? "<?php elseif (\\Vis::check('{$name}', {$expression})): ?>"
                    : "<?php elseif (\\Vis::check('{$name}')): ?>";
        });
        //
        $this->customDirective('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Execute check of custom 'if' statements.
     *
     * @param string $name
     * @param mixed ...$parameters
     * @return mixed
     */
    public function check($name, ...$parameters)
    {
        return call_user_func($this->conditions[$name], ...$parameters);
    }

    /**
     * Register custom directives.
     *
     * @param string $name
     * @param callable $handler
     * @return void
     */
    public function customDirective($name, callable $handler)
    {
        if (! preg_match(self::RGX_VALID_DIRECTIVE_NAME, $name)) {
            $message = 'The directive name [%s] is not valid. Directive names'
                . ' must only contain alphanumeric characters and underscores.';
            //
            throw new InvalidArgumentException(sprintf($message, $name));
        }
        //
        $this->customDirectives[$name] = $handler;
    }
}