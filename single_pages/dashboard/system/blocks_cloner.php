<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\SinglePage\Dashboard\System\BlocksCloner $controller
 * @var Concrete\Core\Page\View\PageView $view
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Application\Service\UserInterface $interface

 * @var Concrete\Package\BlocksCloner\GlobalOptions $globalOptions
 */
?>

<form method="POST" action="<?= h((string) $view->action('save')) ?>">
    <?php $token->output('blocks_cloner-save') ?>
    <div class="form-group">
        <div class="form-check">
            <?= $form->checkbox('exportEnabled', 1, $globalOptions->isExportEnabled()) ?>
            <label class="form-check-label" for="exportEnabled">
                <?= t('Enable export features') ?>
            </label>
        </div>
    </div>
    
    <div class="form-group">
        <div class="form-check">
            <?= $form->checkbox('importEnabled', 1, $globalOptions->isImportEnabled()) ?>
            <label class="form-check-label" for="importEnabled">
                <?= t('Enable import features') ?>
            </label>
        </div>
    </div>
    
    <div class="form-group">
        <div class="form-check">
            <?= $form->checkbox('pageStructureEnabled', 1, $globalOptions->isPageStructureEnabled()) ?>
            <label class="form-check-label" for="pageStructureEnabled">
                <?= t('Enable page structure panel') ?>
            </label>
        </div>
    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?= $interface->submit(/* text: */ t('Save'), /* formID: */ '', /* buttonAlign: */ 'right', /* innerClass: */ 'btn-primary') ?>
        </div>
    </div>
</form>