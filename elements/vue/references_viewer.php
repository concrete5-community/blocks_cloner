<?php

use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\Validation\CSRF\Token;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\View\View $view
 */

$resolverManager = app(ResolverManagerInterface::class);

$viewStackUrl = '';
$page = Page::getByPath('/dashboard/blocks/stacks');
if ($page && !$page->isError()) {
    $checker = new Checker($page);
    if ($checker->canViewPage()) {
        $viewStackUrl = rtrim((string) $resolverManager->resolve([$page, 'view_details']), '/');
    }
}

$viewSitemapUrl = '';
$page = Page::getByPath('/dashboard/sitemap/full');
if ($page && !$page->isError()) {
    $checker = new Checker($page);
    if ($checker->canViewPage()) {
        $viewSitemapUrl = (string) $resolverManager->resolve([$page]);
    }
}

$viewFileManagerUrl = '';
$page = Page::getByPath('/dashboard/files/search');
if ($page && !$page->isError()) {
    $checker = new Checker($page);
    if ($checker->canViewPage()) {
        $viewFileManagerUrl = (string) $resolverManager->resolve([$page]);
    }
}

$token = app(Token::class);

ob_start();
?>
<div style="display: flex; flex-direction: column; overflow-y: auto" v-bind:style="{'flex-grow': maxHeight ? 0 : 1, 'max-height': maxHeight || 'none'}">
    <div v-if="unrecognizedReferenceTypes.length" class="alert alert-danger">
        <?= t('Unrecognized reference types') ?>
        <ul>
            <li v-for="t in unrecognizedReferenceTypes">
                <code>{{ t }}</code>
            </li>
        </ul>
    </div>
    <div v-else-if="noReferences" class="alert alert-info">
        <?= t('No references found') ?>
    </div>
    <div v-if="references?.blockTypes">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('blockTypes') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced Block Types') ?></strong>
            </caption>
            <colgroup>
                <col width="1" />
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(blockType, key) in references.blockTypes">
                    <td class="text-nowrap">
                        <code>{{ key }}</code>
                    </td>
                    <td class="text-nowrap">
                        <span v-if="blockType.error" class="text-danger">{{ blockType.error }}</span>
                        <span v-else>{{ blockType.name }}</span>
                    </td>
                    <td>
                        <span v-if="!blockType.error">
                            <i v-if="!blockType.package"><?= t('Provided by %s', 'Concrete') ?></i>
                            <span v-else>
                                <?= t('Provided by %s', '{{ blockType.package.name }}') ?><br />
                                <span class="small text-muted"><?= t('Handle: %s', '<code>{{ blockType.package.handle }}</code>') ?></span>
                            </span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="references?.files">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('files') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced Files and Images') ?></strong>
                <span v-if="operation === 'export' && fileIDs.length">
                    -
                    <a v-bind:href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/files?cID=${cid}&fIDs=${fileIDs.join(',')}`"><?= t('Download All') ?></a>
                </span>
                <span v-if="operation === 'import' && viewFileManagerUrl">
                    -
                    <a v-bind:href="viewFileManagerUrlWithFolder" target="_blank"><?= t('Open File Manager') ?></a>
                </span>
                <span v-if="operation === 'import' && someErrors('files') && uploadToFolder?.id">
                    -
                    <a href="#" v-on:click.prevent="pickFile()"><?= t('Upload File') ?></a>
                    to
                    <a href="#" v-on:click.prevent="pickUploadFolder()" v-bind:title="uploadToFolder.path">{{ uploadToFolder.name }}</a>
                </span>
            </caption>
            <colgroup>
                <col v-if="operation === 'export'" width="1" />
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(file, key) in references.files">
                    <td v-if="operation === 'export'" class="text-nowrap">
                        <button v-if="file.error" class="btn btn-sm btn-xs btn-primary" disabled><?= t('Download') ?></button>
                        <a v-else class="btn btn-sm btn-xs btn-primary" v-bind:href="`${CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/export/files?cID=${cid}&fIDs=${file.id}`"><?= t('Download') ?></a>
                    </td>
                    <td class="text-nowrap">
                        <code>{{ key }}</code>
                    </td>
                    <td>
                        <span v-if="file.error" class="text-danger">{{ file.error }}</span>
                        <span v-else v-bind:title="`<?= t('Prefix: %s', '${file.prefix}') ?>`">{{ file.name }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="references?.pages">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('pages') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced Pages') ?></strong>
                <span v-if="operation === 'import' && viewSitemapUrl && someErrors('pages')">
                    - <a v-bind:href="viewSitemapUrl" target="_blank"><?= t('open sitemap') ?></a>
                </span>
            </caption>
            <colgroup>
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(page, path) in references.pages">
                    <td class="text-nowrap">
                        <code>{{ path }}</code>
                    </td>
                    <td>
                        <span v-if="page.error" class="text-danger">{{ page.error }}</span>
                        <a v-else v-bind:href="page.visitLink" target="_blank" v-bind:title="`<?= t('ID: %s', '${page.id}') ?>`">{{ page.name }}</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="references?.pageTypes">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('pageTypes') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced Page Types') ?></strong>
            </caption>
            <colgroup>
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(pageType, key) in references.pageTypes">
                    <td class="text-nowrap">
                        <code>{{ key }}</code>
                    </td>
                    <td>
                        <span v-if="pageType.error" class="text-danger">{{ pageType.error }}</span>
                        <span v-else>{{ pageType.name }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="references?.pageFeeds">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('pageFeeds') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced RSS Page Feeds') ?></strong>
            </caption>
            <colgroup>
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(pageFeed, key) in references.pageFeeds">
                    <td class="text-nowrap">
                        <code>{{ key }}</code>
                    </td>
                    <td>
                        <span v-if="pageFeed.error" class="text-danger">{{ pageFeed.error }}</span>
                        <span v-else>{{ pageFeed.title }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="references?.stacks">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('stacks') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced Stacks and Global Areas') ?></strong>
            </caption>
            <colgroup>
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(stack, key) in references.stacks">
                    <td class="text-nowrap">
                        <code>{{ key }}</code>
                    </td>
                    <td>
                        <span v-if="stack.error" class="text-danger">{{ stack.error }}</span>
                        <a v-else-if="viewStackUrl !== ''" v-bind:href="`${viewStackUrl}/${stack.id}`" target="_blank">{{ stack.name }}</a>
                        <span v-else>{{ stack.name }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="references?.containers">
        <table class="table table-striped table-sm table-condensed caption-top">
            <caption>
                <span v-if="operation === 'import'">{{ someErrors('containers') ? ICON.BAD : ICON.GOOD }}</span>
                <strong><?= t('Referenced Containers') ?></strong>
            </caption>
            <colgroup>
                <col width="1" />
            </colgroup>
            <tbody>
                <tr v-for="(container, key) in references.containers">
                    <td class="text-nowrap">
                        <code>{{ key }}</code>
                    </td>
                    <td>
                        <span v-if="container.error" class="text-danger">{{ container.error }}</span>
                        <span v-else>{{ container.name }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <input type="file" ref="filePicker" style="display: none" />
</div>
<?php
$template = ob_get_contents();
ob_end_clean();

?>
<script>$(document).ready(function() {

const RECOGNIZED_REFERENCE_TYPES = [
    'blockTypes',
    'files',
    'pages',
    'pageTypes',
    'pageFeeds',
    'stacks',
    'containers',
];

Vue.component('blocks-cloner-references-viewer', {
    template: <?= json_encode($template) ?>,
    props: {
        cid: {
            type: Number,
            required: true,
        },
        references: {
            type: Object,
            required: true,
        },
        operation: {
            type: String,
            required: true,
        },
        busy: {
            type: Boolean,
            default: false,
        },
        maxHeight: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            ICON: {
                // HEAVY CHECK MARK
                GOOD: '\u2705',
                // CROSS MARK
                BAD: '\u274c',
            },
            CCM_DISPATCHER_FILENAME: window.CCM_DISPATCHER_FILENAME,
            viewStackUrl: <?= json_encode($viewStackUrl) ?>,
            viewSitemapUrl: <?= json_encode($viewSitemapUrl) ?>,
            viewFileManagerUrl: <?= json_encode($viewFileManagerUrl) ?>,
            uploadToFolder: null,
            uploadingFile: false,
        };
    },
    mounted() {
        this.$refs.filePicker.addEventListener('change', (e) => {
            this.filePickerChanged();
        });
        if (this.operation === 'import') {
            this.retrieveInizialUploadFolder();
        }
    },
    computed: {
        fileIDs() {
            const result = [];
            if (this.references?.files) {
                Object.values(this.references.files).forEach((file) => {
                    if (file.id) {
                        result.push(file.id);
                    }
                });
            }
            return result;
        },
        unrecognizedReferenceTypes() {
            if (!this.references) {
                return [];
            }
            return Object.keys(this.references).filter((key) => !RECOGNIZED_REFERENCE_TYPES.includes(key));
        },
        noReferences() {
            return !this.references || Object.keys(this.references).length === 0;
        },
        viewFileManagerUrlWithFolder() {
            if (!this.viewFileManagerUrl) {
                return '';
            }
            <?php
            if (version_compare(APP_VERSION, '8.9999.9999') <= 0) {
                ?>
                return this.viewFileManagerUrl;
                <?php
            } else {
                ?>
                if (!this.uploadToFolder?.path || this.uploadToFolder.path === '/') {
                    return this.viewFileManagerUrl;
                }
                return `${this.viewFileManagerUrl}/folder/${this.uploadToFolder.id}`;
                <?php
            }
            ?>
        },
    },
    methods: {
        async setUploadFolder(id, fallbackToRoot) {
            id = Number(id) || 0
            const response = await window.fetch(
                `${this.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/check-upload-folder?cID=${this.cid}&folderID=${id}&fallbackToRoot=${fallbackToRoot ? 1 : 0}`,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                }
            );
            if (!response.ok) {
                throw new Error(await response.text());
            }
            const responseData = await response.json();
            if (responseData.error) {
                throw new Error(responseData.error);
            }
            this.uploadToFolder = responseData;
        },
        async retrieveInizialUploadFolder() {
            try {
                await this.setUploadFolder(window.localStorage.getItem('ccmBlocksCloner-uploadToFolderID'), true);
            } catch (e) {
                console.error('Failed to load the initial upload folder', e);
            }
        },
        pickUploadFolder() {
            ConcreteEvent.unsubscribe('FileManagerJumpToFolder.ccmBlocksCloner');
            ConcreteEvent.subscribe('FileManagerJumpToFolder.ccmBlocksCloner', async (e, r) => {
                ConcreteEvent.unsubscribe('FileManagerJumpToFolder.ccmBlocksCloner');
                const folderID = Number(r?.folderID) || 0;
                if (folderID < 1) {
                    return;
                }
                try {
                    await this.setUploadFolder(folderID, false);
                } catch(e) {
                    window.ConcreteAlert.error({
                        message: e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                        delay: 2000,
                    });
                    return;
                }
                window.localStorage.setItem('ccmBlocksCloner-uploadToFolderID', folderID.toString());
            });
            $.fn.dialog.open({
                width: 560,
                height: 500,
                modal: true,
                title: <?= json_encode(t('Select the upload folder')) ?>,
                href: CCM_DISPATCHER_FILENAME + '/ccm/system/dialogs/file/jump_to_folder',
            });
        },
        someErrors(referenceKey) {
            if (!this.references || !this.references.hasOwnProperty(referenceKey)) {
                return false;
            }
            return Object.values(this.references[referenceKey]).some((reference) => reference.error);
        },
        pickFile() {
            this.$refs.filePicker.click();
        },
        async filePickerChanged() {
            let emitCompleted = null;
            try {
                if (this.busy || this.uploadingFile) {
                    return;
                }
                const file = this.$refs.filePicker.files?.length === 1 ? this.$refs.filePicker.files[0] : null;
                if (!file) {
                    return;
                }
                emitCompleted = false;
                this.$emit('files-upload-started');
                const decompressZip = /\.zip$/i.test(file.name) && window.confirm(<?= json_encode(t('Should the ZIP archive be extracted?')) ?>);
                this.uploadingFile = true;
                try {
                    const request = {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                        },
                        body: new FormData(),
                    };
                    request.body.append('file', file);
                    request.body.append('decompressZip', decompressZip ? 'true' : 'false');
                    request.body.append('uploadToFolder', this.uploadToFolder.id);
                    request.body.append('__ccm_consider_request_as_xhr', '1');
                    request.body.append(<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, <?= json_encode($token->generate('blocks_cloner:import:uploadFile')) ?>);
                    const response = await window.fetch(
                        `${this.CCM_DISPATCHER_FILENAME}/ccm/blocks_cloner/dialogs/import/upload-file?cID=${this.cid}`,
                        request
                    );
                    if (!response.ok) {
                        throw new Error(await response.text());
                    }
                    const responseData = await response.json();
                    if (responseData.error) {
                        throw new Error(responseData.error);
                    }
                    emitCompleted = true;
                } finally {
                    this.uploadingFile = false;
                }
            } catch (e) {
                window.ConcreteAlert.error({
                    message: e?.message || e?.toString() || <?= json_encode(t('Unknown error')) ?>,
                    delay: 5000,
                });
            } finally {
                this.$refs.filePicker.value = '';
            }
            if (emitCompleted !== null) {
                this.$emit('files-upload-completed', emitCompleted);
            }
        },
    },
});

});</script>

