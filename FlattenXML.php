<?php

namespace com\davemeetsworld\flattenXml;

use DOMDocument;
use DOMXPath;
use DOMException;

class FlattenXml
{
    public function flattenXml($data)
    {
        set_error_handler([$this, 'handleXmlErrors']);
        $dom = new DOMDocument;
        $dom->loadXML($data);
        restore_error_handler();
        $xpath = new DOMXPath($dom);
        $result = array();

        foreach ($xpath->query('//*[count(*) = 0]') as $node) {
            $path = array();
            $val = $node->nodeValue;
            do {
                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attribute) {
                        $path[] = sprintf('%s[%s]', $attribute->nodeName, $attribute->nodeValue);
                    }
                }
                $path[] = $node->nodeName;
            } while ($node = $node->parentNode);
            $result[implode('.', array_reverse($path))] = $val;
        }

        return $result;
    }

    public function handleXmlErrors($errno, $errstr, $errfile, $errline)
    {
        if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0)) {
            restore_error_handler();
            throw new DOMException($errstr);
        } else {
            return false;
        }
    }
}

$t = new FlattenXML();
var_dump($t->flattenXml("<s blah=\"lip\"><t>Dave</t><e>Less</e></s>"));
