<?php 
require ("dashboard.php");
require('server/p_db_connection.php');
?>

<!DOCTYPE html>
<html>

<head>

    <title> Class Schedule </title>

    <link href='fullcalendar/fullcalendar.css' rel='stylesheet' />
    <link href='fullcalendar/bootstrap-colorpicker.min.css' rel='stylesheet' />
    <script src='fullcalendar/moment.min.js'></script>
    <script src='fullcalendar/fullcalendar.js'></script>
    <script src='fullcalendar/jquery.form.min.js'></script>
    <script src='fullcalendar/jquery.validate.js'></script>
    <script src='fullcalendar/bootstrap-colorpicker.js'></script>

    <?php
        $current_sy_sql ="SELECT * FROM school_year WHERE is_current= 1 LIMIT 1";
        $current_sy_query = mysqli_query($conn, $current_sy_sql);
        $current_sy_result = mysqli_fetch_assoc($current_sy_query);
        //print_r($current_sy_result['school_year_id']);
        $current_school_year_id = $current_sy_result['school_year_id'];


        $events = array();
        $filter_by = isset($_GET['filterBy']) ? $_GET['filterBy'] : '';
        $section_id = isset($_GET['section_id']) ? $_GET['section_id'] : '';
        $teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : '';
        $room_id = isset($_GET['room_id']) ? $_GET['room_id'] : '';

        if($filter_by) {

            $where_sql = '';
            if($section_id){
                $where_sql = "sc.section_id = '$section_id'";
            }else if($teacher_id){
                $where_sql = "sc.teacher_id = '$teacher_id'";
            }else if($room_id){
                $where_sql = "sc.room_id = '$room_id'";
            }

            if($section_id || $teacher_id ||$room_id){
                $get_schedule_sql ="SELECT * FROM schedule as sc LEFT JOIN subject as su ON sc.subject_id = su.subj_id LEFT JOIN rooms AS ro ON sc.room_id = ro.room_id WHERE $where_sql";

                $get_schedule_query = mysqli_query($conn, $get_schedule_sql);
               
                while ($schedule_result = mysqli_fetch_assoc($get_schedule_query)){
                    $start_time = explode(':', $schedule_result['start_time']);
                    //print_r($start_time);
                    $end_time = explode(':', $schedule_result['end_time']);
                    //print_r($end_time);

                    $new_start_minute = ((int)$start_time[1] - 1) < 10 ? '0'.((int)$start_time[1] - 1) : ((int)$start_time[1] - 1);
                    $new_start_time = $start_time[0].':'.$new_start_minute.':00';

                    $new_end_minute = ((int)$end_time[1] + 1) < 10 ? '0'.((int)$end_time[1] + 1) : ((int)$end_time[1] + 1);
                    $new_end_time = $end_time[0].':'.$new_end_minute.':00';

                    $events[] = array(
                        'title'             => $schedule_result['subj_name'],
                        'room'              => $schedule_result['room_name'],
                        //'start'             => $schedule_result['start_time'],
                        //'end'               => $schedule_result['end_time'],
                        'start'             => $new_start_time,
                        'end'               => $new_end_time,
                        'dow'               => '['.$schedule_result['day'].']',
                        'backgroundColor'   => $schedule_result['background_color'],
                        'borderColor'       => $schedule_result['border_color'],
                        'eventTextColor'    => $schedule_result['text_color'],
                    );
                }
                //echo json_encode($events);
            }
        }
    ?>
    <script>

        $(document).ready(function() {

            $( "#filter_by" ).change(function() {
                var filterBy = $(this).val();
                //alert(filterBy);
                window.location.href = "class_schedule.php?filterBy=" + filterBy;

            });

            $( "#filter_section_id" ).change(function() {
                var section_id = $(this).val();
                //alert(filterBy);
                window.location.href = "class_schedule.php?filterBy=<?php echo $filter_by; ?>&section_id="+section_id;

            });

            $( "#filter_teacher_id" ).change(function() {
                var teacher_id = $(this).val();
                window.location.href = "class_schedule.php?filterBy=<?php echo $filter_by; ?>&teacher_id="+teacher_id;

            });

            $( "#filter_room_id" ).change(function() {
                var room_id = $(this).val();
                window.location.href = "class_schedule.php?filterBy=<?php echo $filter_by; ?>&room_id="+room_id;
 
            });

            
            $('#calendar').fullCalendar({
               
                titleFormat: 'MMMM D, YYYY',
                //defaultView: 'agendaWeek',
                views: {
                    settimana: {
                        type: 'agendaWeek',
                        duration: {
                            days: 7
                        },
                        title: 'Apertura',
                        columnFormat: 'dddd', // Format the day to only show like 'Monday'
                        hiddenDays: [0, 7] // Hide Sunday and Saturday?
                    }
                },
                defaultView: 'settimana',
                firstDay: 1,
                minTime: '07:00:00',
                defaultDate: '2017-09-12',
                navLinks: false, // can click day/week names to navigate views
                editable: false,
                eventLimit: false, // allow "more" link when too many events
                events: <?php echo json_encode($events); ?>,
                /*events:[
                    {
                        title: 'Lunch',
                        start: '13:00:00',
                        end: '13:30:00',
                        dow:[1],
                        backgroundColor: '#ff0000',
                        borderColor: '#ff0000',
                    },
                    {
                        title: 'Meeting',
                        start: '14:30:00',
                        end: '15:30:00',
                        dow:[2,3],
                    }
                ],*/

                eventRender: function(event, element) { 
                    element.find('.fc-title').append("<br/>" + event.room); 
                } 
            });
            
            //$('.fc-toolbar .fc-center h2').text('My Schedule');
            $('.fc-agenda-view .fc-day-grid').hide();

            $('#text_color').colorpicker();
            $('#background_color').colorpicker();
            $('#border_color').colorpicker();

             $.validator.addMethod('le', function(value, element, param) {
                  return this.optional(element) || value <= $(param).val();
            }, 'Invalid value');
            $.validator.addMethod('ge', function(value, element, param) {
                  return this.optional(element) || value >= $(param).val();
            }, 'Invalid value');


            $('#frm_add_schedule').validate({
                    rules:{
                        "course_id":{
                            required:true,
                        },
                        "school_year_id":{
                            required:true
                        },
                        "section_id":{
                            required:true
                        },
                        "subject_id":{
                            required:true
                        },
                        "teacher_id":{
                            required:true
                        },
                        "room_id":{
                            required:true
                        },
                        "day":{
                            required:true
                        },
                        "start_hour":{
                            required:true,
                            le: "#end_hour"
                        },
                        "start_minute":{
                            required:true
                        },
                        "end_hour":{
                            required:true,
                            ge: "#start_hour"
                        },
                        "end_minute":{
                            required:true
                        }
                    },
                    messages:{
                        "course_id":{
                            required:"",
                        },
                        "school_year_id":{
                            required:""
                        },
                        "section_id":{
                            required:""
                        },
                        "subject_id":{
                            required:""
                        },
                        "teacher_id":{
                            required:""
                        },
                        "room_id":{
                            required:""
                        },
                        "day":{
                            required:""
                        },
                        "start_hour":{
                            required:"",
                            le:""
                        },
                        "start_minute":{
                            required:""
                        },
                        "end_hour":{
                            required:"",
                            ge:"",
                        },
                        "end_minute":{
                            required:""
                        }
                    },
                    submitHandler: function(form){
                        $('#validate-notification-wrapper').html('');
                        $.ajax({
                            type: "POST",
                            url: "fullcalendar/schedule.php",
                            data: $('#frm_add_schedule').serialize(),
                            beforeSend: function() {
                                $('input[type=submit]').addClass('disabled');
                                $('#validate-notification-wrapper').html('Validating New Schedule....');
                            },
                            success: function(data){
                                console.log(data);
                                $('#validate-notification-wrapper').html('<div class="alert alert-'+data.class+'">'+data.content+'</div>');
                                $(this).closest('form').find("input[type=text], textarea, select").val("");
                                $('input[type=submit]').removeClass('disabled');
                            }
                        });

                    }
                });
            
        });

    </script>

