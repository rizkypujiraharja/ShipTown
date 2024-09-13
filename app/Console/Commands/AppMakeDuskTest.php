<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class AppMakeDuskTest extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'app:make-dusk-test {name : The name of the class} {--uri=: The uri of the route}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Dusk test class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Test';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->laravel->basePath().'/stubs/dusk_test.stub';
    }

    /**
     * Get the destination class path.
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel->basePath().'/tests'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Browser';
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Tests';
    }

    protected function buildClass($name): array|string
    {
        $buildClass = parent::buildClass($name);

        if ($this->hasOption('uri')) {
            $buildClass = str_replace('{{uri}}', $this->option('uri'), $buildClass);
        }

        return $buildClass;
    }
}
