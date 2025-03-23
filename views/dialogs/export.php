<?php

use Concrete\Core\Entity\File\Version;
use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Export $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var string $xml
 * @var Concrete\Core\Page\Page[]|string[] $pages
 * @var Concrete\Core\Entity\File\Version[]|string[] $fileVersions
 * @var Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $resolverManager
 */

?>
<div style="display: flex; flex-direction: column; height: 100%;">
    <?php
    if ($pages !== []) {
        ?>
        <div style="max-height: 200px; overflow-y: scroll">
            <table class="table table-striped table-sm table-condensed caption-top" style="width: auto">
                <caption><strong><?= t('Referenced Pages')?></strong></caption>
                <tbody>
                    <?php
                    foreach ($pages as $path => $page) {
                        ?>
                        <tr>
                            <td>
                                <?php
                                if ($page instanceof Page) {
                                    ?>
                                    <a class="btn btn-sm btn-xs btn-primary" target="_blank" href="<?= h($page->getCollectionLink()) ?>"><?= t('Visit') ?></a>
                                    <?php
                                } else {
                                    ?>
                                    <button class="btn btn-sm btn-xs btn-primary" disabled><?= t('Visit') ?></button>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <code><?= h($path) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($page instanceof Page) {
                                    ?>
                                    <?= h($page->getCollectionName()) ?>
                                    <?php
                                } else {
                                    ?>
                                    <i><?= h($page) ?></i>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    if ($fileVersions !== []) {
        ?>
        <div style="max-height: 200px; overflow-y: scroll">
            <table class="table table-striped table-sm table-condensed caption-top" style="width: auto">
                <caption><strong><?= t('Used Files and Images')?></strong></caption>
                <tbody>
                    <?php
                    $fileIDs = [];
                    foreach ($fileVersions as $key => $fileVersion) {
                        if ($fileVersion instanceof Version) {
                            $fileIDs[] = $fileVersion->getFileID();
                        }
                        ?>
                        <tr>
                            <td>
                                <?php
                                if ($fileVersion instanceof Version) {
                                    ?>
                                    <a class="btn btn-sm btn-xs btn-primary" href="<?= h($resolverManager->resolve(['/ccm/blocks_cloner/dialogs/export/files']) . "?cID={$cID}&fIDs={$fileVersion->getFileID()}") ?>">
                                        <?= t('Download') ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <button class="btn btn-sm btn-xs btn-primary" disabled><?= t('Download') ?></button>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <code><?= h($key) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($fileVersion instanceof Version) {
                                    ?>
                                    <?= h($fileVersion->getFileName()) ?>
                                    <?php
                                } else {
                                    ?>
                                    <i><?= h($fileVersion) ?></i>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
                <?php
                if (count($fileIDs) > 1) {
                    ?>
                    <tfoot>
                        <tr>
                            <td>
                                <a class="btn btn-sm btn-xs btn-primary" href="<?= h($resolverManager->resolve(['/ccm/blocks_cloner/dialogs/export/files']) . "?cID={$cID}&fIDs=" . implode(',', $fileIDs)) ?>">
                                    <?= t('Download All') ?>
                                </a>
                            </td>
                        </tr>
                    </tfoot>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
    }
    ?>
    <div style="flex-grow: 1">
        <div style="display: flex; flex-direction: column; height: 100%;">
            <div>
                <strong style="color: #777777"><?= t('XML Data')?></strong>
            </div>
            <textarea  id="blocks_cloner-export-xml" readonly nowrap class="form-control" style="flex-grow: 1; font-family: Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.9em; resize: none"><?= htmlspecialchars($xml, ENT_QUOTES, APP_CHARSET) ?></textarea>
            <div class="text-end text-right" style="padding-top: 1rem">
                <button type="button" class="btn btn-sm btn-primary" id="blocks_cloner-export-copy"><?= t('Copy') ?></button>
                <button type="button" class="btn btn-sm btn-primary" id="blocks_cloner-export-copy-close"><?= h(t('Copy & Close')) ?></button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {

const btnCopy = document.querySelector('#blocks_cloner-export-copy');
const btnCopyText = btnCopy.textContent;
const xmlTextarea = document.querySelector('#blocks_cloner-export-xml');

setTimeout(() => xmlTextarea.focus(), 50);

function reportSuccess()
{
    btnCopy.classList.remove('btn-primary');
    btnCopy.classList.add('btn-success');
    btnCopy.textContent = <?= json_encode(t('Copied!')) ?>;
    setTimeout(() => {
        btnCopy.classList.remove('btn-success');
        btnCopy.classList.add('btn-primary');
        btnCopy.textContent = btnCopyText;
    }, 500);
}

function failed(e)
{
    window.ConcreteAlert.error({
        message: e.message || e || <?= json_encode(t('Unknown error')) ?>,
    });
}

function copy(done)
{
    try {
        if (window.navigator && window.navigator.clipboard && window.navigator.clipboard.writeText) {
            navigator.clipboard.writeText(xmlTextarea.value)
                .then(() => done())
                .catch((e) => failed(e))
            ;
        } else {
            xmlTextarea.select();
            document.execCommand('copy');
            done();
        }
    } catch (e) {
        failed(e);
    }
}

btnCopy.addEventListener('click', (e) => {
    e.preventDefault();
    copy(reportSuccess);
});

document.querySelector('#blocks_cloner-export-copy-close').addEventListener('click', (e) => {
    e.preventDefault();
    copy(() => {
        window.ConcreteAlert.info({
            message: <?= json_encode(t('Copied')) ?>,
            delay: 500,
        });
        $.fn.dialog.closeTop();
    });
});

})();
</script>
