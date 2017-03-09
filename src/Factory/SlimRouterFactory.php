<?php
namespace Acelaya\Expressive\Factory;

use Psr\Container\ContainerInterface;
use Acelaya\Expressive\Router\SlimRouter;

class SlimRouterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new SlimRouter();
    }
}
