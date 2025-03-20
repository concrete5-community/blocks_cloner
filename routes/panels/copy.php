<?php

defined('C5_EXECUTE') or die('Access Denied.');

/*
 * Base path: /ccm/blocks_cloner
 * Base namespace: Concrete\Package\BlocksCloner\Controller
 */

/**
 * @var \Concrete\Core\Routing\Router $router
 */

$router->all('panels/copy', 'Panel\Copy::view');
$router->get('dialogs/copy/export', 'Dialog\Copy\Export::view');
$router->get('dialogs/copy/export/files', 'Dialog\Copy\Export::downloadFiles');