</head>

<style>
    body {
        font-family: arial;
        overflow-x: hidden; 
    }
    #calendar {
        width: 100%;
        margin: 0 auto;
    }

    .form-control.error{
        border: 1px solid red;
    }
    #validate-notification-wrapper{
        float: left;
        text-align: left;
    }
</style>

<body>

<!-- Head Container Fluid -->
  <div class="container-fluid">

    <!-- Page Heading -->
    <div class="row">
    <div class="col-lg-12">
      <h2 class="page-header"><center> Create Schedule <br></center>
      </h2>
                
      <ol class="breadcrumb">
        <li>
          <i class="fa fa-arrow-right"></i> You are here:
        </li>
        <li class="active">
          <i class="fa fa-file"></i> <a href="admin.php"> Create Schedule:</a>
        </li>
      </ol>
    </div>
    </div>
    <!-- Page Heading -->        
  </div>
  <!-- Head Container-fluid  -->

    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <div class="col-md-3">
                <h4>Filter Schedule</h4>
                    <label>Filter by</label>
                    <select name="filter_by" id="filter_by" class="form-control">
                        <option value="Section" <?php echo $filter_by == 'Section' ? 'selected':''; ?>>Select</option>
                        <option value="Section" <?php echo $filter_by == 'Section' ? 'selected':''; ?>>Section</option>
                        <option value="Room" <?php echo $filter_by == 'Room' ? 'selected':''; ?>>Room</option>
                        <option value="Teacher" <?php echo $filter_by == 'Teacher' ? 'selected':''; ?>>Teacher</option>
                    </select>

                    <?php 
                    if($filter_by == 'Section'){
                       

                        $sec_retrieve ="SELECT * FROM section";
                        $sec_query = mysqli_query($conn, $sec_retrieve);
                              
                        if($sec_query){
                          ?>
                          <br />
                          <label>Section</label>
                          <select class="form-control" name="filter_section_id" id="filter_section_id">
                            <option value=""> Select</option>

                                <?php
                                while ($sec_result = mysqli_fetch_assoc($sec_query)){
                                    $is_selected =  $section_id == $sec_result['sec_id'] ? 'selected':''; 

                                echo '<option value="'.$sec_result['sec_id'].'" '.$is_selected.'>'.$sec_result['sec_name'].' </option>';
                                }
                            echo  '</select>';
                        }
                    }
                    ?>

                    <?php 
                    if($filter_by == 'Teacher'){
                        $teach_retrieve ="SELECT * FROM teacher";
                        $teach_query = mysqli_query($conn, $teach_retrieve);
                          
                          if($teach_query){
                          ?>
                          <br />
                          <label>Teacher</label>
                          <select class="form-control" name="filter_teacher_id" id="filter_teacher_id">
                            <option value=""> Select</option>

                            <?php
                            while ($teach_result = mysqli_fetch_assoc($teach_query)){
                                $is_selected =  $teacher_id == $teach_result['teacher_id'] ? 'selected':''; 
                                echo '<option value="'.$teach_result['teacher_id'].'" '.$is_selected.'>'.$teach_result['teacher_fullname'].' </option>';
                            }
                            echo  '</select>';
                        }
                    }
                    ?>

                    <?php 
                    if($filter_by == 'Room'){
                        $room_retrieve ="SELECT * FROM rooms";
                        $room_query = mysqli_query($conn, $room_retrieve);
                          
                          if($room_query){
                          ?>
                          <br />
                          <label>Room</label>
                          <select class="form-control" name="filter_room_id" id="filter_room_id">
                            <option value=""> Select</option>

                            <?php
                            while ($room_result = mysqli_fetch_assoc($room_query)){
                                $is_selected =  $room_id == $room_result['room_id'] ? 'selected':''; 
                                echo '<option value="'.$room_result['room_id'].'" '.$is_selected.'>'.$room_result['room_name'].' </option>';
                            }
        
                        }
                    }
                    ?>
