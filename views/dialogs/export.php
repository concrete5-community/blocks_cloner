<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\BlocksCloner\Controller\Dialog\Export $controller
 * @var Concrete\Core\View\View $view
 * @var int $cID
 * @var string $xml
 * @var Concrete\Core\Page\Page[]|array $pages
 * @var Concrete\Core\Entity\File\Version[]|array $fileVersions
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
                                if ($page === null) {
                                    ?>
                                    <button class="btn btn-sm btn-xs btn-primary" disabled><?= t('Visit') ?></button>
                                    <?php
                                } else {
                                    ?>
                                    <a class="btn btn-sm btn-xs btn-primary" target="_blank" href="<?= h($page->getCollectionLink()) ?>"><?= t('Visit') ?></a>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <code><?= h($path) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($page === null) {
                                    ?>
                                    <i><?= tc('Page', 'Not found') ?></i>
                                    <?php
                                } else {
                                    ?>
                                    <?= h($page->getCollectionName()) ?>
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
                        if ($fileVersion !== null) {
                            $fileIDs[] = $fileVersion->getFileID();
                        }
                        ?>
                        <tr>
                            <td>
                                <?php
                                if ($fileVersion === null) {
                                    ?>
                                    <button class="btn btn-sm btn-xs btn-primary" disabled><?= t('Download') ?></button>
                                    <?php
                                } else {
                                    ?>
                                    <a class="btn btn-sm btn-xs btn-primary" href="<?= h($resolverManager->resolve(['/ccm/blocks_cloner/dialogs/export/files']) . "?cID={$cID}&fIDs={$fileVersion->getFileID()}") ?>">
                                        <?= t('Download') ?>
                                    </a>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <code><?= h($key) ?></code>
                            </td>
                            <td>
                                <?php
                                if ($fileVersion === null) {
                                    ?>
                                    <i><?= tc('File', 'Not found') ?></i>
                                    <?php
                                } else {
                                    ?>
                                    <?= h($fileVersion->getFileName()) ?>
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
            <div class="text-end text-right">
                <button type="button" class="btn btn-sm btn-primary" id="blocks_cloner-export-copy"><?= t('Copy') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {

const btn = document.querySelector('#blocks_cloner-export-copy');
const ta = document.querySelector('#blocks_cloner-export-xml');
const btnText = btn.textContent;

setTimeout(() => ta.focus(), 50);

function copied()
{
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
    btn.textContent = <?= json_encode(t('Copied!')) ?>;
    setTimeout(() => {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
        btn.textContent = btnText;
    }, 500);
}
function failed(e)
{
    window.alert(e.message || e || <?= json_encode(t('Unknown error')) ?>);
}

btn.addEventListener('click', (e) => {
    e.preventDefault();
    try {
        if (window.navigator && window.navigator.clipboard && window.navigator.clipboard.writeText) {
            navigator.clipboard.writeText(ta.value)
                .then(() => copied())
                .catch((e) => failed(e))
            ;
        } else {
            ta.select();
            document.execCommand('copy');
            copied();
        }
    } catch (e) {
        failed(e);
    }
});

})();
</script>
