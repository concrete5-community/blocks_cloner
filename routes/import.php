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
$router->post('dialogs/import/upload-file', 'Dialog\Import::uploadFile');
$router->post('dialogs/import/block', 'Dialog\Import::importBlock');
$router->post('dialogs/import/area', 'Dialog\Import::importArea');
$router->post('dialogs/import/get-designs', 'Dialog\Import::getDesigns');
$router->get('dialogs/import/check-upload-folder', 'Dialog\Import::checkUploadFolder');
