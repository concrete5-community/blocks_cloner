<?php

defined('C5_EXECUTE') or die('Access Denied.');

/*
 * Base path: /ccm/blocks_cloner/panels
 * Base namespace: Concrete\Package\BlocksCloner\Controller\Panel
 */

/**
 * @var \Concrete\Core\Routing\Router $router
 */

$router->get('/copy', 'Copy::view');
