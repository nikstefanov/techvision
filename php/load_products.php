<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_xml.php');
//$products = simplexml_load_file("xml/pr.xml");
//print_r($products->Product[0]->ID,false);
//echo $products->Product[0]->ID;


function load_products($url){
	$products = load_xml($url);
	if(!$products){
		return null;
	}
	
	$id_arr = array();
	foreach($products->Product as $prod){
		//if(!in_array((string)$prod->MimeType, $mediaTypesArray)){$mediaTypesArray[] = (string)$prod->PictureID;$mediaTypesArray[] = (string)$prod->MimeType;}

		if(in_array((string)$prod->ID, $id_arr)){
			$index = array_search((string)$prod->ID, $id_arr);
			if(isset($prod->CatID) && !in_array((string)$prod->CatID, $products_arr[$index]['CatID'])){
				$products_arr[$index]['CatID'][] = (string)$prod->CatID;
			}/*
			$img_in_arr = false;
			foreach($products_arr[$index]['media'] as $img_arr){
				if($img_arr[0]==(string)$prod->PictureID && $img_arr[1]==(string)$prod->MimeType){
					$img_in_arr = true;
					break;
				}
			}
			if(!$img_in_arr){*/
			if(isset($prod->PictureID) && !in_array(array((string)$prod->PictureID, (string)$prod->MimeType), $products_arr[$index]['media'])){
				$products_arr[$index]['media'][] = array((string)$prod->PictureID, (string)$prod->MimeType);
			}
		}else{
			$index = count($id_arr);
			$id_arr[] = (string)$prod->ID;
			$products_arr[$index]['id'] = (string)$prod->ID;
			if(isset($prod->CatID)){$products_arr[$index]['CatID'] = array((string)$prod->CatID);}
			$products_arr[$index]['ManID'] = (string)$prod->ManID;
			$products_arr[$index]['Name'] = (string)$prod->Name;
			$products_arr[$index]['ShortDescr'] = (string)$prod->ShortDescr;
			$products_arr[$index]['FullDescription'] = (string)$prod->FullDescription;	
			$products_arr[$index]['EndPrice'] = (string)$prod->EndPrice;
			$products_arr[$index]['DealerPrice'] = (string)$prod->DealerPrice;
			$products_arr[$index]['Quantity'] = (string)$prod->Quantity;
			$products_arr[$index]['SKU'] = (string)$prod->SKU;
			if(isset($prod->PictureID)){$products_arr[$index]['media'] = array(array((string)$prod->PictureID,(string)$prod->MimeType));}
		}

		unset($prod);
	}
	//print_r($mediaTypesArray,false);
	unset($products);
	return $products_arr;
}

//test
//print_r(load_products("./xml/pr1.xml"),false);
//echo "Exit";
//print_r(load_products("./xml/bestdealers.xml"),false);
//load_products("./xml/bestdealers.xml");
//load_products("http://bestdealers:t$Gm$,xt5SHg@tech-bg.com/files/bestdealers/bestdealers.xml");
//load_products("http://tech-bg.com/files/bestdealers/bestdealers.xml");
//$login = 'bestdealers';
//$password = 't$Gm$,xt5SHg';
//$url = 'http://'.$login.':'.$password.'@'.'tech-bg.com/files/bestdealers/bestdealers.xml';
//load_products($url);
?>
