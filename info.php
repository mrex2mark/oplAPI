<h1>Info.php</h1>

<?php
		echo "Computer Name: " . getenv( "COMPUTERNAME" ) . "<p>\n";
        phpinfo();
?>