<?php

namespace Thomasderooij\LaravelModules\Console\Commands\Extensions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Thomasderooij\LaravelModules\Contracts\Services\ModuleManager;
use Thomasderooij\LaravelModules\Exceptions\InitExceptions\ConfigFileNotFoundException;

trait GenerateOverrideTrait
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    public function __construct (Filesystem $files, ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;

        // If there is a module in the workbench, change the command description to include the module name
        if (($module = $moduleManager->getWorkBench()) !== null) {
            $this->attachDescriptionSuffix($module);
        }

        parent::__construct($files);
    }

    /**
     * @param $name
     * @return string
     */
    protected function getPath($name) : string
    {
        // If there is no module, return default values
        $module = $this->option("module");
        if ($module === null) {
            $module = $this->moduleManager->getWorkBench();
        }

        if ($module === null || strtolower($module) === strtolower(config("modules.vanilla"))) {
            return parent::getPath($name);
        }

        // Parse the namespace to a directory location
        $name = lcfirst($name);
        return base_path().'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Give the base Laravel description a suffix of said module
     *
     * @param string $module
     */
    protected function attachDescriptionSuffix (string $module) : void
    {
        $this->description = $this->description . " for " . ucfirst($module);
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     * @throws ConfigFileNotFoundException
     */
    protected function rootNamespace()
    {
        // If there is no module option provided, grab the workbench module
        $module = $this->option("module");
        if ($module === null) {
            $module = $this->moduleManager->getWorkBench();
        }

        // If we have modules, and a module can be found, return the module namespace
        if ($this->moduleManager->isInitialised() && $module !== null) {
            return $this->moduleManager->getModuleNameSpace($module);
        }

        // If there is no module, return default namespace
        return parent::rootNameSpace();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $options[] = ["module", null, InputOption::VALUE_OPTIONAL, "Apply to this module."];

        return $options;
    }
}
