<?PHP

ini_set( 'memory_limit', '384M');

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_xml.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_products.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_attributes.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'load_category_map.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'AttributesPerProduct.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'find_new_update.php');

function init(){
	global $schema_iterator;
	global $mimetypes_map;
	global $products;
	global $options;
	global $vendors_map;
	global $category_map;
	global $config;
	global $update_array;

	$config = parse_ini_file("config.ini");
	$config['result_base_absolute'] = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.$config['result_base']);
	$config['new_items_file_absolute'] = $config['result_base_absolute'].DIRECTORY_SEPARATOR.$config['new_items_file'];
	$config['update_items_file_absolute'] = $config['result_base_absolute'].DIRECTORY_SEPARATOR.$config['update_items_file'];
	$config['log_file_absolute'] = $config['result_base_absolute'].DIRECTORY_SEPARATOR.$config['log_file'];

	$config['server_base_credentials'] =
		substr_replace($config['server_base'],
			$config['username'].':'.$config['password'].'@',
			strlen(parse_url($config['server_base'], PHP_URL_SCHEME)) + 3, 0);
	$config['client_base_absolute'] = dirname(__FILE__).DIRECTORY_SEPARATOR.$config['client_base'].DIRECTORY_SEPARATOR;

//	log_message("Enter test.");
//	$schema=load_xml("./xml/magento_schema.xml");
	$schema_iterator = new SimpleXMLIterator('file://'.realpath($config['client_base_absolute'].$config['schema_file']),0,true);
	if(!$schema_iterator){log_message("Fail to load schema.");exit(0);}

	$mimetypes_xml = load_xml('file://'.realpath($config['client_base_absolute'].$config['mimetypes_file']));
	if(!$mimetypes_xml){log_message("Fail to load mimetypes.");exit(0);}
	$mimetypes_map = array();
	foreach($mimetypes_xml->Mimetype as $mimetype_item){
		$mimetypes_map[(string)$mimetype_item->Name] = (string)$mimetype_item->Extension;
	}

//	$products=load_products("./xml/pr.xml");
	$products=load_products($config['server_base_credentials'].$config['items_file']);
	if(!$products){log_message("Fail to load products.");exit(0);}

	if(!AttributesPerProduct::load_attribute_specifications_public($config['server_base_credentials'].$config['att_values_file'])){
		log_message("Fail to load attribite values."); exit(0);
	}

	$options = load_attributes($config['server_base_credentials'].$config['att_maps_file']);
	if(!$options){log_message("Fail to load attributes.");exit(0);}

	$vendors_xml = load_xml('file://'.realpath($config['client_base_absolute'].$config['vendors_file']));
	if(!$vendors_xml){log_message("Fail to load manufactururs.");exit(0);}
	$vendors_map = array();
	foreach($vendors_xml->Vendor as $vendor_item){
		$vendors_map[(int)$vendor_item->ManufacturerID] = (string)$vendor_item->CMName;
	}

	$category_map = load_category_map_xml('file://'.realpath($config['client_base_absolute'].$config['categories_file']));
	if(!$category_map){log_message("Fail to load categories map.");exit(0);}
	//print_r($category_map, false);

	$update_array = find_new_update($config,$products);
	if(!is_array($update_array)){log_message("DB error: " . $update_array);exit(0);}
	//echo "New items: ".count($update_array['new']).", update items: ".count($update_array['update'])."\n";
}

