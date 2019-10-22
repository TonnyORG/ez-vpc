<?php

use EzVpc\Tools\ClassLoader;
use EzVpc\Tools\Includer;

/**
 * Custom configuration variables
 */
$commandsClasses = [];
$commandsPath = __DIR__.'/src/Commands';
$commandsNamespace = 'EzVpc\Commands';

/**
 * Load .env variables
 */
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

/**
 * Load available command classes
 */
Includer::recursivelyInclude($commandsPath);
$classLoader = new ClassLoader($commandsNamespace);
$commandsClasses = $classLoader->filterByNamespace(get_declared_classes());
