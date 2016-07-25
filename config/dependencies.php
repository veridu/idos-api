<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

use App\Command;
use App\Exception\AppException;
use App\Factory;
use App\Handler;
use App\Middleware as Middleware;
use App\Middleware\Auth; // TODO: Why not use folder identifiers instead of using so many declarations?
use App\Repository;
use Illuminate\Database\Capsule\Manager;
use Interop\Container\ContainerInterface;
use Jenssegers\Optimus\Optimus;
use Lcobucci\JWT;
use League\Tactician\CommandBus;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleClassNameInflector;
use League\Tactician\Logger\Formatter\ClassNameFormatter;
use League\Tactician\Logger\Formatter\ClassPropertiesFormatter;
use League\Tactician\Logger\LoggerMiddleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;
use Slim\HttpCache\CacheProvider;
use Stash\Driver\Apc;
use Stash\Driver\Composite;
use Stash\Driver\Ephemeral;
use Stash\Driver\FileSystem;
use Stash\Driver\Memcache;
use Stash\Driver\Redis;
use Stash\Driver\Sqlite;
use Stash\Pool;
use Whoops\Handler\PrettyPageHandler;
use League\Event\Emitter;
use Apix\Cache;
use App\Event\ListenerProvider;

if (! isset($app)) {
    die('$app is not set!');
}

$container = $app->getContainer();

// Slim Error Handling
$container['errorHandler'] = function (ContainerInterface $container) {
    return function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Exception $exception
    ) use ($container) {
        $response = $container
            ->get('httpCache')
            ->denyCache($response);

        $log = $container->get('log');

        if ($exception instanceof AppException) {
            $log('API')->info(
                sprintf(
                    '%s [%s:%d]',
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                )
            );

            $body = [
                'status' => false,
                'error'  => [
                    'code' => $exception->getCode(),
                    // 'type' => $exception->getType(),
                    // 'link' => $exception->getLink(),
                    'message' => $exception->getMessage(),
                    'trace'   => $exception->getTraceAsString()
                ]
            ];

            $command = $container
                ->get('commandFactory')
                ->create('ResponseDispatch');
            $command
                ->setParameter('request', $request)
                ->setParameter('response', $response)
                ->setParameter('body', $body)
                ->setParameter('statusCode', $exception->getCode());

            return $container->get('commandBus')->handle($command);
        }

        $log('Foundation')->error(
            sprintf(
                '%s [%s:%d]',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            )
        );
        $log('Foundation')->error($exception->getTraceAsString());

        $settings = $container->get('settings');
        if ($settings['debug']) {
            $prettyPageHandler = new PrettyPageHandler();
            // Add more information to the PrettyPageHandler
            $prettyPageHandler->addDataTable(
                'Request',
                [
                    'Accept Charset'  => $request->getHeader('ACCEPT_CHARSET') ?: '<none>',
                    'Content Charset' => $request->getContentCharset() ?: '<none>',
                    'Path'            => $request->getUri()->getPath(),
                    'Query String'    => $request->getUri()->getQuery() ?: '<none>',
                    'HTTP Method'     => $request->getMethod(),
                    'Base URL'        => (string) $request->getUri(),
                    'Scheme'          => $request->getUri()->getScheme(),
                    'Port'            => $request->getUri()->getPort(),
                    'Host'            => $request->getUri()->getHost()
                ]
            );

            $whoops = new Whoops\Run();
            $whoops->pushHandler($prettyPageHandler);

            return $response
                ->withStatus(500)
                ->write($whoops->handleException($exception));
        }

        $body = [
            'status' => false,
            'error'  => [
                'code'    => 500,
                'type'    => 'APPLICATION_ERROR',
                'link'    => null,
                'message' => 'Internal Application Error'
            ]
        ];

        $command = $container->get('commandFactory')->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body)
            ->setParameter('statusCode', 500);

        return $container->get('commandBus')->handle($command);
    };
};

// Slim Not Found Handler
$container['notFoundHandler'] = function (ContainerInterface $container) {
    return function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ) use ($container) {
        throw new \Exception('not found');
    };
};

// Slim Not Allowed Handler
$container['notAllowedHandler'] = function (ContainerInterface $container) {
    return function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $methods
    ) use ($container) {
        if ($request->isOptions()) {
            return $response->withStatus(204);
        }

        throw new \Exception('notAllowedHandler');
    };
};

// Monolog Logger
$container['log'] = function (ContainerInterface $container) {
    return function ($channel = 'API') use ($container) {
        $settings = $container->get('settings');
        $logger   = new Logger($channel);
        $logger
            ->pushProcessor(new UidProcessor())
            ->pushProcessor(new WebProcessor())
            ->pushHandler(new StreamHandler($settings['log']['path'], $settings['log']['level']));

        return $logger;
    };
};


// Stash Cache
$container['cache'] = function (ContainerInterface $container) {
    $settings = $container->get('settings');

    if (empty($settings['cache']['driver'])) {
        $settings['cache']['driver'] = 'filesystem';
    }

    switch ($settings['cache']['driver']) {
        case 'filesystem':
            $options = array_merge($settings['cache']['default'], $settings['cache']['directory']);
            $pool = Cache\Factory::getTaggablePool(new Cache\Directory, $options);
            break;
        case 'redis':
            $options = array_merge($settings['cache']['default'], $settings['cache']['redis']);
            $redis = new \Redis;
            $redis->connect($settings['cache']['redis']['host'], $settings['cache']['redis']['port']);
            $pool = Cache\Factory::getTaggablePool($redis, $options);
            break;
    }

    // var_dump(get_class_methods($pool));
    // $pool->clear();

    return $pool;
};

