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
$router->get('dialogs/export/area', 'Dialog\Export\Area::view');
$router->get('dialogs/export/block', 'Dialog\Export\Block::view');
$router->get('dialogs/export/files', 'Dialog\Export\Files::view');
