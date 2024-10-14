<?php

namespace HesamMousavi\FalconContainer;

class FalconServiceProvider
{
    protected FalconContainer $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function register() {}

    public function boot()
    {
        // This method can be used to perform actions after the services are registered
    }
}
