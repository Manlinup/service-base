<?php

namespace Sak\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Laravoole\Commands\LaravooleCommand;
use Sak\Core\Exceptions\FileExistsException;
use Sak\Core\Generators\RouteGenerator;
use Sak\Core\Traits\Generate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SakLaravooleCommand
 * @package Sak\Core\Commands
 */
class SakLaravooleCommand extends LaravooleCommand
{

    public function handle()
    {
        parent::fire();
    }
}
