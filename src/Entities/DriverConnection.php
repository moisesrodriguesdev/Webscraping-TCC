<?php

namespace TccUnifametro\Entities;

use Facebook\WebDriver\Remote\{DesiredCapabilities, RemoteWebDriver};

abstract class DriverConnection
{
    private string $host = 'http://localhost:4444/wd/hub';

    protected RemoteWebDriver $driver;

    protected function setup(): void
    {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($this->host, $capabilities, 5000);
    }

    abstract public function run(): array;

    abstract protected function setDriver(): void;

    abstract protected function execScript(): void;
}
