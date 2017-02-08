<?php

/**
* http://stackoverflow.com/a/4147704
*/
function isAscii($str) {
    return preg_match('/^([\x00-\x7F])+$/', $str);
}


function get_query_string_new(){
	$query = "SELECT s.sku \n";
	$query.= "FROM _cm_sku_utility as s \n";
	$query.= "LEFT JOIN catalog_product_entity as pe \n";
	$query.= "ON s.sku = pe.sku \n";
	$query.= "WHERE pe.sku IS NULL;\n";
	return $query;
}

function get_query_string_update(){
	$query = "SELECT s.sku \n";
	$query.= "FROM _cm_sku_utility as s \n";
	$query.= "LEFT JOIN catalog_product_entity pe \n";
	$query.= "ON s.sku = pe.sku \n";
	$query.= "LEFT JOIN `catalog_product_index_price` pp \n";
	$query.= "ON pp.`entity_id` = pe.`entity_id` \n";
	$query.= "AND pp.`customer_group_id` = '1' \n";
	$query.= "LEFT JOIN catalog_product_entity_int pei  \n";
	$query.= "ON pei.`entity_id` = pe.`entity_id` \n";
	$query.= "WHERE pei.`attribute_id` = '219' \n";
	$query.= "AND pei.value != 226 \n";
	$query.= "AND ((pp.price > s.price AND pei.value !=2680) \n";
	$query.= "	OR (pei.value=2680));\n";
	return $query;
}

function get_query_string_truncate(){
	$query = "TRUNCATE _cm_sku_utility; \n";
	return $query;
}

function get_query_string_insert($products){
	$pr_array = array();
	foreach($products as $prod){
		if(isAscii($prod['SKU'])){
			$skip = false;
			foreach($pr_array as $pr){
				if($pr['sku']===$prod['SKU']){
					$skip = true;
					$pr['price']='-100000';
					//echo $prod['SKU']."\n";
					break;
				}
			}
			if(!$skip){
				$pr_array[] = array('sku' => $prod['SKU'], 'price' => $prod['EndPrice']);
			}
		}
	}

	$query = "INSERT INTO _cm_sku_utility (sku,price) VALUES \n";
	foreach($pr_array as $pr){
		if($pr['price']!=='-100000'){
			$query.= "('".$pr['sku']."','".$pr['price']."'),\n";
		}
	}
	$query = substr($query,0,-2);
	$query.=";";
	//print_r($query,false);
	//echo "products: " . count($products) . "\n";
	//echo "pr_array: " . count($pr_array) . "\n";
	return $query;
}

function find_new_update($config, $products){
	$conn = new mysqli($config['db_server'], $config['db_username'], $config['db_password'],$config['db_name'],$config['db_port']);
	if($conn->connect_error){
	    return "Connection failed: " . $conn->connect_error;
	}

	if($conn->query(get_query_string_truncate()) === FALSE){
    		return "Error truncating utility table: " . $conn->error;
	}

	if($conn->query(get_query_string_insert($products)) === FALSE){
    		return "Error filling utility table: " . $conn->error;
	}
	
	$update_array = array(/*'new'=>array(), 'update'=>array()*/);

	if(($result = $conn->query(get_query_string_new())) === FALSE){
    		return "Error selecting new items: " . $conn->error;
	}else{
		foreach($result->fetch_all(MYSQLI_NUM) as $row){
			$update_array['new'][] = $row[0];
		}
	}

	if(($result = $conn->query(get_query_string_update())) === FALSE){
    		return "Error selecting update items: " . $conn->error;
	}else{
		foreach($result->fetch_all(MYSQLI_NUM) as $row){
			$update_array['update'][] = $row[0];
		}
	}
 
	$conn->close();

	return $update_array;

}
/*
function get_query_string_insert_test($products=null){
	$query = "INSERT INTO _cm_sku_utility VALUES \n";
	$query.= "('1rer---erwe','66.0'),\n";
	$query.= "('26yter---erwe','4.0'),\n";
	$query.= "('3Tyter-erwe','41.1'),\n";

	$query.= "('80QQ0055IT','690'),\n";//CM our - exact
	$query.= "('80QQ0059IT','795'),\n";//CM our - lower
	$query.= "('80QQ00D6IT','41.1'),\n";//CM our - higher

	$query.= "('DE-14332','55.9'),\n";//Delphi our - exact
	$query.= "('DE-17046','50.9'),\n";//Delphi our - lower
	$query.= "('DE-87023','160.9'),\n";//Delphi our - higher

	$query.= "('TV-TEST-1-ITEM-345h4uf','100'),\n";//TV our - exact
	$query.= "('TV-TEST-2-ITEM-t4jv4p44','140'),\n";//TV our - lower
	$query.= "('TV-TEST-3-ITEM-m54n39c83hc','241.1');\n";//TV our - higher
	
	return $query;	
}

//test
$credentials['db_server'] = "192.168.0.3";
$credentials['db_username'] = "computermarket.bg";
$credentials['db_password'] = "w92e7d7cX2I80}d";
$credentials['db_name'] = "test_computermarket_bg";
$credentials['db_port'] = 3306;
print_r(find_new_update($credentials,null),false);
*/
?>
