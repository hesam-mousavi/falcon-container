<?php

namespace HesamMousavi\FalconContainer;

class Singleton
{
    private static mixed $instance = null;

    /**
     * The Singleton's constructor should always be not public to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct() {}

    public static function getInstance()
    {
        $class = static::class;
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new static();
        }
        return self::$instance[$class];
    }

    /**
     * Singletons should not be restorable from strings.
     *
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone() {}

}
