<?php

namespace Concrete\Package\BlocksCloner\Controller\Dialog\Export;

use Concrete\Core\Entity\Attribute\Value\PageValue;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Package\BlocksCloner\Controller\Dialog\Export;
use Concrete\Package\BlocksCloner\Subject;
use Concrete\Package\BlocksCloner\XmlParser;
use Exception;
use Punic\Comparer;
use SimpleXMLElement;
use Throwable;

defined('C5_EXECUTE') or die('Access Denied.');

class Attributes extends Export
{
    protected $viewPath = '/dialogs/export/attributes';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\UI\Controller::view()
     */
    public function view()
    {
        parent::view();
        $serializedAttributeKeys = array_map(
            function (PageValue $attributeValue) {
                return $this->serializeAttributeKey($attributeValue);
            },
            $this->getAssignedAttributeValues()
        );
        $this->set('attributeKeys', $serializedAttributeKeys);
        $this->set('token', $this->app->make(Token::class));
        $this->addHeaderItem(
            <<<'EOT'
<style>
#ccm-blockscloker-export-attributes {
    display: flex;
    height: 100%;
    max-height: 100%;
}
#ccm-blockscloker-export-attributes .ccmbc-attributes {
    max-width: 300px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

#ccm-blockscloker-export-attributes .ccmbc-attributes-list {
    flex-grow: 1;
    overflow-y: auto;
    margin-bottom: 5px;
    flex-basis: 0;
}
#ccm-blockscloker-export-attributes .ccmbc-attributes-list button {
    width: 100%;
    margin-bottom: 5px;
    text-overflow: ellipsis;
}
#ccm-blockscloker-export-attributes .ccmbc-attributes-list button:last-child {
    margin-bottom: 0;
}

#ccm-blockscloker-export-attributes .ccmbc-attributes-aside {
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}

#ccm-blockscloker-export-attributes .ccmbc-result {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    padding-left: 10px;
}

#ccm-blockscloker-export-attributes .ccmbc-result-references {
    flex-shrink: 0;
    max-height: 50%;
    overflow-y: auto;
    padding: 10px;
    margin-bottom: 5px;
}
#ccm-blockscloker-export-attributes .ccmbc-result-xml {
    flex-grow: 1;
    display: flex;
}

#ccm-blockscloker-export-attributes .ccmbc-result-xml textarea {
    flex-grow: 1;
}
</style>
EOT
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function export()
    {
        $token = $this->app->make(Token::class);
        if (!$token->validate('blocks_cloner:export:attributes:export')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $selectedAttributeKeyHandles = preg_split('/\s+/', $this->request->request->get('attributeKeyHandles', ''), -1, PREG_SPLIT_NO_EMPTY);
        $selectedAttributeValues = array_filter(
            $this->getAssignedAttributeValues(),
            static function (PageValue $attributeValue) use ($selectedAttributeKeyHandles) {
                return in_array($attributeValue->getAttributeKey()->getAttributeKeyHandle(), $selectedAttributeKeyHandles, true);
            }
        );
        if ($selectedAttributeValues === []) {
            throw new UserMessageException(t('Please select at least one attribute to be exported.'));
        }
        $sx = simplexml_load_string('<attributes />');
        foreach ($selectedAttributeValues as $selectedAttributeValue) {
            $this->exportAttributeValue($selectedAttributeValue, $sx);
        }
        $this->convert($sx);
        $parser = $this->app->make(XmlParser::class);

        return $this->app->make(ResponseFactoryInterface::class)->json([
            'references' => $this->serializeReferences($parser->extractReferences($sx, Subject::ATTRIBUTES)),
            'xml' => $this->formatXml($sx, true),
        ]);
    }

    /**
     * @return \Concrete\Core\Entity\Attribute\Value\PageValue[]
     */
    private function getAssignedAttributeValues()
    {
        $page = $this->getPage();
        $pageVersion = $page->getVersionObject();
        $attributesCategory = $pageVersion->getObjectAttributeCategory();
        $attributeValues = $attributesCategory->getAttributeValues($pageVersion);
        $cmp = new Comparer();
        usort($attributeValues, static function (PageValue $a, PageValue $b) use ($cmp) {
            return $cmp->compare($a->getAttributeKey()->getAttributeKeyHandle(), $b->getAttributeKey()->getAttributeKeyHandle());
        });

        return $attributeValues;
    }

    /**
     * @return array
     */
    private function serializeAttributeKey(PageValue $attributeValue)
    {
        $attributeKey = $attributeValue->getAttributeKey();

        return [
            'id' => (int) $attributeKey->getAttributeKeyID(),
            'handle' => $attributeKey->getAttributeKeyHandle(),
            'name' => $attributeKey->getAttributeKeyDisplayName('text'),
        ];
    }

    private function exportAttributeValue(PageValue $attributeValue, SimpleXMLElement $xParent)
    {
        $attributeKey = $attributeValue->getAttributeKey();
        $attributeKeyController = $attributeKey->getController();
        /** @var \Concrete\Core\Attribute\Controller $attributeKeyController */
        $attributeKeyController->setAttributeValue($attributeValue);
        $xAttribute = $xParent->addChild('attributekey');
        $xAttribute['handle'] = $attributeKey->getAttributeKeyHandle();
        $error = null;
        try {
            $attributeKeyController->exportValue($xAttribute);
        } catch (Exception $x) {
            $error = $x;
        } catch (Throwable $x) {
            $error = $x;
        }
        if ($error !== null) {
            throw new UserMessageException(
                t('The following error occurred while exporting the attribute %s:', $attributeKey->getAttributeKeyDisplayName('text'))
                . "\n" . trim($error->getMessage())
            );
        }
    }
}