function main(){
	global $schema_iterator;
	global $products;
	global $options;
	global $vendors_map;
	global $config;
	global $update_array;

	$csv_string = "";
	$csv_string_update = "";
	
	//$csv_string = '"id",';//for debug
	//$csv_string_update = $csv_string;
	for($schema_iterator->rewind(); $schema_iterator->valid(); $schema_iterator->next()){
		$csv_string.= '"' . (string)$schema_iterator->current()->name . '",';
		if($schema_iterator->current()->update){
			$csv_string_update.= '"' . (string)$schema_iterator->current()->name . '",';		
		}
	}
	$csv_string = substr($csv_string,0,-1)."\n";
	$csv_string_update = substr($csv_string_update,0,-1)."\n";
	
	//print_r($update_array,false);
	foreach($products as $prod){
		$new_item = false;$update_item = false;
		if(in_array($prod['SKU'],$update_array['new'])){
			$new_item = true;
		}elseif(in_array($prod['SKU'],$update_array['update'])){
			$update_item = true;
		}

		if($new_item){
			$categories_info = get_categories_info($prod['CatID'],$prod);//echo"New item.\n";
		}elseif($update_item){
			$categories_info['Import'] = true;//echo "Update item.\n";
		}else{
			$categories_info['Import'] = false;//echo "False item.\n";
		}

		if($categories_info['Import'] /*&& (in_array(12,$prod['CatID']) || in_array(13,$prod['CatID']))*/){
			$csv_line='';
			//$csv_line='"'.$prod['id'].'",';//for debug
			for($schema_iterator->rewind(); $schema_iterator->valid(); $schema_iterator->next()){
				if($update_item && !isset($schema_iterator->current()->update)){continue;}
				$col_val = "";
				if((string)$schema_iterator->current()->name === "media_gallery"){
					foreach($prod['media'] as $pic_array){
						$col_val.= gen_pic_url($pic_array).';';
					}
				}elseif((string)$schema_iterator->current()->name === 'image' ||
					(string)$schema_iterator->current()->name === 'small_image' ||
					(string)$schema_iterator->current()->name === 'thumbnail'){
						$col_val.= gen_pic_url($prod['media'][0]);
				}elseif((string)$schema_iterator->current()->name === 'vendor'){
					if(array_key_exists((int)$prod['ManID'], $vendors_map)){
						$col_val.= $vendors_map[(int)$prod['ManID']];
					}else{
						log_message("Manufacturer missing in the map for id: ".(int)$prod['ManID'].".");
						log_message("For item ".$prod['id'].' '.$prod['Name'].' '.$prod['SKU'].' '.$prod['ShortDescr']);
						$col_val.="OTHER VENDORS";
					}
				}elseif((string)$schema_iterator->current()->name === 'category_ids'){//Switch between cm and tv categories:
					$col_val.=$categories_info['Categories'];//cm categories
					//foreach($prod['CatID'] as $tv_cat_id){$col_val.=$tv_cat_id.',';}$col_val = substr($col_val,0,-1);//tv categories
				}elseif((string)$schema_iterator->current()->name === 'attribute_set'){
					$col_val.=$categories_info['Attribute_set'];
				}elseif((string)$schema_iterator->current()->name === 'name'){
					$col_val.= $column_name = construct_name(array_merge($categories_info['Title_array'], explode(" ", $prod['Name'])));//echo $col_val."\n";//Console
				}elseif((string)$schema_iterator->current()->name === 'short_description'){
					$col_val.= $column_name . ' ' . $prod['ShortDescr'];
				/*}elseif((string)$schema_iterator->current()->name === 'country_of_manufacture'){
					if(isset($options[$prod['id']])){					
						foreach($options[$prod['id']]->getOptions() as $ind_opt => $opt){
							//print_r($opt, false);
							$col_val .= $ind_opt."  ".$opt."\n";
						}
						$col_val = substr($col_val,0,-1);				
					}	
				*/}else{
					$schema_col_iterator = $schema_iterator->getChildren();
					if(is_null($schema_col_iterator)){continue;}
					for($schema_col_iterator->rewind(); $schema_col_iterator->valid(); $schema_col_iterator->next()){
						if($schema_col_iterator->current()->getName()==='value') $col_val = (string)$schema_col_iterator->current();
						elseif($schema_col_iterator->current()->getName()==='mainfiletag') $col_val = $prod[(string)$schema_col_iterator->current()];
						elseif($schema_col_iterator->current()->getName()==='chars'){
							$col_val="";
							if(isset($options[$prod['id']])){					
								$schema_chars_iterator = $schema_col_iterator->getChildren();
								for($schema_chars_iterator->rewind(); $schema_chars_iterator->valid(); $schema_chars_iterator->next()){
									//$col_val.= (int)$schema_chars_iterator->current() . ',';
									if(array_key_exists((int)$schema_chars_iterator->current(), $options[$prod['id']]->getOptions())){$col_val.= $options[$prod['id']]->getOptions()[(int)$schema_chars_iterator->current()].',';}	
								}
							}
						$col_val = substr($col_val,0,-1);				
						}else{continue;}
					}
				}
				$csv_line.= '"'.str_replace('"' ,'""' , $col_val).'",';			
			}
			if($new_item){
				$csv_string.= substr($csv_line,0,-1)."\n";
			}else{//$update_item
				$csv_string_update.= substr($csv_line,0,-1)."\n";
			}
		}
	}
	file_put_contents ($config['new_items_file_absolute'], $csv_string);
	file_put_contents ($config['update_items_file_absolute'], $csv_string_update);

}

