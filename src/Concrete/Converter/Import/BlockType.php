<?php

namespace Concrete\Package\BlocksCloner\Converter\Import;

use Closure;

defined('C5_EXECUTE') or die('Access Denied.');

class BlockType
{
    /**
     * @var string
     */
    private $newBlockTypeHandle = '';

    /**
     * @var array
     */
    private $templateRemappings = [];

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
     * @var \Closure|null
     */
    private $customConversion = null;

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
     * @return string
     */
    public function getNewBlockTypeHandle()
    {
        return $this->newBlockTypeHandle;
    }

    /**
     * @param string $sourceTemplate
     * @param string|null $newTemplate
     * @param string|string[] $newCustomClasses
     *
     * @return $this
     */
    public function addTemplateRemapping($sourceTemplate, $newTemplate = null, $newCustomClasses = '')
    {
        $sourceTemplate = preg_replace('/\.php$/', '', (string) $sourceTemplate);
        if (isset($this->templateRemappings[$sourceTemplate])) {
            throw new \RuntimeException(t('Duplicated source template handle: %s', $sourceTemplate));
        }
        if ($newTemplate !== null) {
            $newTemplate = (string) $newTemplate;
        }
        if (is_array($newCustomClasses)) {
            $newCustomClasses = array_values(
                array_filter(
                    array_map(
                        static function ($cls) {
                            return is_string($cls) ? trim($cls) : '';
                        },
                        $newCustomClasses
                    ),
                    static function ($cls) {
                        return $cls !== '';
                    }
                )
            );
        } else {
            $newCustomClasses = is_string($newCustomClasses) ? preg_split('/\s+/', $newCustomClasses, -1, PREG_SPLIT_NO_EMPTY) : [];
        }
        if ($newTemplate === null && $newCustomClasses === []) {
            throw new \RuntimeException(t('No conversion will be applied'));
        }
        $this->templateRemappings[$sourceTemplate] = [
            'newTemplate' => $newTemplate,
            'newCustomClasses' => $newCustomClasses,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateRemappings()
    {
        return $this->templateRemappings;
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
        $this->addRecordFields[$tableName] = array_map('strval', $namesAndValues);

        return $this;
    }

    /**
     * @return array
     */
    public function getAddRecordFields()
    {
        return $this->addRecordFields;
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
     * @return array
     */
    public function getEnsureIntegerFields()
    {
        return $this->ensureIntegerFields;
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
     * @return array
     */
    public function getRemoveRecordFields()
    {
        return $this->removeRecordFields;
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
     * @return array
     */
    public function getFontAwesome4to5Fields()
    {
        return $this->fontAwesome4to5Fields;
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
     * @return array
     */
    public function getRenameDataTables()
    {
        return $this->renameDataTables;
    }

    /**
     * @param \Closure|null $closure
     *
     * @return $this
     */
    public function setCustomConversion($closure)
    {
        $this->customConversion = $closure instanceof Closure ? $closure : null;

        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getCustomConversion()
    {
        return $this->customConversion;
    }
}
