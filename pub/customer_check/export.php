<?php
// Database Connection
require("db_connection.php");

// get Users
$query = "select entity_id,firstname ,lastname,email from customer_entity where password_hash is null";
if (!$result = mysqli_query($con, $query)) {
    exit(mysqli_error($con));
}

$users = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=users_password_reset.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email'));

if (count($users) > 0) {
    foreach ($users as $row) {
        fputcsv($output, $row);
    }
}
?>
