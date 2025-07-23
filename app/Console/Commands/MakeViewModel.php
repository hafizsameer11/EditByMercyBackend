<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeViewModel extends GeneratorCommand
{
 
    protected $name = 'make:viewmodel';
    protected $description = 'Create a new ViewModel class';
    protected $type = 'ViewModel';

    protected function getStub()
    {
        return base_path('stubs/viewmodel.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\ViewModels';
    }
    protected function getArguments()
{
    return [
        ['name', InputArgument::REQUIRED, 'The name of the class'],
    ];
}

}
