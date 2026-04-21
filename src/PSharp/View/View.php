<?php
namespace PSharp\View;

use PSharp\View\Interfaces\FactoryInterface;
use PSharp\View\Interfaces\RepositoryInterface;
use PSharp\View\Interfaces\ViewInterface;
use PSharp\Support\Str;
use PSharp\Support\Interfaces\Renderable;
use Exception;
use Error;
use Throwable;

/**
 * Embodies the View itself and its capabilities.
 *
 */
class View implements ViewInterface
{
    /**
     * @var string
     * @access private
     */
    public const VIEW_SUFFIX = '.vis.php';

    /**
     * @var \PSharp\View\Interfaces\FactoryInterface
     * @access private
     */
    protected $factory;

    /**
     * @var \PSharp\View\Interfaces\RepositoryInterface
     * @access private
     */
    protected $repository;

    /**
     * @var string
     * @access private
     */
    protected $name;

    /**
     * @var array
     * @access private
     */
    protected $data;

    /**
     * @var string
     * @access private
     */
    protected $sourcePath;

    /**
     * @var string
     * @access private
     */
    protected $compiledPath;

    /**
     * Instantiate me.
     *
     * @param    \PSharp\View\Interfaces\FactoryInterface $factory
     * @param    \PSharp\View\Interfaces\RepositoryInterface $repository
     * @param string $name
     * @param string $path
     * @param array $data = []
     * @return void
     */
    public function __construct(
        FactoryInterface $factory,
        RepositoryInterface $repository,
        string $name, string $path, $data = []
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->name = $name;
        $this->data = $data;
        //
        $this->compiledPath = $this->repository->getCompiler()->getCompiledPath(
            $this->sourcePath = $path
        );
    }

    /**
     * Retrieves the view name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retrieves the view source path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->sourcePath;
    }

    /**
     * Causes the view to render.
     *
     * @param callable|null $callback
     * @return string
     */
    public function render(callable $callback = null)
    {
        try {
            $contents = $this->renderContents();
            //
            $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;
            //
            $this->factory->flushStateIfDoneRendering();
            //
            return $response ?? $contents;
        } catch (Exception $e) {
            $this->factory->flushState();
            //
            throw $e;
        } catch (Throwable $e) {
            $this->factory->flushState();
            //
            throw $e;
        }
    }

    /**
     * Joins data to the view instance.
     *
     * @param array|string $name
     * @param mixed $value
     * @return $this
     */
    public function with($name, $value = null)
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } elseif (is_string($name) || is_numeric($name)) {
            $this->data["{$name}"] = $value;
        }
        //
        return $this;
    }

    /**
     * Render the view contents.
     *
     * @return string
     */
    protected function renderContents()
    {
        $this->factory->incrementRender();
        //
        $contents = $this->getContents();
        //
        $this->factory->decrementRender();
        //
        return $contents;
    }

    /**
     * Retrieves the contents.
     *
     * @return string
     */
    protected function getContents()
    {
        if (! file_exists($this->compiledPath)) {
            $this->compiledPath = $this->repository->compile($this->name);
        }
        //
        return $this->evaluatePath($this->compiledPath, $this->gatherData());
    }

    /**
     * Runs the compiled file with the data assigned.
     *
     * @param string $__path
     * @param array $__data
     * @return string
     */
    protected function evaluatePath($__path, $__data)
    {
        $level = ob_get_level();
        //
        ob_start();
        //
        extract($__data, EXTR_SKIP);
        //
        try {
            include $__path;
        } catch (Exception $e) {
            $this->handleException($e, $level);
        } catch (Throwable $e) {
            $this->handleException($e, $level);
        }
        //
        return ltrim(ob_get_clean());
    }

    /**
     * Retrieves the assigned data.
     *
     * @return array
     */
    protected function gatherData()
    {
        $data = array_merge($this->factory->getShared(), $this->data);
        //
        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }
        //
        return $data;
    }

    /**
     * Handles the given exception.
     *
     * @param    \Throwable $error
     * @param int $level
     * @return void
     *
     * @throws    \Throwable
     */
    protected function handleException(Throwable $error, int $level)
    {
        $this->errors[] = compact('error','level');
        //
        throw $error;
    }
}