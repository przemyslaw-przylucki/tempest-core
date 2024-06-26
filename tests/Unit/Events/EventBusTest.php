<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Events;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Container\GenericContainer;
use Tempest\Events\EventBusConfig;
use Tempest\Events\EventHandler;
use Tempest\Events\GenericEventBus;
use Tests\Tempest\Unit\Events\Fixtures\MyEventBusMiddleware;

/**
 * @internal
 * @small
 */
class EventBusTest extends TestCase
{
    public function test_it_works(): void
    {
        $container = new GenericContainer();

        $handler = new EventHandler();
        $handler->setHandler(new ReflectionMethod(MyEventHandler::class, 'handleItHappened'));

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    $handler,
                ],
            ],
            middleware: [
                new MyEventBusMiddleware(),
            ]
        );

        $eventBus = new GenericEventBus($container, $config);

        MyEventHandler::$itHappened = false;
        MyEventBusMiddleware::$hit = false;

        $eventBus->dispatch(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
        $this->assertTrue(MyEventBusMiddleware::$hit);
    }
}
