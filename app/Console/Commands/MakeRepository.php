<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeRepository extends GeneratorCommand
{
  protected $name = 'make:repository';
    protected $description = 'Create a new Repository class';
    protected $type = 'Repository';

    protected function getStub()
    {
        return base_path('stubs/repository.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }
    protected function getArguments()
{
    return [
        ['name', InputArgument::REQUIRED, 'The name of the class'],
    ];
}

}
