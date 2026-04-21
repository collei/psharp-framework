<?php
namespace PSharp\View;

use PSharp\Core\Container;
use PSharp\View\Interfaces\FactoryInterface;
use PSharp\View\Interfaces\RepositoryInterface;

/**
 * The view factory.
 *
 */
class Factory implements FactoryInterface
{
    use Traits\FactoryLayouts;
    use Traits\FactoryLoops;

    /**
     * @var \PSharp\Core\Container
     * @access protected
     */
    protected $container;

    /**
     * @var \PSharp\View\Interfaces\RepositoryInterface
     * @access protected
     */
    protected $repository;

    /**
     * @var array
     * @access protected
     */
    protected $shared = [];

    /**
     * @var int
     * @access protected
     */
    protected $renderCount = 0;

    /**
     * Instantiate me.
     *
     * @param    \PSharp\Core\Container $container
     * @param    \PSharp\View\Interfaces\RepositoryInterface $repository
     * @return void
     */
    public function __construct(Container $container, RepositoryInterface $repository)
    {
        $this->container = $container;
        $this->repository = $repository;
        //
        $this->share('__env', $this);
        //
        $this->getFlashedFromSession();
    }

    /**
     * Injects flashed variables from previous session.
     *
     * @return void
     */
    protected function getFlashedFromSession()
    {
        if ($session = session()) {
            foreach ($session->flashed() as $name => $value) {
                $this->share($name, $value);
            }
        }
    }

    /**
     * Catter a View instance from the given file path.
     *
     * @param string $path
     * @param array|Arrayable $data
     * @param array $mergeData
     * @return    \PSharp\View\Interfaces\ViewInterface
     */
    public function file($path, $data = [], $mergeData = [])
    {
        $data = $data ?? [];
        //
        $data = array_merge(
            $mergeData, ($data instanceof Arrayable ? $data->toArray() : $data)
        );
        //
        $name = $this->repository->nameByPath($path);
        //
        return $this->viewInstance($name, $path, $data);
    }

    /**
     * Catter a View instance from the given view name.
     *
     * @param string $view
     * @param array|Arrayable $data
     * @param array $mergeData
     * @return    \PSharp\View\Interfaces\ViewInterface
     */
    public function make($view, $data = [], array $mergeData = [])
    {
        $data = $data ?? [];
        //
        $data = array_merge(
            $mergeData, ($data instanceof Arrayable ? $data->toArray() : $data)
        );
        //
        $path = $this->repository->find($view);
        //
        return $this->viewInstance($view, $path, $data);
    }

    /**
     * Catter a View instance.
     *
     * @param string $view
     * @param string $path
     * @param array $data
     * @return    \PSharp\View\Interfaces\ViewInterface
     * @throws    \PSharp\View\EmptyViewException
     */
    protected function viewInstance($view, $path, $data)
    {
        $content = file_get_contents($path);
        //
        if (empty($content)) throw new EmptyViewException(
            sprintf('Empty view source: [%s]. Please double check it.', $path)
        );
        //
        $this->repository->compile($view);
        //
        return new View($this, $this->repository, $view, $path, $data);
    }

    /**
     * Retrieves a reference to the container.
     *
     * @return    \PSharp\Core\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Retrieves a reference to the view repository.
     *
     * @return    \PSharp\View\Interfaces\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Retrieves the view stack.
     *
     * @return array
     */
    public function getViewStack()
    {
        return $this->viewStack;
    }

    /**
     * Retrieves a shared datum by $key.
     *
     * @param string $key
     * @param mixed $default = null
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * Retrieves an array with all shared data.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Share a datum across all views.
     *
     * @param string $key
     * @param mixed $value = null
     * @return mixed
     */
    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];
        //
        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }
        //
        return $value;
    }

    /**
     * Increments the render count.
     *
     * @return void
     */
    public function incrementRender()
    {
        $this->renderCount++;
    }

    /**
     * Decrements the render count.
     *
     * @return void
     */
    public function decrementRender()
    {
        $this->renderCount--;
    }

    /**
     * Determine whether the view rendering is done.
     *
     * @return bool
     */
    public function doneRendering()
    {
        return 0 == $this->renderCount;
    }

    /**
     * Flush all state.
     *
     * @return void
     */
    public function flushState()
    {
        $this->renderCount = 0;
        //
        $this->flushSections();
    }

    /**
     * Flush all state if view rendering is done.
     *
     * @return void
     */
    public function flushStateIfDoneRendering()
    {
        if ($this->doneRendering()) {
            $this->flushState();
        }
    }
}