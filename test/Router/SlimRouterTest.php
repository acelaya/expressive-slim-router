<?php
namespace AcelayaTest\Expressive\Router;

use Acelaya\Expressive\Router\SlimRouter;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Expressive\Router\Route;

class SlimRouterTest extends TestCase
{
    /**
     * @var SlimRouter
     */
    protected $router;
    /**
     * @var Router
     */
    protected $slimRouter;
    /**
     * @var ObjectProphecy
     */
    protected $middleware;

    public function setUp()
    {
        new SlimRouter();
        $this->slimRouter = new Router();
        $this->router = new SlimRouter($this->slimRouter);
        $this->middleware = $this->prophesize(MiddlewareInterface::class);
    }

    public function testAddRoute()
    {
        $middleware = $this->middleware->reveal();
        $this->router->addRoute(new Route('/foo(/:bar)', $middleware, ['GET', 'POST'], 'home'));
        $this->injectRoutes();

        $this->assertCount(1, $this->slimRouter->getNamedRoutes());

        /** @var \Slim\Route $route */
        $route = $this->slimRouter->getMatchedRoutes('GET', '/foo/baz')[0];
        $this->assertEquals('/foo(/:bar)', $route->getPattern());
        $this->assertEquals('home', $route->getName());
        $this->assertEquals($middleware, $route->getParams()['middleware']);
    }

    public function testAddRouteWithOptions()
    {
        $middleware = $this->middleware->reveal();
        $route = new Route('/foo/:bar', $middleware, ['GET', 'POST'], 'home');
        $route->setOptions([
            'conditions' => [
                'bar' => 'es|en'
            ],
            'defaults' => [
                'bar' => 'en'
            ]
        ]);
        $this->router->addRoute($route);
        $this->injectRoutes();

        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('GET', '/foo/es'));
        $this->assertCount(0, $this->slimRouter->getMatchedRoutes('GET', '/foo/baz', true));
    }

    public function testAddRouteWithAnyMethod()
    {
        $middleware = $this->middleware->reveal();
        $route = new Route('/foo/bar', $middleware, Route::HTTP_METHOD_ANY, 'home');
        $this->router->addRoute($route);
        $this->injectRoutes();

        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('GET', '/foo/bar'));
        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('POST', '/foo/bar'));
        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('PUT', '/foo/bar'));
        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('DELETE', '/foo/bar'));
        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('PATCH', '/foo/bar'));
        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('OPTIONS', '/foo/bar'));
        $this->assertCount(1, $this->slimRouter->getMatchedRoutes('HEAD', '/foo/bar'));
    }

    public function testDummyCallable()
    {
        $this->assertNull($this->router->dummyCallable());
    }

    public function testGenerateUrl()
    {
        $middleware = $this->middleware->reveal();
        $route = new Route('/foo(/:bar)', $middleware, ['GET', 'POST'], 'home');
        $this->router->addRoute($route);

        $this->assertEquals('/foo', $this->router->generateUri('home'));
        $this->assertEquals('/foo/baz', $this->router->generateUri('home', ['bar' => 'baz']));
    }

    /**
     * @expectedException \Zend\Expressive\Router\Exception\RuntimeException
     */
    public function testGenerateUrlWithInvalidName()
    {
        $middleware = $this->middleware->reveal();
        $route = new Route('/foo(/:bar)', $middleware, ['GET', 'POST'], 'home');
        $this->router->addRoute($route);
        $this->router->generateUri('invalidName');
    }

    public function testMatchInvalidRequest()
    {
        $result = $this->router->match(ServerRequestFactory::fromGlobals());
        $this->assertTrue($result->isFailure());
    }

    public function testMatchValidRequest()
    {
        $middleware = $this->middleware->reveal();
        $this->router->addRoute(new Route('/foo(/:bar)', $middleware, ['GET', 'POST'], 'home'));
        $this->injectRoutes();

        $this->assertCount(1, $this->slimRouter->getNamedRoutes());
        $result = $this->router->match(new ServerRequest([], [], '/foo/bar', 'POST'));
        $this->assertTrue($result->isSuccess());
    }

    private function injectRoutes()
    {
        $ref = new \ReflectionObject($this->router);
        $method = $ref->getMethod('injectRoutes');
        $method->setAccessible(true);
        $method->invoke($this->router);
    }
}
