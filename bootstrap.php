<?php

/**
 * Eventbrite Package
 *
 * Eventbrite package for building web applications linked with Eventbrite's events and tickets system.
 *
 * @package		Eventbrite
 * @author		Gilles De Mey
 * @copyright           Copyright (c) 2011 Stas Sușcov
 * @license		See LICENSE
 * @link		http://www.gillesdemey.be
 */
Autoloader::add_core_namespace('Eventbrite');

Autoloader::add_classes(array(
    'Eventbrite\\Eventbrite' => __DIR__ . '/classes/eventbrite.php',
));

