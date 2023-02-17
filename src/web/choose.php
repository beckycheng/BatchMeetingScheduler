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

    }



}else{
    header('Location: index.html');
}

$stmt->free_result();
$stmt->close();


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


                <h1 class="mb-4 text-poly">Please select time slots by your preferences</h1>


                <h4>Meeting title: <small class="text-secondary"> <?php echo $mt_title ?></small></h4>
                <h4>Subject title: <small class="text-secondary"><?php echo $mt_subject ?></small></h4>
                <h4>Teacher name: <small class="text-secondary"><?php echo $mt_teacher ?></small></h4>
                <h4>Duration of each meeting (minutes): <small class="text-secondary"><?php echo $mt_duration ?></small></h4>
                <h4>Deadline time: <small class="text-secondary"> <?php echo $mt_deadline ?> </small></h4>
                <h4>Meeting code: <small class="text-secondary"> <?php echo $_POST['code'] ?> </small></h4>


                <form  action="chooseform.php" id="formchoose" method="post" enctype="multipart/form-data">

                    <input type="hidden"  name="studentid" value="<?php echo $_POST['studentid'] ?>">
                    <input type="hidden"  name="uuid" value="<?php echo $_POST['code'] ?>">

                    <div class="mb-3 mt-5">
                        <label for="code" class="form-label">First choose</label>
                        <select class="form-select" name="choose1" id="choose1" required>
                            <option disabled selected hidden>Click to select time slots</option>

                            <?php

                            foreach ($mt_timeslots as $value){
                                echo "<option value=\"$value\">$value</option>";
                            }

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
                            <select class="form-select" name="choose<?php echo $x ?>" id="choose<?php echo $x ?>"  required>
                                <option disabled selected hidden>Click to select time slots</option>

                                <?php

                                foreach ($mt_timeslots as $value){
                                    echo "<option value=\"$value\">$value</option>";
                                }

                                ?>

                            </select>

                        </div>


                        <?php

                    }




                    ?>


                    <div class="row">
                        <div class="col-6">
                            <div class="d-grid">
                                <button type="button" id="reselect" class="btn btn-primary fw-bold text-white" >Reselect</button>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-grid">
                                <button type="button" id="submitbtn" class="btn btn-poly fw-bold text-white" >Submit</button>
                            </div>
                        </div>
                    </div>





                </form>



            </div>
        </div>
    </main>

</div>


<script src="/scripts/jquery-3.6.0.min.js"></script>
<script>
    $( document ).ready(function() {
    const timeslots = <?php echo json_encode($mt_timeslots)    ?> ;

    var remaintimeslots= $.extend( true, [], timeslots );
        $(".form-select").prop( "disabled", true );
        $("#choose1").prop( "disabled", false );


        $("#submitbtn").click(function(){
            $(".form-select").prop( "disabled", false );
            $("#formchoose").submit();


        });



    $("#reselect").click(function(){
        $("#choose1").prop( "disabled", false );

        remaintimeslots= $.extend( true, [], timeslots );


        var option =`<option disabled="" selected="" hidden="">Click to select time slots</option>`;

        remaintimeslots.forEach(function(value){
            option+=`<option value="`+value+`">`+value+`</option>`;
        });

        $("#choose1").empty().append(option);

        if (timeslots.length>10){
            var count=10;
        }else {
            var count=timeslots.length;
        }



        for (let i = 2; i <= count; i++) {

            $("#choose"+i).empty().append(`<option disabled="" selected="" hidden="">Click to select time slots</option>`);
        }


    });



    $(".form-select").change(function(){

        //var thiselect = $(this).attr('id').charAt($(this).attr('id').length - 1);

        var thiselect = $(this).attr('id').replace("choose", "");


        var selectedtimeslot = remaintimeslots.indexOf($(this).val());



        if (selectedtimeslot > -1) {
            remaintimeslots.splice(selectedtimeslot, 1);
        }


        if (parseInt(thiselect)<10){



            $("#choose"+(parseInt(thiselect)+1)).prop( "disabled", false );
            $("#choose"+(parseInt(thiselect))).prop( "disabled", true );

            var option =`<option disabled="" selected="" hidden="">Click to select time slots</option>`

            remaintimeslots.forEach(function(value){
                option+=`<option value="`+value+`">`+value+`</option>`;
            });


            $("#choose"+(parseInt(thiselect)+1)).empty().append(option);
        }






    });

    });

</script>


</body>
</html>