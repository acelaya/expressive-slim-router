<?php
declare(strict_types=1);

namespace AcelayaTest\Expressive;

use Acelaya\Expressive\ConfigProvider;
use Acelaya\Expressive\Factory\SlimRouterFactory;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Router\RouterInterface;

class ConfigProviderTest extends TestCase
{
    private $configProvider;

    public function setUp()
    {
        $this->configProvider = new ConfigProvider();
    }

    /**
     * @test
     */
    public function providedConfigIsCorrect()
    {
        $provider = $this->configProvider;

        $this->assertEquals([
            'dependencies' => [
                'factories' => [
                    RouterInterface::class => SlimRouterFactory::class,
                ],
            ],
        ], $provider());
    }
}
