<?php
namespace Docs\MainBundle\Serializer;

use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\Context;

/**
 * Extends the XmlSerializationVisitor to allow
 * array keys as xml node names
 * @author hbotev
 *
 */
class DocsXmlSerializationVisitor extends XmlSerializationVisitor
{
    /**
     * (non-PHPdoc)
     * @see \JMS\Serializer\XmlSerializationVisitor::visitArray()
     */
    public function visitArray($data, array $type, Context $context)
    {
//         parent::visitArray($data, $type, $context);
        if (null === $this->document) {
            $this->document = $this->createDocument(null, null, true);
        }

        if (null !== $this->getCurrentMetadata()
            && null !== $this->getCurrentMetadata()->xmlEntryName
        ) {
            $entryName = $this->getCurrentMetadata()->xmlEntryName;
        } else {
            $entryName = "entry";
        }

        if (null !== $this->getCurrentMetadata()
            && null !== $this->getCurrentMetadata()->xmlKeyAttribute
        ) {
            $keyAttributeName = $this->getCurrentMetadata()->xmlKeyAttribute;
        } else {
            $keyAttributeName = null;
        }

        foreach ($data as $k => $v) {
            $tagName = $this->isElementNameValid($k) ? $k : $entryName;

            $entryNode = $this->document->createElement($tagName);
            $this->getCurrentNode()->appendChild($entryNode);
            $this->setCurrentNode($entryNode);

            if (null !== $keyAttributeName) {
                $entryNode->setAttribute($keyAttributeName, (string) $k);
            }

            if (null !== $node = $this->getNavigator()->accept($v, $this->getElementType($type), $context)) {
                $this->getCurrentNode()->appendChild($node);
            }

            $this->revertCurrentNode();
        }
    }

    /**
     * Check if string is valid xml tag name
     * @param string $name
     * @return bool
     */
    protected function isElementNameValid($name)
    {
        return $name && false === strpos($name, ' ') && preg_match('#^[\pL_][\pL0-9._-]*$#ui', $name);
    }
}
