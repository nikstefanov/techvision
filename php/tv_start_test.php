<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 'on');
?>
<!DOCTYPE html>
<html>
<title>Start Techvision script</title>
<body>
<?php
	if($_GET['q']==1){
		echo "<pre>\n";
		require_once('./import/techvision/php/main.php');
		echo "</pre>\n";
		echo '<div>Finished.</div>';
	}elseif($_GET['q']==2){
		echo "<pre>\n";
		require_once('./import/techvision/php/db_test.php');
		echo "</pre>\n";
		echo '<div>Finished.</div>';
	}elseif($_GET['q']==3){
		echo "<pre>\n";
		require_once('./import/techvision/php/find_new_update.php');
		echo "</pre>\n";
		echo '<div>Finished.</div>';
	}else{
?>
<form method='get' target='_self' action='tv_start_test.php'>
	<input type='hidden' name='q' value='1'/>
	<button type="submit">Start tv script</button>
</form>
<br/>
<form method='get' target='_self' action='tv_start_test.php'>
	<input type='hidden' name='q' value='2'/>
	<button type="submit">Execute sql query</button>
</form>
<br/>
<form method='get' target='_self' action='tv_start_test.php'>
	<input type='hidden' name='q' value='3'/>
	<button type="submit">Test new &amp; update function</button>
</form>
<?php	}?>
</body>
</html>
