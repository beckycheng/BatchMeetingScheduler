<?php
date_default_timezone_set("Asia/Hong_Kong");
include $_SERVER["DOCUMENT_ROOT"] . "/conn/conn.php";

$stmt = $conn->prepare("SELECT * FROM `result` WHERE `uuid` = ? ");


$stmt->bind_param("s" , $_GET['uuid'] );

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows==1){

    while ($row = $result->fetch_assoc()) {


        $timeslotsarray=json_decode($row['result']) ;
        $stmt->free_result();
        $stmt->close();

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


            }

        }

        $stmt->free_result();
        $stmt->close();
    }


}else{



    $stmt = $conn->prepare("SELECT * FROM `meeting` WHERE `uuid` = ? ");


    $stmt->bind_param("s" , $_GET['uuid'] );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows==1){

        while ($row = $result->fetch_assoc()) {

            $mt_deadline = $row['deadline'];
            $mt_timeslots=json_decode($row['timeslots'], true);
            $mt_studentid=json_decode($row['studentid'], true);

            $timeslotsnum=count($mt_timeslots)>10? 10 :count($mt_timeslots);

            $stmt->free_result();
            $stmt->close();

            if (time()>strtotime($mt_deadline)){

                $studentidarray=[];
                $timeslotsarray=[];

                $stmt = $conn->prepare("SELECT * FROM `choose` WHERE `uuid` = ? ");

                $stmt->bind_param("s" , $_GET['uuid'] );

                $stmt->execute();

                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {

                    array_push($studentidarray,$row['studentid']);

                }

                $studentidarray_random= $studentidarray;
                shuffle($studentidarray_random);

                foreach ($mt_timeslots as $value) {
                    $timeslotsarray[$value]=0;
                }

                $stmt->free_result();
                $stmt->close();


                for ($x = 1; $x <= $timeslotsnum && count($studentidarray_random)>0 ; $x++) {



                    foreach ($studentidarray_random as $y => $value){

                        $sql = "SELECT * FROM `choose` WHERE `uuid` = \"{$_GET['uuid']}\"  AND `studentid`= \"$value\"  ";

                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()){
                            if ($timeslotsarray[$row['choose'.$x]]==0){
                                $timeslotsarray[$row['choose'.$x]]=$value;
                                unset($studentidarray_random[$y]);
                            }
                        }



                    }

                }


                $remainstudent=array_diff($mt_studentid,$studentidarray);

                $remainstudent=array_merge($remainstudent,$studentidarray_random);

                shuffle($remainstudent);


                foreach ($remainstudent as $value){

                    foreach ($timeslotsarray as $y => $value2){
                        if ($value2==0){
                            $timeslotsarray[$y]=$value;
                            break;
                        }
                    }
                }


                $timeslotsarray=json_encode($timeslotsarray) ;




                $sql = "INSERT INTO `result` (`id`, `uuid`, `result`) VALUES (NULL, '{$_GET['uuid']}', '{$timeslotsarray}')";

                $conn->query($sql);

                $timeslotsarray=json_decode($timeslotsarray) ;


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


                    }

                }

                $stmt->free_result();
                $stmt->close();

            }else{
                header('Location: index.html');
            }
        }

    }else{
        $stmt->free_result();
        $stmt->close();
        header('Location: index.html');
    }




}


if (isset($timeslotsarray)){

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



                    <h1 class="mb-4 text-poly">Time slot allocation results</h1>

                    <?php
                    if (isset($_GET['edit'])){

                        ?>

                        <div class="alert alert-success" role="alert">
                            Edit result successfully!<br>

                        </div>

                        <?php
                    }

                    ?>


                    <h4>Meeting title: <small class="text-secondary"> <?php echo $mt_title ?></small></h4>
                    <h4>Subject title: <small class="text-secondary"><?php echo $mt_subject ?></small></h4>
                    <h4>Teacher name: <small class="text-secondary"><?php echo $mt_teacher ?></small></h4>
                    <h4>Duration of each meeting (minutes): <small class="text-secondary"><?php echo $mt_duration ?></small></h4>
                    <h4>Deadline time: <small class="text-secondary"> <?php echo $mt_deadline ?> </small></h4>
                    <h4>Meeting code: <small class="text-secondary"> <?php echo $_GET['uuid'] ?> </small></h4>



                    <table class="table mt-5">
                        <thead>
                        <tr>
                            <th scope="col">Time slot</th>
                            <th scope="col">Student id</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php

                        foreach ($timeslotsarray as $x  =>  $value)
                            echo "<tr><td>{$x}</td><td>{$value}</td></tr>"

                        ?>


                        </tbody>
                    </table>


                </div>
            </div>
        </main>

    </div>




    </body>
    </html>

<?php


}