function gen_pic_url($pic_array){
	global $mimetypes_map;
	$pic_id_length = strlen($pic_array[0]);
	if($pic_id_length<7){
		$pic_name = substr("0000000",$pic_id_length) . $pic_array[0] . "_0";
	}else{
		$pic_name = $pic_array[0] . "_0";	
	}
	if(array_key_exists($pic_array[1], $mimetypes_map)){
		$extension = $mimetypes_map[$pic_array[1]]; 
	}else{
		log_message("Media extension missing in mimetypes map for: ".$pic_array[1].".");
		$extension = "jpg";//guess
	}
	return "http://tech-bg.com/images/" . $pic_name . '.' . $extension;
}

function get_categories_info($tv_categories,$prod){
	global $category_map;
	
	$cat_info = array();
	$cm_categories = array();
	$cm_attrib_set = array();
	$cm_import=false;
	$cm_name_array = array();
	foreach($tv_categories as $cat_id){
		if($cat_id==='12' || $cat_id=='13'){// Printers
			$add_info = get_printers_info_main($prod); 
			$add_categories = $add_info['Category'];
			$add_attribute_sets = $add_info['Attribute_set'];
			$add_import = $add_info['Import'];
			$add_name_array = array_merge($add_info['Title'], array("принтер"),$category_map[$cat_id]['Title']);
		}elseif(!array_key_exists ($cat_id, $category_map)){
			log_message("Category missinig in category map file: ".$cat_id.".");
			log_message("For item ".$prod['id'].' '.$prod['Name'].' '.$prod['SKU'].' '.$prod['ShortDescr']);

			$add_categories = array(203,266);
			$add_attribute_sets = array("Accessories");
			$add_import = true;
			$add_name_array = array("Артикул");
		}else{
			$add_categories = $category_map[$cat_id]['Map'];
			$add_attribute_sets = $category_map[$cat_id]['Attribute_set'];
			$add_import = $category_map[$cat_id]['Import'];
			$add_name_array = $category_map[$cat_id]['Title'];
		}
		$cm_categories = array_merge($cm_categories, $add_categories);
		$cm_attrib_set = array_merge($cm_attrib_set, $add_attribute_sets);
		$cm_import = $cm_import || $add_import;
		$cm_name_array = array_merge($cm_name_array, $add_name_array);
		
	}
	$cm_categories = array_unique($cm_categories, SORT_NUMERIC);
	$cm_attrib_set = array_unique($cm_attrib_set, SORT_STRING);
	$cm_name_array = array_unique($cm_name_array, SORT_STRING);
	
	$cat_info['Categories'] = "";
	if(empty($cm_categories)){
		$cat_info['Categories'].= "@- ";
		foreach($tv_categories as $cid){$cat_info['Categories'] .= $cid . ',';}	
	}else{
		foreach($cm_categories as $cid){$cat_info['Categories'] .= $cid . ',';}
	}
	$cat_info['Categories'] = substr($cat_info['Categories'], 0, -1);
	$cat_info['Import'] = $cm_import;
	$cat_info['Attribute_set'] = "";
	foreach($cm_attrib_set as $attrib_set){$cat_info['Attribute_set'] .= $attrib_set.',';}
	$cat_info['Attribute_set'] = substr($cat_info['Attribute_set'], 0, -1);
	$cat_info['Title_array'] = $cm_name_array;
	//foreach($cm_name_array as $name_item){$cat_info['Title'] .= $name_item . ' ';}
	//$cat_info['Title'] = substr($cat_info['Title'], 0, -1);//The space is needed.	 

	return $cat_info;					
}

