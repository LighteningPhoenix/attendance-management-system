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

    $_POST['password'];



    $query =

    "
    SELECT *
    FROM admin_login
    WHERE admin_id = '$adminId'
    ";



    $result =

    mysqli_query(
        $connection,
        $query
    );



    if (

        mysqli_num_rows($result) > 0

    ) {

        $admin =

        mysqli_fetch_assoc(
            $result
        );



        if (

            password_verify(
                $password,
                $admin['password']
            )

        ) {

            $_SESSION['admin'] =
            $adminId;

            header(
                "Location: ../admin.php"
            );

            exit();

        }

    }



    $_SESSION['login_error'] =

"Invalid Admin ID or Password.";

header(
    "Location: ../index.php"
);

exit();

}

?>

