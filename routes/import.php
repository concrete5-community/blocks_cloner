<?php

defined('C5_EXECUTE') or die('Access Denied.');

/*
 * Base path: /ccm/blocks_cloner
 * Base namespace: Concrete\Package\BlocksCloner\Controller
 */

/**
 * @var \Concrete\Core\Routing\Router $router
 */

$router->all('panels/import', 'Panel\Import::view');
$router->get('dialogs/import', 'Dialog\Import::view');
$router->post('dialogs/import/analyze', 'Dialog\Import::analyze');
$router->post('dialogs/import/import', 'Dialog\Import::import');
