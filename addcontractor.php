<?php

session_start();



// AUTO LOGOUT AFTER 1 HOUR

$timeout_duration = 3600;



if (

    isset($_SESSION['LAST_ACTIVITY'])

    &&

    (
        time() -
        $_SESSION['LAST_ACTIVITY']
    ) > $timeout_duration

) {

    session_unset();

    session_destroy();

    header(
        "Location: index.html?admin=true"
    );

    exit();

}



// UPDATE LAST ACTIVITY TIME

$_SESSION['LAST_ACTIVITY'] = time();



// CHECK ADMIN LOGIN

if (

    !isset($_SESSION['admin'])

) {

    header(
        "Location: index.html?admin=true"
    );

    exit();

}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $contractor_name =

        mysqli_real_escape_string(
            $connection,
            $_POST["contractor_name"]
        );



    $supply_order_number =

        mysqli_real_escape_string(
            $connection,
            $_POST["supply_order_number"]
        );



    $from_date =

        mysqli_real_escape_string(
            $connection,
            $_POST["from_date"]
        );



    $to_date =

        mysqli_real_escape_string(
            $connection,
            $_POST["to_date"]
        );



    $category =

        $_POST["category"];

    $employee_name =

        $_POST["employee_name"];

    $aadhaar_number =

        $_POST["aadhaar_number"];

    $phone_number =

        $_POST["phone_number"];

    $dob =

    $_POST["dob"];



$address =

    $_POST["address"];


    for ($i = 0; $i < count($employee_name); $i++) {

        $cat = mysqli_real_escape_string(
            $connection,
            $category[$i]
        );



        $emp_name = mysqli_real_escape_string(
            $connection,
            $employee_name[$i]
        );



        $aadhaar = mysqli_real_escape_string(
            $connection,
            $aadhaar_number[$i]
        );



        $phone = mysqli_real_escape_string(
            $connection,
            $phone_number[$i]
        );

        $emp_dob = mysqli_real_escape_string(
    $connection,
    $dob[$i]
);



$emp_address = mysqli_real_escape_string(
    $connection,
    $address[$i]
);

        // AUTO GENERATE EMPLOYEE ID

        $emp_id = substr($aadhaar, -6);



        // CHECK DUPLICATE AADHAAR

        $checkDuplicateAadhaar =

            "SELECT *
             FROM master
             WHERE aadhaar_number = '$aadhaar'";



        $aadhaarResult =

            mysqli_query(
                $connection,
                $checkDuplicateAadhaar
            );



        if (

            mysqli_num_rows(
                $aadhaarResult
            ) > 0

        ) {

            header(
                "Location: addcontractor.php?duplicate_aadhaar=1"
            );

            exit();

        }



        // CHECK DUPLICATE EMPLOYEE ID

        $checkDuplicateEmpID =

            "SELECT *
             FROM master
             WHERE employee_id = '$emp_id'";



        $empIDResult =

            mysqli_query(
                $connection,
                $checkDuplicateEmpID
            );



        if (

            mysqli_num_rows(
                $empIDResult
            ) > 0

        ) {

            header(
                "Location: addcontractor.php?duplicate_empid=1"
            );

            exit();

        }



        $sql =

            "INSERT INTO master
(
    contractor_name,
    category,
    employee_id,
    employee_name,
    aadhaar_number,
    phone_number,
    dob,
    address
)

            VALUES
            (
                '$contractor_name',
                '$cat',
                '$emp_id',
                '$emp_name',
                '$aadhaar',
                '$phone',
                '$emp_dob',
                '$emp_address'
            )";



        mysqli_query(
            $connection,
            $sql
        );

    }



    // INSERT SUPPLY OVERHEAD DATA

    $supplyInsert =

        "INSERT IGNORE INTO supply_overhead
        (
            contractor_name,
            supply_order_number,
            from_date,
            to_date
        )

        VALUES
        (
            '$contractor_name',
            '$supply_order_number',
            '$from_date',
            '$to_date'
        )";



    mysqli_query(
        $connection,
        $supplyInsert
    );



    header(
        "Location: addcontractor.php?success=1"
    );

    exit();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Add Contractor
</title>

<link rel="stylesheet"
      href="style.css">

<style>

.remove-row-btn {

    width: 52px;

    height: 52px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: auto;

    padding: 0;

    border: none;

    border-radius: 14px;

    cursor: pointer;

    background:
    linear-gradient(
        135deg,
        #64748b,
        #475569
    );

    color: white;

    font-size: 24px;

    font-weight: bold;

    line-height: 1;

    transition: 0.25s ease;

    box-shadow:
    0 6px 14px rgba(71,85,105,0.25);

}



.remove-row-btn:hover {

    transform:
    translateY(-2px)
    scale(1.04);

    background:
    linear-gradient(
        135deg,
        #475569,
        #334155
    );

    box-shadow:
    0 10px 20px rgba(71,85,105,0.35);

}

.disabled-btn {

    opacity: 0.5;
    cursor: not-allowed;

}

textarea {

    width: 100%;
    resize: vertical;
    min-height: 60px;
    font-family: inherit;
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    box-sizing: border-box;

}
</style>

</head>

<body>

<header class="main-header">

<h1>
ORDNANCE FACTORY BADMAL
</h1>

<h2>
Attendance Management System
</h2>

</header>

<main class="main-container">

<section class="admin-dashboard">

<h2>
Add Contractor
</h2>



<?php

