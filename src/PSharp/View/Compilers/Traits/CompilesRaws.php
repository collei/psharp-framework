<?php
namespace PSharp\View\Compilers\Traits;

/**
 * Provides capabilities for compiling template raw output methods.
 *
 */
trait CompilesRaws
{
    /**
     * @var array
     * @access private
     */
    protected $rawBlocks = [];

    /**
     * Stores @verbatim sections.
     *
     * @param mixed $value
     * @return string
     */
    protected function storeVerbatim($value)
    {
        return preg_replace_callback(self::RGX_VERBATIM, function ($match) {
            return $this->storeRawBlock($match[1]);
        }, $value);
    }

    /**
     * Stores @php sections.
     *
     * @param mixed $value
     * @return string
     */
    protected function storeRawPhp($value)
    {
        return preg_replace_callback(self::RGX_PHP_RAW, function ($match) {
            return $this->storeRawBlock('<?php '.$match[1].' ?>');
        }, $value);
    }

    /**
     * Stores raw blocks.
     *
     * @param mixed $value
     * @return string
     */
    protected function storeRawBlock($value)
    {
        return $this->getRawPlaceholder(
            array_push($this->rawBlocks, $value) - 1
        );
    }

    /**
     * Restores raw blocks.
     *
     * @param mixed $result
     * @return string
     */
    protected function restoreRawBlock($result)
    {
        $placeholders = '/'.$this->getRawPlaceholder('(\d+)').'/';
        //
        $result = preg_replace_callback($placeholders, function ($match) {
            return $this->rawBlocks[$match[1]];
        }, $result);
        //
        $this->rawBlocks = [];
        //
        return $result;
    }

    /**
     * Retrieves the placeholder.
     *
     * @param mixed $replace
     * @return string
     */
    protected function getRawPlaceholder($replace)
    {
        return str_replace('#', $replace, self::STR_RAW_PLACEHOLDER);
    }
}