<?php
date_default_timezone_set("Asia/Hong_Kong");
include $_SERVER["DOCUMENT_ROOT"] . "/conn/conn.php";

$stmt = $conn->prepare("SELECT * FROM `meeting` WHERE `uuid` = ? ");


$stmt->bind_param("s" , $_POST['code'] );

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows==1){

    while ($row = $result->fetch_assoc()) {

        $mt_studentid = json_decode($row['studentid'], true);




        if(in_array($_POST['studentid'],$mt_studentid)){

            $mt_title = $row['title'];
            $mt_subject = $row['subject'];
            $mt_teacher = $row['teacher'];
            $mt_duration = $row['duration'];
            $mt_deadline = $row['deadline'];
            $mt_timeslots = json_decode($row['timeslots'], true);

        }else{
            ?>
            <script>
                alert("worng student id");
                window.location.href = 'index.html';
            </script>
            <?php
        }

        $stmt->free_result();
        $stmt->close();


        $stmt = $conn->prepare("SELECT * FROM `choose` WHERE `uuid` = ? AND `studentid` = ?;");


        $stmt->bind_param("ss" , $_POST['code'] ,$_POST['studentid'] );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows==1){

            while ($row = $result->fetch_assoc()) {

                $studentchoose=$row;


            }



        }else{
            header('Location: index.html');
        }

    }



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


                <h1 class="mb-4 text-poly">The following is your choose</h1>

                <div class="alert alert-danger" role="alert">
                    If you want to update your choose, click this <a href="studentinput.html">link</a>
                </div>


                <h4>Meeting title: <small class="text-secondary"> <?php echo $mt_title ?></small></h4>
                <h4>Subject title: <small class="text-secondary"><?php echo $mt_subject ?></small></h4>
                <h4>Teacher name: <small class="text-secondary"><?php echo $mt_teacher ?></small></h4>
                <h4>Duration of each meeting (minutes): <small class="text-secondary"><?php echo $mt_duration ?></small></h4>
                <h4>Deadline time: <small class="text-secondary"> <?php echo $mt_deadline ?> </small></h4>
                <h4>Meeting code: <small class="text-secondary"> <?php echo $_POST['code'] ?> </small></h4>


                <form  action="" method="post" enctype="multipart/form-data">

                    <input type="hidden"  name="studentid" value="<?php echo $_POST['studentid'] ?>">
                    <input type="hidden"  name="uuid" value="<?php echo $_POST['code'] ?>">

                    <div class="mb-3 mt-5">
                        <label for="code" class="form-label">First choose</label>
                        <select class="form-select" name="choose1" required>


                            <?php


                                echo "<option disabled selected hidden>{$studentchoose['choose1']}</option>";


                            ?>

                        </select>

                    </div>

                    <?php

                    $choose_num=count($mt_timeslots)>10? 10 :count($mt_timeslots);

                    $choose_words=["First","Second","Third","Fourth","Fifth","Sixth","Seventh","Eighth","Ninth","Tenth"];

                    for ($x = 2; $x <= $choose_num; $x++) {

                        ?>

                        <div class="mb-3">
                            <label for="code" class="form-label"><?php echo $choose_words[$x-1] ?> choose</label>
                            <select class="form-select" name="choose<?php echo $x ?>" required>


                                <?php


                                    echo "<option disabled selected hidden >".$studentchoose['choose'.$x]."</option>";


                                ?>

                            </select>

                        </div>


                        <?php

                    }




                    ?>






                </form>



            </div>
        </div>
    </main>

</div>




</body>
</html>