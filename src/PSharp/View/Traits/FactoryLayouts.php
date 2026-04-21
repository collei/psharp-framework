<?php
namespace PSharp\View\Traits;

use PSharp\Support\Arr;
use PSharp\View\View;
use Countable;

/**
 * Provides capabilities for processing template layouts.
 *
 */
trait FactoryLayouts
{
    /**
     * @var array
     * @access private
     */
    protected $sections = [];

    /**
     * @var array
     * @access private
     */
    protected $sectionStack = [];

    /**
     * @var array
     * @access private
     */
    protected static $parentPlaceholder = [];

    /**
     * Starts a section.
     *
     * @param string $section
     * @param mixed $content = null
     * @return void
     */
    public function startSection($section, $content = null)
    {
        if (null === $content) {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content instanceof View ? $content : e($content));
        }
    }

    /**
     * Performs section injection.
     *
     * @param string $section
     * @param mixed $content = null
     * @return void
     */
    public function inject($section, $content)
    {
        $this->startSection($section, $content);
    }

    /**
     * Performs yields.
     *
     * @return mixed
     */
    public function yieldSection()
    {
        if (empty($this->sectionStack)) {
            return '';
        }
        //
        return $this->yieldContent($this->stopSection());
    }

    /**
     * Stops a section.
     *
     * @param bool $overwrite = false
     * @return mixed
     */
    public function stopSection($overwrite = false)
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }
        //
        $last = array_pop($this->sectionStack);
        //
        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }
        //
        return $last;
    }

    /**
     * Performs section appending.
     *
     * @return mixed
     */
    public function appendSection()
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }
        //
        $last = array_pop($this->sectionStack);
        //
        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }
        //
        return $last;
    }

    /**
     * Performs a section extension.
     *
     * @param string $section
     * @param mixed $content = null
     * @return void
     */
    public function extendSection($section, $content)
    {
        if (isset($this->sections[$section])) {
            $content = str_replace(
                static::parentPlaceholder($section), $content, $this->sections[$section]
            );
        }
        //
        $this->sections[$section] = $content;
    }

    /**
     * Performs content yield.
     *
     * @param string $section
     * @param mixed $default = ''
     * @return string
     */
    public function yieldContent($section, $default = '')
    {
        $sectionContent = $default instanceof View ? $default : e($default);
        //
        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
        }
        //
        $sectionContent = str_replace('@@parent', '----parent--holder----', $sectionContent);
        //
        $result = str_replace(
            '----parent-holder----', '@parent', str_replace(
                static::parentPlaceholder($section), '', $sectionContent
            )
        );
        //
        return $result;
    }

    /**
     * Handles parent placeholder.
     *
     * @param string $section = ''
     * @return string
     */
    public static function parentPlaceholder($section = '')
    {
        if (! isset(static::$parentPlaceholder[$section])) {
            static::$parentPlaceholder[$section] = '##parent-placeholder-'.sha1($section).'##';
        }
        //
        return static::$parentPlaceholder[$section];
    }

    /**
     * Tells if the given section exists.
     *
     * @param string $section
     * @return bool
     */
    public function hasSection($section)
    {
        return array_key_exists($section, $this->sections);
    }

    /**
     * Retrieves a section.
     *
     * @param string $section
     * @param mixed $default = null
     * @return mixed
     */
    public function getSection($section, $default = null)
    {
        return $this->getSections()[$section] ?? $default;
    }

    /**
     * Retrieves all sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Flush sections.
     *
     * @return void
     */
    public function flushSections()
    {
        $this->sections = [];
        $this->sectionStack = [];
    }
}

