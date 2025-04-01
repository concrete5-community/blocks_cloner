<?php

namespace Concrete\Package\BlocksCloner\Converter\Import;

defined('C5_EXECUTE') or die('Access Denied.');

use JsonSerializable;

class BlockType implements JsonSerializable
{
    /**
     * @var string
     */
    private $newBlockTypeHandle = '';

    /**
     * @var array
     */
    private $templateRemapping = [];

    /**
     * @var array
     */
    private $addRecordFields = [];

    /**
     * @var array
     */
    private $ensureIntegerFields = [];

    /**
     * @var array
     */
    private $removeRecordFields = [];

    /**
     * @var array
     */
    private $fontAwesome4to5Fields = [];

    /**
     * @var array
     */
    private $renameDataTables = [];

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNewBlockTypeHandle($value)
    {
        $this->newBlockTypeHandle = (string) $value;

        return $this;
    }

    /**
     * @param string $sourceTemplate
     * @param string|null $newTemplate
     * @param string $newCustomClasses
     *
     * @return $this
     */
    public function addTemplateRemapping($sourceTemplate, $newTemplate = null, $newCustomClasses = '')
    {
        $sourceTemplate = preg_replace('/\.php$/', '', (string) $sourceTemplate);
        if (isset($this->templateRemapping[$sourceTemplate])) {
            throw new \RuntimeException(t('Duplicated source template handle: %s', $sourceTemplate));
        }
        if ($newTemplate !== null) {
            $newTemplate = (string) $newTemplate;
        }
        $newCustomClasses = (string) $newCustomClasses;
        if ($newTemplate === null) {
            if ($newCustomClasses === '') {
                throw new \RuntimeException(t('No conversion will be applied'));
            }
            $this->templateRemapping[$sourceTemplate] = [
                'newCustomClasses' => $newCustomClasses,
            ];
        } elseif ($newCustomClasses === '') {
            $this->templateRemapping[$sourceTemplate] = $newTemplate;
        } else {
            $this->templateRemapping[$sourceTemplate] = [
                'newTemplate' => $newTemplate,
                'newCustomClasses' => $newCustomClasses,
            ];
        }

        return $this;
    }

    /**
     * @param string $tableName
     * @param array $namesAndValues example ['newFieldName' => 'newFieldValue']
     *
     * @return $this
     */
    public function addRecordFields($tableName, array $namesAndValues)
    {
        $tableName = (string) $tableName;
        if (isset($this->addRecordFields[$tableName])) {
            throw new \RuntimeException(t('Duplicated table name: %s', $tableName));
        }
        $this->addRecordFields[$tableName] = (object) array_map('strval', $namesAndValues);

        return $this;
    }

    /**
     * @param string $tableName
     * @param string[] $fieldNames
     *
     * @return $this
     */
    public function ensureIntegerFields($tableName, array $fieldNames)
    {
        $tableName = (string) $tableName;
        if (isset($this->ensureIntegerFields[$tableName])) {
            throw new \RuntimeException(t('Duplicated table name: %s', $tableName));
        }
        $this->ensureIntegerFields[$tableName] = array_values(array_map('strval', $fieldNames));

        return $this;
    }

    /**
     * @param string $tableName
     * @param string[] $fieldNames
     *
     * @return $this
     */
    public function removeRecordFields($tableName, array $fieldNames)
    {
        $tableName = (string) $tableName;
        if (isset($this->removeRecordFields[$tableName])) {
            throw new \RuntimeException(t('Duplicated table name: %s', $tableName));
        }
        $this->removeRecordFields[$tableName] = array_values(array_map('strval', $fieldNames));

        return $this;
    }

    /**
     * @param string $newName
     * @param string $newName
     *
     * @return $this
     */
    public function renameDataTable($oldName, $newName)
    {
        $oldName = (string) $oldName;
        if (isset($this->renameDataTables[$oldName])) {
            throw new \RuntimeException(t('Duplicated table name: %s', $oldName));
        }
        $this->renameDataTables[$oldName] = (string) $newName;

        return $this;
    }

    /**
     * @param string $tableName
     * @param string[] $fieldNames
     *
     * @return $this
     */
    public function fontAwesome4to5Fields($tableName, array $fieldNames)
    {
        $tableName = (string) $tableName;
        if (isset($this->fontAwesome4to5Fields[$tableName])) {
            throw new \RuntimeException(t('Duplicated table name: %s', $tableName));
        }
        $this->fontAwesome4to5Fields[$tableName] = array_values(array_map('strval', $fieldNames));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $result = [];
        if ($this->newBlockTypeHandle !== '') {
            $result['newBlockTypeHandle'] = $this->newBlockTypeHandle;
        }
        if ($this->templateRemapping !== []) {
            $result['templateRemapping'] = $this->templateRemapping;
        }
        if ($this->addRecordFields !== []) {
            $result['addRecordFields'] = $this->addRecordFields;
        }
        if ($this->ensureIntegerFields !== []) {
            $result['ensureIntegerFields'] = $this->ensureIntegerFields;
        }
        if ($this->removeRecordFields !== []) {
            $result['removeRecordFields'] = $this->removeRecordFields;
        }
        if ($this->fontAwesome4to5Fields !== []) {
            $result['fontAwesome4to5Fields'] = $this->fontAwesome4to5Fields;
        }
        if ($this->renameDataTables !== []) {
            $result['renameDataTables'] = $this->renameDataTables;
        }

        return $result;
    }
}
