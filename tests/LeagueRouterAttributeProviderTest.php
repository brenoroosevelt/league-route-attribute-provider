<?php
declare(strict_types=1);

namespace BrenoRoosevelt\RouteAttributeProvider\League\Tests;

use BrenoRoosevelt\RouteAttributeProvider\League\LeagueRouterAttributeProvider;
use Habemus\Container;
use Jerowork\RouteAttributeProvider\Api\Route;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\StrategyInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class LeagueRouterAttributeProviderTest extends TestCase
{
    /** @test */
    public function shouldConfigureRoutes(): void
    {
        // arrange
        $router = new Router();
        $route = new Route(
            '/home',
            ['GET'],
            'routeName',
            ['Middleware1', 'Middleware2'],
            'localhost',
            ['http', 'https'],
            80,
            443,
            ['strategy' => ApplicationStrategy::class]
        );

        $provider = new LeagueRouterAttributeProvider($router, new Container());

        // act
        $provider->configure(stdClass::class, '__invoke', $route);

        // assert
        $leagueRoute = $router->getNamedRoute('routeName');
        $this->assertEquals('routeName', $leagueRoute->getName());
        $this->assertEquals('GET', $leagueRoute->getMethod());
        $this->assertEquals('/home', $leagueRoute->getPath());
        $this->assertEquals('localhost', $leagueRoute->getHost());
        $this->assertEquals('https', $leagueRoute->getScheme());
        $this->assertEquals(443, $leagueRoute->getPort());
        $this->assertInstanceOf(StrategyInterface::class, $leagueRoute->getStrategy());
        foreach ($leagueRoute->getMiddlewareStack() as $middleware) {
            $this->assertTrue(in_array($middleware, ['Middleware1', 'Middleware2']));
        }
    }

    /** @test */
    public function shouldApplyRoutes(): void
    {
        // arrange
        $router = new Router();
        $cache = new Psr16ArrayCache();

        // act
        LeagueRouterAttributeProvider::apply($router, [__DIR__], $cache);

        // assert
        $leagueRoute = $router->getNamedRoute('routeName');
        $this->assertEquals('routeName', $leagueRoute->getName());
        $this->assertEquals('GET', $leagueRoute->getMethod());
        $this->assertEquals('/home', $leagueRoute->getPath());
        foreach ($leagueRoute->getMiddlewareStack() as $middleware) {
            $this->assertTrue(in_array($middleware, ['Middleware1', 'Middleware2']));
        }
        $this->assertFalse($cache->isEmpty());
    }
}
