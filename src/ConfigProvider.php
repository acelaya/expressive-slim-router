<?php
declare(strict_types=1);

namespace Acelaya\Expressive;

use Zend\Expressive\Router\RouterInterface;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    RouterInterface::class => Factory\SlimRouterFactory::class,
                ],
            ],
        ];
    }
}
