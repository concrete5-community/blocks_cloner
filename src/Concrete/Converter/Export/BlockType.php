<?php

namespace Concrete\Package\BlocksCloner\Converter\Export;

defined('C5_EXECUTE') or die('Access Denied.');

class BlockType
{
    private $contentFields = [];

    private $fileSetIDFields = [];

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param string $tableName
     * @param string[] $fieldNames
     *
     * @return $this
     */
    public function addContentField($tableName, array $fieldNames)
    {
        $tableName = (string) $tableName;
        $fieldNames = array_values(array_map('strval', $fieldNames));
        if (!isset($this->contentFields[$tableName])) {
            $this->contentFields[$tableName] = [];
        }
        $this->contentFields[$tableName] = array_values(
            array_unique(
                array_merge(
                    $this->contentFields[$tableName],
                    array_values(array_map('strval', $fieldNames))
                )
            )
        );

        return $this;
    }

    /**
     * @param string $tableName
     * @param string[] $fieldNames
     *
     * @return $this
     */
    public function addFileSetIDField($tableName, array $fieldNames)
    {
        $tableName = (string) $tableName;
        $fieldNames = array_values(array_map('strval', $fieldNames));
        if (!isset($this->fileSetIDFields[$tableName])) {
            $this->fileSetIDFields[$tableName] = [];
        }
        $this->fileSetIDFields[$tableName] = array_values(
            array_unique(
                array_merge(
                    $this->fileSetIDFields[$tableName],
                    array_values(array_map('strval', $fieldNames))
                )
            )
        );

        return $this;
    }

    /**
     * @param string $tableName
     *
     * @return string[]
     */
    public function getContentFieldsForTable($tableName)
    {
        $tableName = (string) $tableName;

        return isset($this->contentFields[$tableName]) ? $this->contentFields[$tableName] : [];
    }

    /**
     * @param string $tableName
     *
     * @return string[]
     */
    public function getFileSetIDFieldsForTable($tableName)
    {
        $tableName = (string) $tableName;

        return isset($this->fileSetIDFields[$tableName]) ? $this->fileSetIDFields[$tableName] : [];
    }
}
