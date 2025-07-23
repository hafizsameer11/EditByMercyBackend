<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeTrait extends GeneratorCommand
{ 
    protected $name = 'make:trait';
    protected $description = 'Create a new trait';
    protected $type = 'Trait';

    protected function getStub()
    {
        return base_path('stubs/trait.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Traits';
    }
    protected function getArguments()
{
    return [
        ['name', InputArgument::REQUIRED, 'The name of the class'],
    ];
}

}
