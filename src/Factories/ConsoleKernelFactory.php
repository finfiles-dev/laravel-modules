<?php

namespace Thomasderooij\LaravelModules\Factories;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Thomasderooij\LaravelModules\Console\Kernel;
use Thomasderooij\LaravelModules\Contracts\Factories\ConsoleKernelFactory as Contract;
use Thomasderooij\LaravelModules\Contracts\Services\ModuleManager;
use Thomasderooij\LaravelModules\Exceptions\InitExceptions\ConfigFileNotFoundException;
use Thomasderooij\LaravelModules\Services\RouteSource;

class ConsoleKernelFactory extends FileFactory implements Contract
{
    /**
     * The route source information service
     *
     * @var RouteSource
     */
    protected $routeSource;

    public function __construct(Filesystem $filesystem, ModuleManager $moduleManager, RouteSource $routeSource)
    {
        $this->moduleManager = $moduleManager;
        $this->routeSource = $routeSource;

        parent::__construct($filesystem, $moduleManager);
    }

    /**
     * Create a console kernel
     *
     * @param string $module
     * @throws FileNotFoundException
     * @throws ConfigFileNotFoundException
     */
    public function create (string $module) : void
    {
        $this->populateFile(base_path($this->getRelativeConsoleDir($module)), $this->getKernelFileName(), $this->getStub(), [
            $this->getKernelNamespacePlaceholder() => $this->getKernelNamespace($module),
            $this->getModuleKernelPlaceholder() => $this->getModuleKernel(),
            $this->getKernelConsoleDirPlaceholder() => $this->getKernelConsoleDir($module),
            $this->getRouteFilePlaceholder() => $this->getRelativeRouteFilePath($module),
        ]);
    }

    /**
     * Get the relative path to the console kernel
     *
     * @param string $module
     * @return string
     */
    protected function getRelativeConsoleDir (string $module) : string
    {
        return config("modules.root") . "/$module/{$this->getConsoleDirectory()}";
    }

    /**
     * Get the relative path to the console route file
     *
     * @param string $module
     * @return string
     */
    protected function getRelativeRouteFilePath (string $module) : string
    {
        $directory = $this->getRelativeRouteRootDir($module);
        return $this->ensureSlash($directory) . $this->routeSource->getConsoleRoute() . $this->routeSource->getRouteFileExtension();
    }

    /**
     * Get the relative directory for the console route
     *
     * @param string $module
     * @return string
     */
    protected function getRelativeRouteRootDir (string $module) : string
    {
        return config('modules.root') . "/" . ucfirst($module) . "/" . $this->routeSource->getRouteRootDir();
    }

    /**
     * Get the kernel namespace
     *
     * @param string $module
     * @return string
     * @throws ConfigFileNotFoundException
     */
    protected function getKernelNamespace (string $module) : string
    {
        return $this->moduleManager->getModuleNameSpace($module) . $this->getConsoleDirectory();
    }

    /**
     * Get the module kernel class
     *
     * @return string
     */
    protected function getModuleKernel () : string
    {
        return Kernel::class;
    }

    /**
     * @param string $module
     * @return string
     */
    protected function getKernelConsoleDir (string $module) : string
    {
        return config('modules.root') . "/" . ucfirst($module) . "/Console/Commands";
    }

    /**
     * Get the file stub
     *
     * @return string
     */
    protected function getStub () : string
    {
        return __DIR__ . '/stubs/consoleKernel.stub';
    }

    /**
     * Get the kernel namespace placeholder
     *
     * @return string
     */
    protected function getKernelNamespacePlaceholder () : string
    {
        return "{kernelNamespace}";
    }

    /**
     * Get the module kernel classname placeholder
     *
     * @return string
     */
    protected function getModuleKernelPlaceholder ( ): string
    {
        return "{moduleKernel}";
    }

    /**
     * Get tje kernel directory placeholder
     *
     * @return string
     */
    protected function getKernelConsoleDirPlaceholder () : string
    {
        return "{kernelConsolePath}";
    }

    /**
     * Get the route file placeholder
     *
     * @return string
     */
    protected function getRouteFilePlaceholder () : string
    {
        return "{kernelConsoleRouteFile}";
    }

    /**
     * Get the kernel directory within the module
     *
     * @return string
     */
    protected function getConsoleDirectory () : string
    {
        return "Console";
    }

    /**
     * Get the kernel file name
     *
     * @return string
     */
    protected function getKernelFileName () : string
    {
        return "Kernel.php";
    }
}