// Slim HTTP Cache
$container['httpCache'] = function (ContainerInterface $container) {
    return new CacheProvider();
};

// Tactician Command Bus
$container['commandBus'] = function (ContainerInterface $container) {
    $settings = $container->get('settings');
    $logger   = new Logger('CommandBus');
    $logger
        ->pushProcessor(new UidProcessor())
        ->pushProcessor(new WebProcessor())
        ->pushHandler(new StreamHandler($settings['log']['path'], $settings['log']['level']));

    $commandPaths = glob(__DIR__ . '/../app/Command/*/*.php');
    $commands     = [];
    foreach ($commandPaths as $commandPath) {
        $matches = [];
        preg_match_all('/.*Command\/(.*)\/(.*).php/', $commandPath, $matches);

        $resource = $matches[1][0];
        $command  = $matches[2][0];

        $commands[sprintf('App\\Command\\%s\\%s', $resource, $command)] = sprintf('App\\Handler\\%s', $resource);
    }

    $commands[Command\ResponseDispatch::class] = Handler\Response::class;
    $handlerMiddleware                         = new CommandHandlerMiddleware(
        new ClassNameExtractor(),
        new ContainerLocator(
            $container,
            $commands
        ),
        new HandleClassNameInflector()
    );
    if ($settings['debug']) {
        $formatter = new ClassPropertiesFormatter();
    } else {
        $formatter = new ClassNameFormatter();
    }

    return new CommandBus(
        [
            new LoggerMiddleware(
                $formatter,
                $logger
            ),
            $handlerMiddleware
        ]
    );
};

// App Command Factory
$container['commandFactory'] = function (ContainerInterface $container) {
    return new Factory\Command();
};

// Validator Factory
$container['validatorFactory'] = function (ContainerInterface $container) {
    return new Factory\Validator();
};

// App Entity Factory
$container['entityFactory'] = function (ContainerInterface $container) {
    return new Factory\Entity();
};

// Auth Middleware
$container['authMiddleware'] = function (ContainerInterface $container) {
    return function ($authorizationRequirement) use ($container) {
        $repositoryFactory = $container->get('repositoryFactory');
        $jwt               = $container->get('jwt');

        return new Auth(
            $repositoryFactory->create('Credential'),
            $repositoryFactory->create('User'),
            $repositoryFactory->create('Company'),
            $jwt('parser'),
            $jwt('validation'),
            $jwt('signer'),
            $authorizationRequirement
        );
    };
};

// Permission Middleware
$container['permissionMiddleware'] = function (ContainerInterface $container) {
    return function ($permissionType) use ($container) {
        return new Middleware\Permission($container, $permissionType);
    };
};

// App Repository Factory
$container['repositoryFactory'] = function (ContainerInterface $container) {
    $settings = $container->get('settings');
    switch ($settings['repository']['strategy']) {
        case 'db':
        default:
            $strategy = new Repository\DBStrategy($container->get('entityFactory'), $container->get('db'));
    }

    if ((isset($settings['repository']['cached'])) && ($settings['repository']['cached'])) {
        $strategy = new Repository\CachedStrategy(
            new Factory\Repository($strategy),
            $container->get('cache')
        );
    }

    return new Factory\Repository($strategy);
};

// JSON Web Token
$container['jwt'] = function (ContainerInterface $container) {
    return function ($item) use ($container) {
        switch ($item) {
            case 'builder':
                return new JWT\Builder();
            case 'parser':
                return new JWT\Parser();
            case 'validation':
                return new JWT\ValidationData();
            case 'signer':
                return new JWT\Signer\Hmac\Sha256();
        }
    };
};

// DB Access
$container['db'] = function (ContainerInterface $container) {
    $capsule = new Manager();
    $capsule->addConnection($container['settings']['db']);

    return $capsule->getConnection();
};

// Respect Validator
$container['validator'] = function (ContainerInterface $container) {
    return Validator::create();
};

// Optimus
$container['optimus'] = function (ContainerInterface $container) {
    $settings = $container->get('settings');

    return new Optimus(
        $settings['optimus']['prime'],
        $settings['optimus']['inverse'],
        $settings['optimus']['random']
    );
};

// App files
$container['globFiles'] = function () {
    return [
        'routes'    => glob(__DIR__ . '/../app/Route/*.php'),
        'handlers'  => glob(__DIR__ . '/../app/Handler/*.php'),
        'listenerProviders'  => glob(__DIR__ . '/../app/Listener/*/*Provider.php'),
    ];
};

// Register Event emitter & Event listeners
$container['eventEmitter'] = function (ContainerInterface $container) {
    $emitter = new Emitter();

    $providers = array_map(function ($providerFile) {
        return preg_replace('/.*?Listener\/(.*)\/ListenerProvider.php/', 'App\\Listener\\\$1\\ListenerProvider', $providerFile);
    }, $container->get('globFiles')['listenerProviders']);

    foreach ($providers as $provider) {
        $emitter->useListenerProvider(new $provider($container));
    }
    
    return $emitter;
};