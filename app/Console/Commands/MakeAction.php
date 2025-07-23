<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;


class MakeAction extends GeneratorCommand
{
    protected $name = 'make:action';
    protected $description = 'Create a new Action class';
    protected $type = 'Action';

    protected function getStub()
    {
        return base_path('stubs/action.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Actions';
    }
    protected function getArguments()
{
    return [
        ['name', InputArgument::REQUIRED, 'The name of the class'],
    ];
}

}
