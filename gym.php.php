<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sgaesho_ayoesho";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

if (isset($_GET['action']) && isset($_GET['action'])){
    $action = $_GET['action'];
    $class = $_GET['class'];
    if ($action == 'get_timing_list' && !empty($class)){
        $timings_list = '<option value="">Select Timing </option>';
        $timings_query = "select *from timings where class_id = '$class'";
        $timings_result = $conn->query($timings_query);
        $timings_rows = $timings_result->fetchAll();
        foreach ($timings_rows as $tm_row) {
            $tm_id = $tm_row['id'];
            $tm_name = $tm_row['name'];
            $timings_list .= '<option value="'.$tm_id.'">'.$tm_name.'</option>';

        }
        echo $timings_list;
        exit();
    }
}

if (isset($_POST['submit'])){
    $error_msg = '';
    $class = $_POST['class'];
    $timing = $_POST['timing'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    if ($class == ''){
        $error_msg .= '* Select the class <br/>';
    }
    else{
        $old_class = $class;
    }
    if ($timing == ''){
        $error_msg .= '* Select the timing <br/>';
    }
    else{
        $old_timing = $class;
    }
    if ($name == ''){
        $error_msg .= '* Enter the name <br/>';
    }else{
        $valid_name = true;
        if (!preg_match('/[^A-Za-z ]/', $name))
            $valid_name = false;
        if ($valid_name){
            $error_msg .= '* Name containing special characters OR numbers. Enter the correct name <br/>';
        }
        else{
            $old_name = $name;
        }
    }

    if ($phone == ''){
        $error_msg .= '* Enter the phone <br/>';
    }else{
        if (substr($phone, 0,1) != '0'){
            $error_msg .= '* Phone Number must be started with 0 . Enter the correct Phone Number <br/>';
        }
        else{
            $data = '';
            $valid_phone = 1;
            if (!ctype_digit($phone)) {
                $valid_phone = 2;
            }
            if ($valid_phone == 2){
                $error_msg .= '* Phone Number containing special characters OR letters. Enter the correct Phone Number <br/>';
            }
            else{
                if (strlen($phone) == 9 || strlen($phone) == 10){
                    $old_phone = $phone;
                }
                else{
                    $error_msg .= '* Phone Number Must be 9 OR 10 Digits . Enter the correct Phone Number <br/>';
                }
            }
        }
    }
    if ($error_msg == ''){
        $error_msg2 = '';
        $timings_query1 = "select *from timings where id = '$timing' LIMIT 1";
        $timings_result1 = $conn->query($timings_query1);
        $tm_row1 = $timings_result1->fetch();
        $capacity_count = $tm_row1['capacity'];
        if ($capacity_count > 0){
            $insert_booking_query = "INSERT INTO `bookings`(`class_id`, `timing_id`, `name`, `phone_number`) VALUES (?,?,?,?)";
            try{
                $conn->prepare($insert_booking_query)->execute([$class, $timing, $name, $phone]);
                $capacity_count = $capacity_count - 1;
                $update_timing_query = "UPDATE timings SET capacity=? WHERE id=?";
                $conn->prepare($update_timing_query)->execute([$capacity_count, $timing]);
                $success_message = 'Booking created successfully';
            }catch (PDOException $e){
                $error_msg2 .= '* Booking created failed. Server Error <br/>';
            }
        }
        else{
            $error_msg2 .= '* No Capacity available in this class and timings Please try with other class and timing also you can check the available list below <br/>';
        }
    }
    else{
        $error_msg .= '* Fix these and then try to submit again <br/>';
    }
}

$classes_query = "select *from classes";
$classes_result = $conn->query($classes_query);
$classes_rows = $classes_result->fetchAll();

$timings_query1 = "select t.*, c.name as class_name from timings t INNER JOIN classes c on c.id = t.class_id";
$timings_result1 = $conn->query($timings_query1);
$timings_row1 = $timings_result1->fetchAll();

$booking_query = "select b.*, c.name as class_name, t.name as timing_name from bookings b INNER JOIN classes c on c.id = b.class_id INNER JOIN timings t on t.id = b.timing_id";
$booking_result = $conn->query($booking_query);
$booking_row = $booking_result->fetchAll();

$check_capacity_query = "select sum(capacity) from timings";
$check_capacity_result = $conn->query($check_capacity_query);
$check_capacity_row = $check_capacity_result->fetch(PDO::FETCH_NUM);
$capacity_available = $check_capacity_row[0];
if ($capacity_available == 0){
    $error_msg2 = '* No Capacity available in Our GYM<br/>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {font-family: Arial, Helvetica, sans-serif; text-align: center;}
        * {box-sizing: border-box;
        }

        h1{

            text-align: center;
        }

        .form-inline {
            display: inline-block;
            flex-flow: row wrap;
            align-items: center;
            border: 2px solid gray;
            padding: 10px;
        }

        .form-inline label {
            margin: 5px 10px 5px 0;
        }

        .form-heading {
            font-size: 24px;
            text-align: left;
            color: green;
            margin-top: 0px;
        }

        .form-inline input {
            vertical-align: middle;
            margin: 5px 10px 5px 0;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            min-width: 300px;
        }

        .form-inline select {
            vertical-align: middle;
            margin: 5px 10px 5px 0;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        .form-inline button {
            padding: 10px 20px;
            background-color: dodgerblue;
            border: 1px solid #ddd;
            color: white;
            cursor: pointer;
        }

        .form-inline button:hover {
            background-color: royalblue;
        }

        .error_span{
            color:red;
            background-color: #ddd;
            text-align: left;
            padding: 10px;
            line-height: 22px;
        }

        .success_span{
            color:green;
            background-color: #ddd;
            text-align: left;
            padding: 10px;
            line-height: 22px;
        }

        @media (max-width: 800px) {
            .form-inline input {
                margin: 10px 0;
            }

            .form-inline {
                flex-direction: column;
                align-items: stretch;
            }
        }
        .hak_table {
             font-family: Arial, Helvetica, sans-serif;
             border-collapse: collapse;
             width: 80%;
            text-align: center;
         }

        .hak_table td, .hak_table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .hak_table tr:nth-child(even){background-color: #f2f2f2;}

        .hak_table tr:hover {background-color: #ddd;}

        .hak_table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #04AA6D;
            color: white;
            text-align: center;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<h1>Welcome To GYM</h1>

<form class="form-inline" action="" method="post">
    <h2 class="form-heading">Add New Booking</h2>
    <?php
    if (isset($error_msg)){
        if ($error_msg != '') {
            ?>
            <p class="error_span">* Fix these errors <br/><?php echo $error_msg; ?></p>
            <?php
        }
    }
    ?>
    <?php
    if (isset($error_msg2)){
        if ($error_msg2 != '') {
            ?>
            <p class="error_span"><?php echo $error_msg2; ?></p>
            <?php
        }
    }
    ?>
    <?php
    if (isset($success_message)){
        if ($success_message != '') {
            ?>
            <p class="success_span"><?php echo $success_message; ?></p>
            <?php
        }
    }
    ?>
    <label for="class">Class:</label>
    <select name="class" id="class">
        <option value="">Select Class</option>
        <?php
        foreach ($classes_rows as $cs_row) {
            ?>
            <option value="<?php echo $cs_row['id'];?>"><?php echo $cs_row['name'];?></option>
        <?php
        }
        ?>
    </select>
<!--    --><?php //if (isset($old_class)) if ($old_class == $cs_row['id']) echo 'selected';?>
    <label for="timing">Timing:</label>
    <select name="timing" id="timing">
        <option value="">Select Class First</option>
    </select>
    <label for="name">Name:</label>
    <input type="text" id="name" placeholder="Enter Name" name="name" value="<?php if (isset($old_name)) echo $old_name;?>">
    <label for="phone">Phone Number:</label>
    <input type="text" id="phone" placeholder="Enter Phone Number" name="phone" value="<?php if (isset($old_phone)) echo $old_phone;?>">
    <button type="submit" name="submit">Submit</button>
</form>
<input name="old_class" id="old_class" value="<?php if (isset($old_class)) echo $old_class;?>" hidden>
<input name="old_timing" id="old_timing" value="<?php if (isset($old_timing)) echo $old_timing;?>" hidden>

<h1>Classes List With Available Timings List</h1>
<center><table class="hak_table">
    <tr>
        <th>ID</th>
        <th>Class</th>
        <th>Timings</th>
        <th>Available Capacity</th>
    </tr>
    <?php
    foreach ($timings_row1 as $tr_row1){
        ?>
        <tr>
            <td><?php echo $tr_row1['id'];?></td>
            <td><?php echo $tr_row1['class_name'];?></td>
            <td><?php echo $tr_row1['name'];?></td>
            <td><?php echo $tr_row1['capacity'];?></td>
        </tr>
        <?php
    }
    ?>
</table></center>


<h1>Booking List</h1>
<center><table class="hak_table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone Number</th>
            <th>Class</th>
            <th>Timing</th>
        </tr>
        <?php
        foreach ($booking_row as $bk_row){
            ?>
            <tr>
                <td><?php echo $bk_row['id'];?></td>
                <td><?php echo $bk_row['name'];?></td>
                <td><?php echo $bk_row['phone_number'];?></td>
                <td><?php echo $bk_row['class_name'];?></td>
                <td><?php echo $bk_row['timing_name'];?></td>
            </tr>
            <?php
        }
        ?>
    </table></center>


<script>
    function set_old_timing() {
        var old_timing = $('#old_timing').val();
        $('#timing option[value="'+old_timing+'"]').prop('selected', true);
    }
    var old_class = $('#old_class').val();
    $('#class option[value="'+old_class+'"]').prop('selected', true);
    if (old_class != ''){
        $.ajax({
            method: 'POST',
            url: 'gym.php?action=get_timing_list&class='+old_class,
            success: function (data) {
                $('#timing').html('');
                $('#timing').html(data);
                var old_timing = $('#old_timing').val();
            }
        }).done(set_old_timing);
    }
    $(document).ready(function() {
        $('#class').on('change', function () {
            var selected_class = this.value;
            if (selected_class != ''){
                $.ajax({
                    method: 'POST',
                    url: 'gym.php?action=get_timing_list&class='+selected_class,
                    success: function (data) {
                        $('#timing').html('');
                        $('#timing').html(data);
                    }
                })
            }
            else{
                $('#timing').html('<option value="">Select Class First</option>');
                $('#timing').html(data);
            }
        })
    });
</script>

</body>
</html>