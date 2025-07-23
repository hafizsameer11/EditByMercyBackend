<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeDTO extends GeneratorCommand
{
     protected $name = 'make:dto';
    protected $description = 'Create a new DTO class';
    protected $type = 'DTO';

    protected function getStub()
    {
        return base_path('stubs/dto.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\DTOs';
    }
    protected function getArguments()
{
    return [
        ['name', InputArgument::REQUIRED, 'The name of the class'],
    ];
}

}
