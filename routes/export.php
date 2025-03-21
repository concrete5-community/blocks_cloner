<?php

defined('C5_EXECUTE') or die('Access Denied.');

/*
 * Base path: /ccm/blocks_cloner
 * Base namespace: Concrete\Package\BlocksCloner\Controller
 */

/**
 * @var \Concrete\Core\Routing\Router $router
 */

$router->all('panels/export', 'Panel\Export::view');
$router->get('dialogs/export', 'Dialog\Export::view');
$router->get('dialogs/export/files', 'Dialog\Export::downloadFiles');
