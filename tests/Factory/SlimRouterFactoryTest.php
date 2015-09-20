<?php
namespace AcelayaTest\Expressive\Factory;

use Acelaya\Expressive\Router\SlimRouter;
use Acelaya\Expressive\Factory\SlimRouterFactory;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

class SlimRouterFactoryTest extends TestCase
{
    /**
     * @var SlimRouterFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new SlimRouterFactory();
    }

    public function testInvoke()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $instance = $this->factory->__invoke($container->reveal());
        $this->assertInstanceOf(SlimRouter::class, $instance);
    }
}
