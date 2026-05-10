<?php
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
$conn->query("UPDATE stalls SET approval_status = 'Approved'");
echo "All existing stalls set to Approved.";
?>
