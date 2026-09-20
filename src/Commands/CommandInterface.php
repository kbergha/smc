<?php declare(strict_types=1);

namespace Smc\Commands;

interface CommandInterface
{
    public function execute() : bool;
}