function get_printers_info($full_data){

	//$full_data = $prod['Name'].' '.$prod['ShortDescr'].' '.$prod['FullDescription'];
	if(mb_stripos($full_data, 'баркод принте')!==FALSE
		/*|| mb_stripos($full_data, 'етикет')!==FALSE*/){
			$printer_info['Category'] = array(122,124,296);
			$printer_info['Attribute_set'] = array('Printers');
			$printer_info['Title'] = array('Етикетен');
	}elseif(stripos($full_data, 'mfp')!==FALSE ||
		mb_stripos($full_data, 'мфу')!==FALSE ||
		stripos($full_data, 'scan')!==FALSE ||
		stripos($full_data, 'scan/copy')!==FALSE ||
		mb_stripos($full_data, 'сканиране')!==FALSE ||
		stripos($full_data, 'multifunctional')!==FALSE ||
		mb_stripos($full_data, 'мултифункционал')!==FALSE){
			$printer_info['Category'] = array(122,124,252);
			$printer_info['Attribute_set'] = array('Printer (multifunction)');
			$printer_info['Title'] = array('Мултифункционален');
	}elseif((stripos($full_data, 'matrix')!==FALSE && stripos($full_data, 'dot matrix font')===FALSE)||
		mb_stripos($full_data, 'матричен')!==FALSE ||
		stripos($full_data, '-pin')!==FALSE ||
		mb_stripos($full_data, '-пинов')!==FALSE ||
		mb_stripos($full_data, 'Брой пинове')!==FALSE ||
		mb_stripos($full_data, 'Баркод шрифтове')){
			$printer_info['Category'] = array(122,124,296);
			$printer_info['Attribute_set'] = array('Printer Matrix');
			$printer_info['Title'] = array('Матричен');

//	}else{	
//		$printer_info['Category'] = array(122,124,250);//laser
//		$printer_info['Attribute_set'] = array('Printers');
	}

	$printer_info['Import'] = true;

	return $printer_info;

}

function get_printers_info_main($prod){
	$printer_info = get_printers_info($prod['Name']);
	if(!isset($printer_info['Category'])){
		$printer_info = get_printers_info($prod['ShortDescr']);
		if(!isset($printer_info['Category'])){
			$printer_info = get_printers_info(filter_var($prod['FullDescription'], FILTER_SANITIZE_STRING));
			if(!isset($printer_info['Category'])){
				$printer_info['Category'] = array(122,124,250);//laser
				$printer_info['Attribute_set'] = array('Printers');
				$printer_info['Title'] = array('Лазарен');

			}
		}
	}
	return $printer_info;	
}

function construct_name($title_array){
	//print_r($title_array,false);
	$title = "";
	foreach($title_array as $title_item){
		$title_item = trim($title_item);
		if(!empty($title_item) && mb_stripos($title, $title_item)===FALSE){
			$title .= $title_item . " ";
		}
	}
	return $title;
}

function log_message($mes){
	global $config;
	file_put_contents($config['log_file_absolute'], date("Y-m-d | h:i:sa ").$mes."\n", FILE_APPEND);
}

init();
main();
exit(0);
?>
