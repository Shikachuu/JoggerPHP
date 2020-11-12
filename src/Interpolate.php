<?php

declare(strict_types=1);

namespace Jogger;

class Interpolate
{
    private array $defaultContext;

    /**
     * Interpolate's constructor.
     *
     * Lets you set some key value pairs as the default context when interpolating string.
     * @param array $defaultContext
     */
    public function __construct(array $defaultContext = array()) {
        $this->defaultContext = $defaultContext;
    }

    /**
     * @return array Returns the current default context.
     */
    public function getDefaultContext(): array {
        return $this->defaultContext;
    }

    /**
     * @param array $defaultContext Sets the current default context for string interpolation.
     */
    public function setDefaultContext(array $defaultContext): void {
        $this->defaultContext = $defaultContext;
    }

    /**
     * Takes a string and an associative array as params.
     *
     * Changes every part of the string that is surrounded by curly braces, and uses it as an array key to search.
     * If the array contains a value for the key the function will swap it to it's assigned value, casted to a string.
     * @param string $message The string that needs the values changed.
     * @param array $context Associative, key-value pairs, the key should be the name given between the curly braces in the string
     * @return string
     */
    public function __invoke(string $message, array $context = array()): string {
        $replace = array();
        $ctx = array_merge($this->defaultContext, $context);
        foreach ($ctx as $key => $value) {
            if (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace[sprintf("{%s}", $key)] = $value;
            }
        }
        return strtr($message, $replace);
    }
}
