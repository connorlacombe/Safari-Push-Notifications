<?php
function mysqli_do($q) {
	$c = mysqli_connect("SERVER", "USER", 'PASS') or die(mysqli_error($c));
	mysqli_select_db($c, "DB") or die(mysqli_error($c));
	$r = mysqli_query($c, $q) or die(mysqli_error($c));
	mysqli_close($c);
	return $r;
}
?>