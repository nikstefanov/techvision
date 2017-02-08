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


function build_categories_tree($url){
	$cat_xml = load_xml($url);
	if(!$cat_xml){echo "Fail to load XML.\n";exit(0);}

	//$c0 = new Category(0,"Root Category",array());
	$categories_array = array();
	/*
	for($index=0; $index<$cat_xml->count();$index++){
		$categories_array[$index] = new Category((int)$cat_xml->Categories[$index]->CategoryID,(string)$cat_xml->Categories[$index]->Name,null,((int)$cat_xml->Categories[$index]->ParentCategoryID ? false : true),(int)$cat_xml->Categories[$index]->ParentCategoryID);
	}*/

	foreach($cat_xml->Categories as $category_note){
		/*
		$cat_path="";
		$pid = (int)$category_note->ParentCategoryID;
		$cat_path = (int)$category_note->CategoryID;
		$cat_path = $pid .','. $cat_path;
		while($pid!=0){
			$parent_node = $cat_xml->xpath("/root/Categories[CategoryID=".$pid."]");
			if(!$parent_node){echo "Cannot find parent ID=".$pid."\n";break;}
			if(count($parent_node)>1){echo "More than one parent category.";break;}
			$pid = (int)$parent_node[0]->ParentCategoryID;
			$cat_path = $pid .','. $cat_path;
			//echo $pid."\n";
		};*/

		$cat_path="";
		$name_path="";
		$i_node = $category_note;
		$cid = (int)$category_note->CategoryID;
		do{
			$xpath_nodes = $cat_xml->xpath("/root/Categories[CategoryID=".$cid."]");
			if(!$xpath_nodes){echo "Cannot find parent ID=".$cid."\n";break;}
			if(count($xpath_nodes)>1){echo "More than one parent category.";break;}
			$i_node = $xpath_nodes[0];
			$cat_path = (int)$i_node->CategoryID . ',' . $cat_path;
			$name_path = (string)$i_node->Name.', '.$name_path;
			$cid = (int)$i_node->ParentCategoryID;
		}while($cid!=0);


		$categories_array[(int)$category_note->CategoryID] = new Category(
										(int)$category_note->CategoryID,
										(string)$category_note->Name,
										substr($cat_path, 0, -1),
										substr($name_path, 0, -2)
										//(int)$category_note->ParentCategoryID ? false : true,
										//(int)$category_note->ParentCategoryID
									);
//		$categories_array[$index] = new Category((int)$cat_xml->Categories[$index]->CategoryID,(string)$cat_xml->Categories[$index]->Name,null,((int)$cat_xml->Categories[$index]->ParentCategoryID ? false : true),(int)$cat_xml->Categories[$index]->ParentCategoryID);
		
	}	
	return $categories_array;

}

function export_array($cat_array, $filename){
	$file_string = "";
	foreach($cat_array as $category){
		$file_string .= '"'.$category->id.'","'.$category->cat_path.'","'.$category->name_path.'"'."\r\n";
		/*
		$file_string .= '"'.$category->cat_path.'",';
		foreach(explode(", ", $category->name_path) as $item){
			$file_string .= '"'.$item.'",';
		}
		$file_string .= "\r\n";
		*/
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


//print_r(build_categories_tree("./xml/Categories.xml"),false);
export_array(build_categories_tree("./xml/Categories.xml"),"categories.csv");
?>
