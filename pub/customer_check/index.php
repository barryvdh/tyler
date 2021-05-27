<?php
// Database Connection
require("db_connection.php");

// List Users
$query = "select entity_id,firstname ,lastname,email from customer_entity where password_hash is null;";
if (!$result = mysqli_query($con, $query)) {
    exit(mysqli_error($con));
}

if (mysqli_num_rows($result) > 0) {
    $number = 1;
    $users = '<table class="table table-bordered">
        <tr>
            <th>No.</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
        </tr>
    ';
    while ($row = mysqli_fetch_assoc($result)) {
        $users .= '<tr>
            <td>'.$number.'</td>
            <td>'.$row['firstname'].'</td>
            <td>'.$row['lastname'].'</td>
            <td>'.$row['email'].'</td>
        </tr>';
        $number++;
    }
    $users .= '</table>';
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User list to reset password</title>
    <!-- Bootstrap CSS File  -->
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
</head>
<body>
<div class="container">
    <!--  Header  -->
    <div class="row">
        <div class="col-md-12">
            <h2>User list to reset password</h2>
	    <h2>Please click the below link to export the users <h1><a href="http://one2oneretail2.com/pub/customer_check/export.php">download</a></H1></h2> 		
</div>
    </div>
    <!--  /Header  -->

    <!--  Content   -->
    <div class="form-group">
        <?php echo $users ?>
    </div>
    <div class="form-group">
        <button >
