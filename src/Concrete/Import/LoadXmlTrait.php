<?php

namespace Concrete\Package\BlocksCloner\Import;

use Concrete\Core\Error\UserMessageException;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @readonly
 */
trait LoadXmlTrait
{
    /**
     * @param string|mixed $xml
     *
     * @throws \Concrete\Core\Error\UserMessageException
     *
     * @return \SimpleXMLElement
     */
    protected function loadXml($xml)
    {
        if (!is_string($xml) || $xml === '') {
            throw new UserMessageException(t('Please specify the XML to be imported'));
        }
        $restore = libxml_use_internal_errors(true);
        try {
            $sx = simplexml_load_string($xml);
            $errors = libxml_get_errors();
        } finally {
            libxml_use_internal_errors($restore);
        }
        if (!empty($errors)) {
            $lines = [];
            foreach ($errors as $error) {
                $line = '';
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $line .= '[' . t('Warning') . '] ';
                        break;
                    case LIBXML_ERR_ERROR:
                        $line .= '[' . t('Error') . '] ';
                        break;
                    case LIBXML_ERR_FATAL:
                        $line .= '[' . t('Fatal error') . '] ';
                        break;
                }
                $line .= $error->message;
                if ($error->line) {
                    $line .= ' (' . t('at line %s', $error->line) . ')';
                }
                $lines[] = $line;
            }
            throw new UserMessageException(implode("\n", $lines));
        }
        if (!$sx) {
            throw new UserMessageException(t('Failed to parse the XML'));
        }

        return $sx;
    }
}
