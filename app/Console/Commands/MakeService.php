<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeService extends GeneratorCommand
{
  protected $name = 'make:service';
    protected $description = 'Create a new service class';
    protected $type = 'Service';

    protected function getStub()
    {
        return base_path('stubs/service.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Services';
    }
        // ✅ This is the fix: allow 1 argument (class name)
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the service class'],
        ];
    }

}
