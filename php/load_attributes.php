<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_xml.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'AttributesPerProduct.php');

function load_attributes($url){
	$attributes_map = load_xml($url);
	if(!$attributes_map){
		return null;
	}

	$att_map_array = array();
//	$attributes_vector = new \Ds\Vector();
	foreach($attributes_map->Attribute as $SpecificationAttribute){/*
		$product_in_vector = false;
		foreach($attributes_vector as $attributes){
			if($attributes->isProductID((int)$SpecificationAttribute->ProductID)){
				$product_in_vector = true;
				$attributes->addOption((int)$SpecificationAttribute->SpecificationAttributeOptionID);
				break;
			}
		}
		if(!$product_in_vector){
			$attributes_vector->push(new AttributesPerProduct((int)$SpecificationAttribute->ProductID,(int)$SpecificationAttribute->SpecificationAttributeOptionID));	
		}
		*/
		if(array_key_exists((int)$SpecificationAttribute->ProductID, $att_map_array)){
		 	/*if(!in_array((int)$SpecificationAttribute->SpecificationAttributeOptionID, $att_map_array[(int)$SpecificationAttribute->ProductID])){
				$att_map_array[(int)$SpecificationAttribute->ProductID][]=(int)$SpecificationAttribute->SpecificationAttributeOptionID;
			}*/
			$att_map_array[(int)$SpecificationAttribute->ProductID]->addOption((int)$SpecificationAttribute->SpecificationAttributeOptionID);
		}else{
			/*$att_map_array[(int)$SpecificationAttribute->ProductID]=array();
			$att_map_array[(int)$SpecificationAttribute->ProductID][]=(int)$SpecificationAttribute->SpecificationAttributeOptionID;*/
			$att_map_array[(int)$SpecificationAttribute->ProductID] =
				new AttributesPerProduct((int)$SpecificationAttribute->ProductID, (int)$SpecificationAttribute->SpecificationAttributeOptionID);
		}
		unset($SpecificationAttribute);
	}
	unset($attributes_map);
	return $att_map_array;
}




//test
//print_r(load_attributes("./xml/pr_att1.xml"),false);
//echo "Exit";
//print_r(load_products("./xml/bestdealers.xml"),false);
//load_products("./xml/bestdealers.xml");
//foreach(load_attributes("./xml/pr_att1.xml") as $attpp){print_r($attpp,false);/*print_r($attpp->getOptions(),false);*/}
//foreach(load_attributes("./xml/pr_att1.xml") as $attpp){$attpp->printOptions();}
//foreach(load_attributes("./xml/_attribute1.xml") as $attpp){print_r($attpp,false);/*print_r($attpp->getOptions(),false);*/}
//
//if(!AttributesPerProduct::load_attribute_specifications_public("./xml/_attribute2.xml")){
//	echo "Fail to load attribite values.\n"; exit(0);
//}
//foreach(load_attributes("./xml/_attribute1.xml") as $attpp){print_r($attpp,false);/*print_r($attpp->getOptions(),false);*/}

//if(!AttributesPerProduct::load_attribute_specifications_public('http://bestdealers:t$Gm$,xt5SHg@tech-bg.com/files/bestdealers/_attribute2.xml')){
//	echo "Fail to load attribite values.\n"; exit(0);
//}
//foreach(load_attributes('http://bestdealers:t$Gm$,xt5SHg@tech-bg.com/files/bestdealers/_attribute1.xml') as $attpp){print_r($attpp,false);/*print_r($attpp->getOptions(),false);*/}

?>
