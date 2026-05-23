<?php

$connection =
    mysqli_connect(
        "localhost",
        "root",
        "",
        "attendance_system"
    );



if (!$connection) {

    die(
        "Database connection failed."
    );

}

?>