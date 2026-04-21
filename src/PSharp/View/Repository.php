<?php
namespace PSharp\View;

use InvalidArgumentException;
use PSharp\View\Interfaces\RepositoryInterface;
use PSharp\View\Interfaces\CompilerInterface;
use PSharp\Support\Str;

/**
 * The compiled view template repository.
 *
 */
class Repository implements RepositoryInterface
{
    /**
     * @var array
     * @access private
     */
    protected $views = [];

    /**
     * @var array
     * @access private
     */
    protected $compiledViews = [];

    /**
     * @var array
     * @access private
     */
    protected $paths = [];

    /**
     * @var array
     * @access private
     */
    protected $extensions = ['vis.php','blade.php','php','css','html'];

    /**
     * @var \PSharp\View\Compilers\CompilerInterface
     * @access private
     */
    protected $compiler;

    /**
     * Initializes the view repository.
     *
     * @param    \PSharp\View\Compilers\CompilerInterface $compiler
     * @return void
     */
    public function __construct(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Finds a view source by its name.
     *
     * @param string $path
     * @return string
     */
    public function find($name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }
        //
        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Finds a compiled view by its name.
     *
     * @param string $path
     * @return string
     */
    public function findCompiled($name)
    {
        if (isset($this->compiledViews[$name])) {
            return $this->compiledViews[$name];
        }
        //
        return $this->setCompiledAsFound(
            $name, $this->compiler->getCompiledPath($this->find($name))
        );
    }

    /**
     * Finds a view source name by its path, if any.
     *
     * @param string $path
     * @return string
     */
    public function nameByPath($path)
    {
        if (($name = array_search($path, $this->views)) !== false) {
            return $name;
        }
        //
        return $this->nameFromPath($path);
    }

    /**
     * Extracts the view name from its path.
     *
     * @param string $path
     * @return string
     */
    protected function nameFromPath($path)
    {
        $path = preg_replace('#[\\\\/]+#', '/', $path);

        $segments = explode('/', $path);

        $name = array_pop($segments);

        foreach ($this->extensions as $ext) {
            if (Str::endsWith($name, $ext)) {
                $name = Str::trimSuffixOnce($name, $ext);
                //
                break;
            }
        }

        return str_replace('/', '.', $name);
    }

    /**
     * Compiles a view by its name (if needed) and returns the path
     * for the compiled version.
     *
     * It compiles when the compiled version does not exist or
     * if it is older than the source. By setting $force = true, compilation
     * occurs no matter what.
     *
     * @param string $name
     * @param bool $force = false
     * @return string
     */
    public function compile($name, bool $force = false)
    {
        $view = $this->find($name);
        //
        if ($force) {
            return $this->compiledViews[$name] = $this->compileSource($view);
        }
        //
        if ($this->compiler->isExpired($view)) {
            return $this->setCompiledAsFound($name, $this->compileSource($view));
        }
        //
        return $this->findCompiled($name);
    }

    /**
     * Sets the compiled version of view $name as found within $path.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    protected function setCompiledAsFound($name, $path)
    {
        return $this->compiledViews[$name] = $path;
    }

    /**
     * Compiles the source and returns the compiled view path.
     *
     * @param string $view
     * @return string
     */
    protected function compileSource($view)
    {
        $source = file_get_contents($view);

        $compiledSource = $this->compiler->compile($source) . PHP_EOL . $this->makePathStringInfo($view);
        $compiledPath = $this->compiler->getCompiledPath($view);

        if ($dir = dirname($compiledPath)) if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($compiledPath, $compiledSource);

        return $compiledPath;        
    }

    /**
     * Returns a descriptive string for internal use only.
     *
     * @param string $sourceFileName
     * @return string
     */
    protected function makePathStringInfo($sourceFileName)
    {
        return sprintf('<?php /**PATH %s ENDPATH**/ ?>', $sourceFileName);
    }

    /**
     * Try to find a view by its name among paths.
     *
     * @param string $name
     * @param array $paths
     * @return string|null
     * @throws    \InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleFiles($name) as $file) {
                if (file_exists($view = $path.'/'.$file)) {
                    return $view;
                }
            }
        }
        //
        throw new InvalidArgumentException(sprintf('View [%s] not found.', $name));
    }

    /**
     * Returns a list of $name x $extensions combinations.
     *
     * @param string $name
     * @return array
     */
    protected function getPossibleFiles($name)
    {
        return array_map(function ($extension) use ($name) {
            return $this->pathFromName($name, $extension);
        }, $this->extensions);
    }

    /**
     * Translates the view name to its path.
     *
     * @param string $name
     * @return string
     */
    protected function pathFromName($name, $extension = null)
    {
        if (Str::startsWith($name, 'jeht.')) {
            return str_replace('.', '/', Str::trimPrefix($name, 'jeht.'))
                . ($extension ? '.'.$extension : '');
        }
        //
        return str_replace('.', '/', $name) . ($extension ? '.'.$extension : '');
    }

    /**
     * Adds a source location.
     *
     * @param string $location
     * @return $this
     */
    public function addLocation($location)
    {
        $this->paths[] = $this->resolvePath($location);
        //
        return $this;
    }

    /**
     * Prepends a source location.
     *
     * @param string $location
     * @return $this
     */
    public function prependLocation($location)
    {
        array_unshift($this->paths, $this->resolvePath($location));
        //
        return $this;
    }

    /**
     * Try to resolve realpath for the given $path.
     *
     * @param string $path
     * @return string
     */
    protected function resolvePath($path)
    {
        return realpath($path) ?: $path;
    }

    /**
     * Registers an extension.
     *
     * @param string $path
     * @return $this
     */
    public function addExtension($extension)
    {
        $index = array_search($extension, $this->extensions);
        //
        if (false !== $index) {
            unset($this->extensions[$index]);
        }
        //
        array_unshift($this->extensions, $extension);
        //
        return $this;
    }

    /**
     * Sets locations at once.
     *
     * @param array $paths
     * @return $this
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
        //
        return $this;
    }

    /**
     * Returns all locations.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Returns all registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns all found views.
     *
     * @return array
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Retrieves the compiler instance.
     *
     * @return    \PSharp\View\Compilers\CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
}