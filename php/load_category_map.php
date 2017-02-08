<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_xml.php');

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

function add_category_xml(&$cat_map, $category){
	if($category->Import && (string)$category->Import==='false'){
		$cat_map['Import'] = false && $cat_map['Import'];
	}else{  $cat_map['Import'] = true  && $cat_map['Import'];
	}
	if($category->Attribute_set)$cat_map['Attribute_set'][] = (string)$category->Attribute_set;
	if($category->Map){
		foreach($category->Map->CID as $cid){$cat_map['Map'][]= (int)$cid;}
	}
	if($category->Title){
		$cat_map['Title'] =
			array_merge($cat_map['Title'], explode(" ", (string)$category->Title));
	}
}

function load_category_map_xml($url){
	$cxml = load_xml($url);
	if(!$cxml)return null;

	$category_map=array();
	foreach($cxml->Category as $category){
		$category_map[(int)$category->ID] = array();
		$category_map[(int)$category->ID]['ID'] = (int)$category->ID;
		$category_map[(int)$category->ID]['Import'] = true;
		$category_map[(int)$category->ID]['Map'] = array();
		$category_map[(int)$category->ID]['Attribute_set'] = array();
		$category_map[(int)$category->ID]['Title'] = array();
			add_category_xml($category_map[(int)$category->ID], $category);
		if($category->Parent[0]){
			$xpath_nodes = $cxml->xpath("/Categories/Category[ID=".(int)$category->Parent[0]->ID."]");
			if(!$xpath_nodes){echo "Cannot find parent ID=".(int)$category->Parent[0]->ID."\n";return null;}
			if(count($xpath_nodes)>1){echo "More than one parent category.".(int)$category->Parent[0]->ID;return null;}
			add_category_xml($category_map[(int)$category->ID], $xpath_nodes[0]);
		}
		if($category->Parent[1]){
			$xpath_nodes = $cxml->xpath("/Categories/Category[ID=".(int)$category->Parent[1]->ID."]");
			if(!$xpath_nodes){echo "Cannot find parent ID=".(int)$category->Parent[1]->ID."\n";return null;}
			if(count($xpath_nodes)>1){echo "More than one parent category.".(int)$category->Parent[1]->ID;return null;}
			add_category_xml($category_map[(int)$category->ID], $xpath_nodes[0]);
		}/*
		if(strlen($category_map[(int)$category->ID]['Map']))
			$category_map[(int)$category->ID]['Map'] = substr($category_map[(int)$category->ID]['Map'], 0, -1);*/
	}
	return $category_map;
}

//test
//print_r(load_category_map_xml("./xml/cat_map_1_4.xml"), false);
//load_category_map_xml("./xml/cat_map_1_4.xml")
//print_r(load_category_map_xml("file:///home/bitrix/techvision"."/xml/cat_map_1_4.xml"), false);
?>
