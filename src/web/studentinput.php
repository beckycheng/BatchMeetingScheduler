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

            header("Location: choose.php?id={$_POST['studentid']}");

        }else{
                ?>
            <script>
                    alert("worng student id");
                    window.location.href = 'index.html';
            </script>
<?php
        }

    }



}else{
    header('Location: index.html');
}

$stmt->free_result();
$stmt->close();