</select>
            </div>
</div>
</div>
<br/>

            <div class="col-xs-12">
            <div id='calendar'></div>
                <button style="position: absolute;" type="button" class="btn btn-info" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus-circle"></i> Add New Schedule</button>
                
            </div>
        </div>
    

    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
        <!-- Modal content-->
        <form id="frm_add_schedule" method="POST" action="#">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">New Schedule</h4>
            </div>
            <div class="modal-body">
                <div class="row"> 
                    <div class="form-group col-lg-6">
                        <label>Strand / Course</label>
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-file"></span>
                          </span>
                        <?php
                            $sc_retrieve ="SELECT * FROM strand_course";
                            $sc_query = mysqli_query($conn, $sc_retrieve);

                            if($sc_query){
                        ?>
                          <select class="form-control" name="course_id">
                            <option value=""> Strand / Course:</option>
                            
                          <?php 
                                while ($sc_result = mysqli_fetch_assoc($sc_query)){
                                    echo '<option value="'.$sc_result['sc_id'].'">'.$sc_result['sc_name'].', '.$sc_result['sc_sc'].' </option>';
                                }
                          
                                echo '</select>';
                            }
                        ?>

                          </select>
                        </div>
                    </div>

                    s

                    <div class="form-group col-lg-6">
                        <label>Section</label>
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-th"></span>
                          </span>
                           <?php 
                            $sec_retrieve ="SELECT * FROM section";
                            $sec_query = mysqli_query($conn, $sec_retrieve);
                              
                          if($sec_query){
                          ?>
                          <select class="form-control" name="section_id">
                            <option value=""> Section:</option>

                                <?php
                                while ($sec_result = mysqli_fetch_assoc($sec_query)){

                                echo '<option value="'.$sec_result['sec_id'].'">'.$sec_result['sec_name'].' </option>';
                                }
                            echo  '</select>';
                        }
                        ?>
                        </div>
                    </div>

                    <div class="form-group col-lg-6">
                        <label>Subject</label>
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-book"></span>
                          </span>
                        <?php 
                        $subj_retrieve ="SELECT * FROM subject";
                        $subj_query = mysqli_query($conn, $subj_retrieve);
                          
                          if($subj_query){
                          ?>
                          <select class="form-control" name="subject_id">
                            <option value=""> Subject:</option>

                            <?php
                            while ($subj_result = mysqli_fetch_assoc($subj_query)){

                            echo '<option value="'.$subj_result['subj_id'].'">'.$subj_result['subj_name'].' </option>';
                            }
                        echo  '</select>';
                        }
                      ?>
                        </div>
                    </div>

                    <div class="form-group col-lg-6">
                        <label>Teacher</label>
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-users"></span>
                          </span>
                    <?php 
                        $teach_retrieve ="SELECT * FROM teacher";
                        $teach_query = mysqli_query($conn, $teach_retrieve);
                          
                          if($teach_query){
                          ?>
                          <select class="form-control" name="teacher_id">
                            <option value=""> Teacher:</option>

                            <?php
                            while ($teach_result = mysqli_fetch_assoc($teach_query)){

                            echo '<option value="'.$teach_result['teacher_id'].'">'.$teach_result['teacher_fullname'].' </option>';
                            }
                            echo  '</select>';
                        }
                      ?>
                        </div>
                    </div>

                    <div class="form-group col-lg-6">
                        <label>Room</label>
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-home"></span>
                          </span>
                        <?php 
                        $room_retrieve ="SELECT * FROM rooms";
                        $room_query = mysqli_query($conn, $room_retrieve);
                          
                          if($room_query){
                          ?>

                          <select class="form-control" name="room_id">
                            <option value=""> Room:</option>

                            <?php
                            while ($room_result = mysqli_fetch_assoc($room_query)){

                            echo '<option value="'.$room_result['room_id'].'">'.$room_result['room_name'].' </option>';
                            }
                            echo  '</select>';
                        }
                      ?>
                        </div>
                    </div>

                    <div class="form-group col-lg-g">
                        <label>Assign Day / Time Start and End</label>
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-calendar"></span>
                          </span>
                          <select class="form-control" name="day">
                            <option value=""> Day:</option>
                            <option value="1"> Monday</option>
                            <option value="2"> Tuesday</option>
                            <option value="3"> Wednesday</option>
                            <option value="4"> Thursday</option>
                            <option value="5"> Friday</option>
                            <option value="6"> Saturday</option>
                          
                          </select>
                        </div>
                    </div>

                    <div class="form-group col-lg-6">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-clock-o"></span>
                          </span>
                          <select class="form-control" name="start_hour" id="start_hour">
                            <option value=""> Start Hour:</option>
                            <?php
                                for($start_hour=0; $start_hour <= 23; $start_hour++){   
                                   $start_hour = $start_hour < 10 ? '0'.$start_hour : $start_hour;
                                   echo '<option value="'.$start_hour.'">'.$start_hour.'</option>'; 
                                }
                            ?> 
                          </select>
                        </div>
                    </div>
                    <div class="form-group col-lg-1">
                        <label>:</label>
                    </div>
                    <div class="form-group col-lg-5">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-clock-o"></span>
                          </span>
                          <select class="form-control" name="start_minute">
                            <option value="">  Start Minute:</option>
                            <?php
                                for($start_minute=0; $start_minute <= 59; $start_minute++){   
                                   $start_minute = $start_minute < 10 ? '0'.$start_minute : $start_minute;
                                   echo '<option value="'.$start_minute.'">'.$start_minute.'</option>'; 
                                }
                            ?> 
                          </select>
                        </div>
                    </div>

                    <div class="form-group col-lg-6">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-clock-o"></span>
                          </span>
                          <select class="form-control" name="end_hour" id="end_hour">
                            <option value=""> End Hour:</option>
                            <?php
                                for($end_hour=0; $end_hour <= 23; $end_hour++){ 
                                   $end_hour = $end_hour < 10 ? '0'.$end_hour : $end_hour;
                                   echo '<option value="'.$end_hour.'">'.$end_hour.'</option>'; 
                                }
                            ?> 
                          </select>
                        </div>
                    </div>
                    <div class="form-group col-lg-1">
                        <label>:</label>
                    </div>
                    <div class="form-group col-lg-5">
                        <div class="input-group">
                          <span class="input-group-addon"><span class="fa fa-clock-o"></span>
                          </span>
                          <select class="form-control" name="end_minute">
                            <option value="">  End Minute:</option>
                            <?php
                                for($end_minute=0; $end_minute <= 59; $end_minute++){   
                                   $end_minute = $end_minute < 10 ? '0'.$end_minute : $end_minute;
                                   echo '<option value="'.$end_minute.'">'.$end_minute.'</option>'; 
                                }
                            ?> 
                          </select>
                        </div>
                    </div>

                    <div class="form-group col-lg-4">
                        <label>Text Color</label>
                        <div id="text_color" class="input-group colorpicker-component">
                            <input type="text" name="text_color" value="#000000" class="form-control" />
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Background Color</label>
                        <div id="background_color" class="input-group colorpicker-component">
                            <input type="text" name="background_color" value="#5bc0de" class="form-control" />
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Border Color</label>
                        <div id="border_color" class="input-group colorpicker-component">
                            <input type="text" name="border_color" value="#46b8da" class="form-control" />
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    </div>
                 
                </div>
            </div>
            <div class="modal-footer">
              <div id="validate-notification-wrapper"></div>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <input type="submit" value="Submit" name="submit" class="btn btn-primary" />
              <input type="hidden" name="action" value="add-schedule" />
            </div>
        </div>
        </form>  
        </div>
    </div>


</body>
</html>