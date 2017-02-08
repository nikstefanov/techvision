<?php
	$servername = "192.168.0.3";
	$username = "computermarket.bg";
	$password = "w92e7d7cX2I80}d";
	$dbname = "test_computermarket_bg";
	$port = 3306;

	//$query ="SELECT pe.sku FROM catalog_product_entity pe LIMIT 10;";
	//$query ="SELECT * FROM _cm_sku_utility;\n";

	/*
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
	*/
	/*
	$query = "SELECT s.sku \n";
	$query.= "FROM _cm_sku_utility as s \n";
	$query.= "LEFT JOIN catalog_product_entity as pe \n";
	$query.= "ON s.sku = pe.sku \n";
	$query.= "WHERE pe.sku IS NULL;\n";
	*/
	/*
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
	$query.= "	OR (pei.value=2680) \n";
	$query.= "); \n";
	*/


	// Create connection
	$conn = new mysqli($servername, $username, $password,$dbname,$port);

	// Check connection
	if ($conn->connect_error) {
	    echo("<div>Connection failed: " . $conn->connect_error."</div>");
	    die("Connection failed: " . $conn->connect_error);
	}

	echo "Connected successfully.\n";
	echo $query."\n";

	$result = $conn->query($query);

	if(!$result){
		echo "Error: ".$conn->error."\n";
	}elseif ($result->num_rows > 0){
    	// output data of each row
    		while($row = $result->fetch_assoc()){
			print_r($row,false);echo "\n";
    		}
	} else {
    		echo "0 results.\n";
	}
	$conn->close();
?>
