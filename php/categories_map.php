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


function build_categories_map($url){
	$cat_xml = load_xml($url);
	if(!$cat_xml){echo "Fail to load XML.\n";exit(0);}

//	$categories_array = array();
	$Categories="<Categories>\n";

	foreach($cat_xml->Categories as $category_note){

		$cnode="<Category>\n";
		$cnode.="\t<ID>".(int)$category_note->CategoryID."</ID>\n";
		$cnode.="\t<Name>".(string)$category_note->Name."</Name>\n";
		$cid = (int)$category_note->ParentCategoryID;
		if($cid!=0){
			do{
				$xpath_nodes = $cat_xml->xpath("/root/Categories[CategoryID=".$cid."]");
				if(!$xpath_nodes){echo "Cannot find parent ID=".$cid."\n";break;}
				if(count($xpath_nodes)>1){echo "More than one parent category.";break;}
				$i_node = $xpath_nodes[0];
				$cnode.="\t<Parent><ID>".(int)$i_node->CategoryID."</ID><Name>".(string)$i_node->Name."</Name></Parent>\n";
				$cid = (int)$i_node->ParentCategoryID;
			}while($cid!=0);
		}
//		$cnode.="\t<Map><CID></CID><CID></CID></Map>\n";
//		$cnode.="\t<Atribute_set></Atribute_set>\n";
		$cnode.="</Category>\n";
//		$categories_array[(int)$category_note->CategoryID] = $cnode;
		$Categories.=$cnode;
		
	}
	$Categories.="</Categories>\n";
//	return $categories_array;
	return $Categories;

}

function export_array($cat_array, $filename){
	$file_string = "";
	foreach($cat_array as $category){
		$file_string .= '"'.$category->cat_path.'","'.$category->name_path.'"'."\r\n";
	}
	file_put_contents($filename, $file_string);
}

class Category{
	public $id;
	public $name;
	//public $successors_array;
	//public $main;
	//public $parentid;
	public $cat_path;
	public $name_path;

	function __construct($id1,$name1="",/*$successors=null,$main1=false,$parentid1=0*/$cat_path1="",$name_path1=""){
		$this->id = $id1;
		$this->name = $name1;
		//$this->successors_array = $successors;
		//$this->main=$main1;
		//$this->parentid=$parentid1;
		$this->cat_path = $cat_path1;
		$this->name_path = $name_path1;
	}

	function set_all($id1,$name1="",/*$successors=null,$main1=false,$parentid1=0*/$cat_path1="",$name_path1=""){
		$this->id = $id1;
		$this->name = $name1;
		//$this->successors_array = $successors;
		//$this->main=$main1;
		//$this->parentid=$parentid1;
		$this->cat_path = $cat_path1;
		$this->name_path = $name_path1;
	}
}


//print_r(build_categories_map("./xml/Categories.xml"),false);
//export_array(build_categories_tree("./xml/Categories.xml"),"categories.csv");
file_put_contents("cat_map_1.xml", build_categories_map("./xml/Categories.xml"));
?>
