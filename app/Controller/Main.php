<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Factory\Command;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Router;

/**
 * Handles requests to /.
 */
class Main implements ControllerInterface {
    /**
     * Route list.
     *
     * @var array
     */
    private $routes;
    /**
     * Router instance.
     *
     * @var \Slim\Router
     */
    private $router;
    /**
     * Command Bus instance.
     *
     * @var \League\Tactician\CommandBus
     */
    private $commandBus;
    /**
     * Command Factory Instance.
     *
     * @var \App\Factory\Command
     */
    private $commandFactory;

    /**
     * Class constructor.
     *
     * @param array                        $routes
     * @param \Slim\Router                 $router
     * @param \League\Tactician\CommandBus $commandBus
     * @param \App\Factory\Command         $commandFactory
     *
     * @return void
     */
    public function __construct(
        array $routes,
        Router $router,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->routes         = $routes;
        $this->router         = $router;
        $this->commandBus     = $commandBus;
        $this->commandFactory = $commandFactory;
    }

    /**
     * Lists all public endpoints.
     *
     * @apiEndpointResponse 200 schema/listAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        sort($this->routes);
        $classList = array_map(
            function ($filename) {
                $filename = substr($filename, 0, -4);
                $pos = strpos($filename, '/Route/');
                if ($pos === false) {
                    return $filename;
                }

                return substr($filename, $pos + 7);
            },
            array_filter(
                $this->routes,
                function ($filename) {
                    return strpos($filename, 'Interface') === false;
                }
            )
        );

        $routeList    = [];
        $publicRoutes = [];

        foreach ($this->router->getRoutes() as $route) {
            $routeName             = $route->getName();
            $routeList[$routeName] = [
                'name'    => $routeName,
                'uri'     => $route->getPattern(),
                'methods' => $route->getMethods()
            ];
        }

        foreach ($classList as $className) {
            $routeClass = sprintf(
                '\\App\\Route\\%s',
                str_replace('/', '\\', $className)
            );
            foreach ($routeClass::getPublicNames() as $routeName) {
                $publicRoutes[] = $routeList[$routeName];
            }
        }

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', ['data' => $publicRoutes]);

        return $this->commandBus->handle($command);
    }
}
