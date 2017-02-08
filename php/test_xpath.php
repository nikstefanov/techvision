<?php
require_once './load_xml.php';

function load_xml_old($filepath){
	libxml_use_internal_errors(true);
	$xml = simplexml_load_file ($filepath,"SimpleXMLElement_Addl");
	if(!$xml){
		echo "Failed loading ".$filepath."\n";
    		foreach(libxml_get_errors() as $error) {
        		echo "\t", $error->message;
    		}		
	}
	return $xml;
}




//print_r(build_categories_tree("./xml/Categories.xml"),false);


/*
$cat_xml = load_xml("./xml/Categories.xml");
$node = $cat_xml->xpath("/root/Categories[CategoryID=6]");
print_r($node,false);
*/



$schema_iterator = new SimpleXMLIterator("file:///home/bitrix/techvision/xml/magento_schema.xml",0,true);
$nodes = $schema_iterator->xpath("/magento_schema/attribute[not(update)]");
foreach($nodes as $node){unset($node[0]);}
//$nodes = $schema_iterator->xpath("/magento_schema/attribute[update]");
//print_r(count($nodes),false);
echo $schema_iterator->count()."\n";
for( $schema_iterator->rewind(); $schema_iterator->valid(); $schema_iterator->next() ) {
	echo $schema_iterator->current()->name."\n";	
}
?>
