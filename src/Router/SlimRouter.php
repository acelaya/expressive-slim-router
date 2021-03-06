<?php
namespace Acelaya\Expressive\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Router;
use Zend\Expressive\Router\Exception;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

class SlimRouter implements RouterInterface
{
    /**
     * @var Router
     */
    private $router;
    /**
     * @var Route[]
     */
    private $routes;

    public function __construct(Router $router = null)
    {
        if (null === $router) {
            $router = $this->createRouter();
        }

        $this->router = $router;
        $this->routes = [];
    }

    /**
     * Create a default Aura router instance
     *
     * @return Router
     */
    private function createRouter()
    {
        return new Router();
    }

    /**
     * @param Route $route
     */
    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function dummyCallable()
    {
    }

    /**
     * @param  Request $request
     * @return RouteResult
     */
    public function match(Request $request): RouteResult
    {
        $this->injectRoutes();

        $matchedRoutes = $this->router->getMatchedRoutes($request->getMethod(), $request->getUri()->getPath());
        if (count($matchedRoutes) === 0) {
            return RouteResult::fromRouteFailure(null);
        }

        /** @var \Slim\Route $matchedRoute */
        $matchedRoute = array_shift($matchedRoutes);
        $params = $matchedRoute->getParams();

        // Get the middleware from the route params and remove it
        $middleware = $params['middleware'];
        unset($params['middleware']);

        $route = new Route(
            $matchedRoute->getPattern(),
            $middleware,
            $matchedRoute->getHttpMethods(),
            $matchedRoute->getName()
        );
        return RouteResult::fromRoute($route, $params);
    }

    /**
     * Generate a URI from the named route.
     *
     * Takes the named route and any substitutions, and attempts to generate a
     * URI from it. Additional router-dependent options may be passed.
     *
     * The URI generated MUST NOT be escaped. If you wish to escape any part of
     * the URI, this should be performed afterwards; consider passing the URI
     * to league/uri to encode it.
     *
     * @see https://github.com/auraphp/Aura.Router#generating-a-route-path
     * @see http://framework.zend.com/manual/current/en/modules/zend.mvc.routing.html
     * @param string $name
     * @param array $substitutions
     * @param array $options
     * @return string
     * @throws Exception\RuntimeException if unable to generate the given URI.
     */
    public function generateUri(string $name, array $substitutions = [], array $options = []): string
    {
        $this->injectRoutes();

        if (! $this->router->hasNamedRoute($name)) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot generate URI based on route "%s"; route not found',
                $name
            ));
        }

        return $this->router->urlFor($name, $substitutions);
    }

    private function injectRoutes(): void
    {
        foreach ($this->routes as $key => $route) {
            $this->injectRoute($route);
            unset($this->routes[$key]);
        }
    }

    private function injectRoute(Route $route): void
    {
        $slimRoute = new \Slim\Route($route->getPath(), [$this, 'dummyCallable']);
        $slimRoute->setName($route->getName());

        $allowedMethods = $route->getAllowedMethods();
        $slimRoute->via($allowedMethods === Route::HTTP_METHOD_ANY ? 'ANY' : $allowedMethods);

        // Process options
        $options = $route->getOptions();
        if (isset($options['conditions']) && is_array($options['conditions'])) {
            $slimRoute->setConditions($options['conditions']);
        }
        // The middleware is merged with the rest of the route params
        $params = [
            'middleware' => $route->getMiddleware()
        ];
        if (isset($options['defaults']) && is_array($options['defaults'])) {
            $params = array_merge($options['defaults'], $params);
        }
        $slimRoute->setParams($params);

        $this->router->map($slimRoute);
    }
}
