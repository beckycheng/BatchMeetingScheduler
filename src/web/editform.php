<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Asia/Hong_Kong");
include $_SERVER["DOCUMENT_ROOT"] . "/conn/conn.php";

$stmt = $conn->prepare("SELECT * FROM `result` WHERE `uuid` = ? ");


$stmt->bind_param("s" , $_POST['uuid'] );

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows==1){



    while ($row = $result->fetch_assoc()) {

        $stmt->free_result();
        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM `meeting` WHERE `uuid` = ? AND `password` = ? ");

        $stmt->bind_param("ss" , $_POST['uuid'], $_POST['password'] );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows==1){

            while ($row = $result->fetch_assoc()) {

                $mt_timeslots=json_decode($row['timeslots'], true);

            }



            $stmt->free_result();
            $stmt->close();



            foreach ($mt_timeslots as $value){
                $timeslotsarray[$value]=$_POST[$value];
            }

            $timeslotsarray=json_encode($timeslotsarray) ;



            $stmt = $conn->prepare("UPDATE `result` SET `result` = ? WHERE `result`.`uuid` = '{$_POST['uuid']}'");

            $stmt->bind_param("s" , $timeslotsarray );

            $stmt->execute();

            $stmt->free_result();
            $stmt->close();


            header('Location: result.php?uuid='.$_POST['uuid'].'&edit');

        }


    }


}