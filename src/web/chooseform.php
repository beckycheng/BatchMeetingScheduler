<?php
date_default_timezone_set("Asia/Hong_Kong");
include $_SERVER["DOCUMENT_ROOT"] . "/conn/conn.php";





$stmt = $conn->prepare("SELECT * FROM `meeting` WHERE `uuid` = ? ");


$stmt->bind_param("s" , $_POST['uuid'] );

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows==1){

    while ($row = $result->fetch_assoc()) {

        $mt_studentid = json_decode($row['studentid'], true);
        $mt_timeslots=json_decode($row['timeslots'], true);

        $timeslotsnum=count($mt_timeslots);

        $mt_deadline = $row['deadline'];




        if(in_array($_POST['studentid'],$mt_studentid)){

            if (time()>strtotime($mt_deadline)){
                header('Location: result.php?uuid='.$_POST['uuid']);
                die();

            }



        }else{
            header('Location: index.html');
            die();
        }

    }



}else{
    header('Location: index.html');
    die();
}

$stmt->free_result();
$stmt->close();











$stmt = $conn->prepare("SELECT * FROM `choose` WHERE `uuid` = ? AND `studentid` = ? ");


$stmt->bind_param("ss" , $_POST['uuid'] ,$_POST['studentid'] );

$stmt->execute();

$result = $stmt->get_result();




for ($x = 2; $x <= 10; $x++) {

    if ($x<=$timeslotsnum){
        ${'choose'.$x}=$_POST['choose'.$x];

    }else{
        ${'choose'.$x}=NULL;
    }

}

if ($result->num_rows>0){

    $row = $result->fetch_assoc();

    $existid=$row['id'];

    $stmt->free_result();
    $stmt->close();


    $stmt = $conn->prepare("UPDATE `choose` SET `choose1` = ?, `choose2` = ?, `choose3` = ?, `choose4` = ?, `choose5` = ?, `choose6` = ?, `choose7` = ?, `choose8` = ?, `choose9` = ?, `choose10` = ? WHERE `choose`.`id` = $existid");

    $stmt->bind_param("ssssssssss",  $_POST["choose1"],$choose2 ,$choose3,$choose4,$choose5,$choose6,$choose7,$choose8,$choose9,$choose10 );


    $stmt->execute();
    $stmt->close();

    header('Location: successchoose.html');

}else{
    $stmt->free_result();
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO `choose` (`id`, `studentid`, `uuid`, `choose1`, `choose2`, `choose3`, `choose4`, `choose5`, `choose6`, `choose7`, `choose8`, `choose9`, `choose10`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');

    $stmt->bind_param("ssssssssssss", $_POST['studentid'],  $_POST['uuid'] , $_POST["choose1"],$choose2 ,$choose3,$choose4,$choose5,$choose6,$choose7,$choose8,$choose9,$choose10 );


    $stmt->execute();
    $stmt->close();

    header('Location: successchoose.html');
}






?>