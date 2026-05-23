<?php

session_start();

include 'db_connect.php';



if (

    $_SERVER["REQUEST_METHOD"] == "POST"

) {

    $adminId =

    mysqli_real_escape_string(
        $connection,
        $_POST['adminId']
    );

    $password =

    mysqli_real_escape_string(
        $connection,
        $_POST['password']
    );



    $query =

    "
    SELECT *
    FROM admin_login
    WHERE admin_id = '$adminId'
    AND password = '$password'
    ";



    $result =

    mysqli_query(
        $connection,
        $query
    );



    if (

        mysqli_num_rows($result) > 0

    ) {

        $_SESSION['admin'] =
            $adminId;

        header(
            "Location: admin.php"
        );

        exit();

    }

    else {

        header(
            "Location: index.html?error=1"
        );

        exit();

    }

}

?>