if (isset($_GET["success"])) {

    echo "

    <p style='
        color: green;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 25px;
        text-align: center;
    '>

        Contractor Added Successfully!

    </p>

    ";

}



if (isset($_GET["duplicate_aadhaar"])) {

    echo "

    <p style='
        color: red;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 25px;
        text-align: center;
    '>

        Duplicate Aadhaar Number Found!

    </p>

    ";

}



if (isset($_GET["duplicate_empid"])) {

    echo "

    <p style='
        color: red;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 25px;
        text-align: center;
    '>

        Duplicate Employee ID Found!

    </p>

    ";

}

?>



<form method="POST"
      id="contractorForm">

<div>

<label>
Contractor Name
</label>

<input type="text"
       name="contractor_name"
       required>

</div>



<br><br>



<div>

<label>
Supply Order Number
</label>

<input type="text"
       name="supply_order_number"
       required>

</div>



<br><br>



<div>

<label>
From Date
</label>

<input type="date"
       name="from_date"
       required>

</div>



<br><br>



<div>

<label>
To Date
</label>

<input type="date"
       name="to_date"
       required>

</div>



<br>



<table id="employeeTable"
       border="1"
       cellpadding="10"
       cellspacing="0"
       width="100%">

<tr>

<th>
Sl. No.
</th>

<th>
Category
</th>

<th>
Employee Name
</th>

<th>
Aadhaar Number
</th>

<th>
Phone Number
</th>

<th>
DOB
</th>

<th>
Address
</th>

<th>
Action
</th>

</tr>



<tr>

<td>
1
</td>

<td>

<input type="text"
       name="category[]"
       required>

</td>



<td>

<input type="text"
       name="employee_name[]"
       required>

</td>



<td>

<input type="text"
       name="aadhaar_number[]"
       pattern="[0-9]{12}"
       minlength="12"
       maxlength="12"
       required>

</td>



<td>

<input type="text"
       name="phone_number[]"
       pattern="[0-9]{10}"
       minlength="10"
       maxlength="10"
       required>

</td>
<td>

<input type="date"
       name="dob[]"
       max="<?php echo date('Y-m-d'); ?>"
       required>

</td>

<td>

<textarea name="address[]"
          rows="2"
          required></textarea>

</td>


<td>

<button type="button"
        class="remove-row-btn">

✖

</button>

</td>

</tr>

</table>



<br>



<button type="button"
        id="addRowButton">

Add Employee

</button>



<button type="submit"
        id="submitButton"
        class="disabled-btn"
        disabled>

Submit

</button>

</form>

<br>

<button type="button"
        onclick="window.location.href='admin.php'">

    Back

</button>

</section>

</main>

<script>

const addRowButton =

document.getElementById(
    "addRowButton"
);



const employeeTable =

document.getElementById(
    "employeeTable"
);



const contractorForm =

document.getElementById(
    "contractorForm"
);



const submitButton =

document.getElementById(
    "submitButton"
);



// ADD ROW

addRowButton.addEventListener(

    "click",

    function () {

        const rowCount =
        employeeTable.rows.length;



        const row =
        employeeTable.insertRow(-1);



        row.innerHTML = `

        <td>
            ${rowCount}
        </td>

        <td>

           <input type="text"
       name="category[]"
       required>

        </td>

        <td>

            <input type="text"
                   name="employee_name[]"
                   required>

        </td>

        <td>

            <input type="text"
                   name="aadhaar_number[]"
                   pattern="[0-9]{12}"
                   minlength="12"
                   maxlength="12"
                   required>

        </td>

        <td>

            <input type="text"
                   name="phone_number[]"
                   pattern="[0-9]{10}"
                   minlength="10"
                   maxlength="10"
                   required>

        </td>

        <td>

    <input type="date"
           name="dob[]"
           required>

</td>

<td>

    <textarea name="address[]"
              rows="2"
              required></textarea>

</td>
        <td>

            <button type="button"
                    class="remove-row-btn">

                ✖

            </button>

        </td>

        `;

        attachRemoveEvents();

        validateForm();

    }

);



// REMOVE ROW

function attachRemoveEvents() {

    const removeButtons =

    document.querySelectorAll(
        ".remove-row-btn"
    );



    removeButtons.forEach(

        function (button) {

            button.onclick = function () {

                if (
                    employeeTable.rows.length > 2
                ) {

                    this.parentElement.parentElement.remove();

                    updateSerialNumbers();

                    validateForm();

                }

            };

        }

    );

}

attachRemoveEvents();



// UPDATE SERIAL NUMBERS

function updateSerialNumbers() {

    for (

        let i = 1;

        i < employeeTable.rows.length;

        i++

    ) {

        employeeTable.rows[i].cells[0].innerHTML =
        i;

    }

}



// VALIDATE FORM

function validateForm() {

    const inputs =

    contractorForm.querySelectorAll(
       "input, select, textarea"
    );



    let allValid = true;



    inputs.forEach(

        function (input) {

            if (
                !input.checkValidity()
            ) {

                allValid = false;

            }

        }

    );



    if (allValid) {

        submitButton.disabled = false;

        submitButton.classList.remove(
            "disabled-btn"
        );

    }

    else {

        submitButton.disabled = true;

        submitButton.classList.add(
            "disabled-btn"
        );

    }

}



// LIVE VALIDATION

contractorForm.addEventListener(

    "input",

    validateForm

);

</script>

</body>

</html>