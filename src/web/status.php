<?php
date_default_timezone_set("Asia/Hong_Kong");
include $_SERVER["DOCUMENT_ROOT"] . "/conn/conn.php";



$stmt = $conn->prepare("SELECT * FROM `meeting` WHERE `uuid` = ? ");


$stmt->bind_param("s" , $_GET['uuid'] );

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows==1){

    while ($row = $result->fetch_assoc()) {

        $mt_title = $row['title'];
        $mt_subject = $row['subject'];
        $mt_teacher = $row['teacher'];
        $mt_duration = $row['duration'];
        $mt_deadline = $row['deadline'];
        $mt_studentid = $row['studentid'];
        $studentnum=count(json_decode($mt_studentid));


    }

    $stmt->free_result();
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM `choose` WHERE `uuid` = ? ");


    $stmt->bind_param("s" , $_GET['uuid'] );

    $stmt->execute();

    $result = $stmt->get_result();

    $choosestudent=$result->num_rows;

    $stmt->free_result();
    $stmt->close();


}else{
    header('Location: index.html');
}




?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="icon" href="images/favicon.ico" />
    <title>PolyU reservation system</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css" >
    <link rel="stylesheet" href="styles/main.css" >
</head>
<body class="bg-poly d-flex align-items-center h-100">

<div class="container">

    <main class="w-100 m-auto" id="main"  >
        <div class="card py-md-5 py-2 px-sm-2 px-md-5   my-5 w-100"  >
            <div class="card-body" >

                <?php
                if (isset($_GET['success'])){

                ?>

                <div class="alert alert-success" role="alert">
                    Meeting created successfully!<br>
                    Share the follow code to your student<br>
                    Meeting code :<span class="text-danger"> <?php echo $_GET['uuid'] ?> </span><br>
                    Edit password : <span class="text-danger"> <?php echo $_GET['password'] ?> (Please record it for future result editing) </span>
                </div>

                <?php
                }

                ?>

                <h1 class="mb-4 text-poly">Meeting state</h1>


                <h4>Meeting title: <small class="text-secondary"> <?php echo $mt_title ?></small></h4>
                <h4>Subject title: <small class="text-secondary"><?php echo $mt_subject ?></small></h4>
                <h4>Teacher name: <small class="text-secondary"><?php echo $mt_teacher ?></small></h4>
                <h4>Duration of each meeting (minutes): <small class="text-secondary"><?php echo $mt_duration ?></small></h4>
                <h4>Deadline time: <small class="text-secondary"> <?php echo $mt_deadline ?> </small></h4>
                <h4>Meeting code: <small class="text-secondary"> <?php echo $_GET['uuid'] ?> </small></h4>



                <div class="card bg-light mx-1 mt-5">
                    <div class="card-body">


                        <h5>Student who have made a choice</h5>


                        <h1 class="display-1 fw-bold"><?php echo $choosestudent?>/<?php echo $studentnum?></h1>





                    </div>
                </div>






            </div>
        </div>
    </main>

</div>




</body>
</html>