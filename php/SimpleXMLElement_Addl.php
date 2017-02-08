<?php
class SimpleXMLElement_Addl extends SimpleXMLElement {

    public function find($xpath) {
        $tmp = $this->xpath($xpath);
        return isset($tmp[0])? $tmp[0]: null;
    }

    public function remove() {
        $dom = dom_import_simplexml($this);
        return $dom->parentNode->removeChild($dom);
    }

}

// Example: removing the <bar> element with id = 1
//$foo = new SimpleXMLElement_Addl('<foo><bar id="1"/><bar id="2"/></foo>');
//$foo->find('//bar[@id="1"]')->remove();
//print $foo->asXML(); // <foo><bar id="2"/></foo>
?>
