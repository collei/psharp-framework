<?php
namespace PSharp\View\Traits;

use PSharp\Support\Arr;
use Countable;

/**
 * Provides capabilities for processing template loop constructs.
 *
 */
trait FactoryLoops
{
    /**
     * @var array
     * @access private
     */
    protected $loopsStack = [];

    /**
     * Handles loop starts.
     *
     * @param array|\Countable $data
     * @return void
     */
    public function addLoop($data)
    {
        $length = (is_array($data) || $data instanceof Countable) ? count($data) : null;
        //
        $parent = Arr::last($this->loopsStack);
        //
        $this->loopsStack[] = [
            'iteration' => 0,
            'index' => 0,
            'remaining' => $length ?? null,
            'count' => $length,
            'first' => true,
            'last' => (isset($length) ? $length == 1 : null),
            'odd' => false,
            'even' => true,
            'depth' => count($this->loopsStack) + 1,
            'parent' => ($parent ? (object) $parent : null),
        ];
    }

    /**
     * Updates loop iteration data.
     *
     * @return void
     */
    public function increaseLoop()
    {
        $loop = $this->loopsStack[$index = count($this->loopsStack) - 1];
        //
        $this->loopsStack[$index] = array_merge($this->loopsStack[$index], [
            'iteration' => $loop['iteration'] + 1,
            'index' => $loop['iteration'],
            'first' => $loop['iteration'] == 0,
            'odd' => ! $loop['odd'],
            'even' => ! $loop['even'],
            'remaining' => isset($loop['count']) ? $loop['remaining'] - 1 : null,
            'last' => isset($loop['count']) ? $loop['iteration'] == $loop['count'] - 1 : null,
        ]);
    }

    /**
     * Handles loop ends.
     *
     * @return void
     */
    public function popLoop()
    {
        array_pop($this->loopsStack);
    }

    /**
     * Retrieves the loop at top of the stack.
     *
     * @return    \stdClass
     */
    public function getLastLoop()
    {
        if ($last = Arr::last($this->loopsStack)) {
            return (object) $last;
        }
    }

    /**
     * Retrieves the loop stack (items come as array).
     *
     * @return array
     */
    public function getloopsStack()
    {
        return $this->loopsStack;
    }
}
