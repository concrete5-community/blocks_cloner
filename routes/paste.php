<?php

defined('C5_EXECUTE') or die('Access Denied.');

/*
 * Base path: /ccm/blocks_cloner
 * Base namespace: Concrete\Package\BlocksCloner\Controller
 */

/**
 * @var \Concrete\Core\Routing\Router $router
 */

$router->all('panels/paste', 'Panel\Paste::view');
$router->get('dialogs/paste/import', 'Dialog\Paste\Import::view');
