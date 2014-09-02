<?php

namespace FrontController;

use Psr\Log\LoggerInterface;
use Silex\ControllerResolver as BaseControllerResolver;
use InvalidArgumentException;
use LogicException;

/**
 * ControllerResolver.
 */
class ControllerResolver extends BaseControllerResolver
{
    protected function createController($controller)
    {
        $module = $controller;
        ////list($module, $method) = explode('::', $controller, 2);
        $classname = 'FrontController\\Module\\' . $module . '\\' . $module . 'Module';

        if (!class_exists($classname)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $classname));
        }

        $controller = new $classname();
        return array($controller, 'handle');
    }
}
