<?php
	include_once("functions.php");
	require_once("database.php");	
	include_once("globals.php");
	$test = true;
	Class employeeList
	{
		Public $name = array();
		Public $hours = array();
		Public $job = array();
	}
	
	Class expense
	{
		Public $name = array();
		Public $cost = array();
	}
	$E_COUNT = 30; //the number of employee lines
	
	// Function to get the client IP address
	function get_client_ip() {
	    $ipaddress = '';
	    if ($_SERVER['HTTP_CLIENT_IP'])
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if($_SERVER['HTTP_X_forWARDED_for'])
	        $ipaddress = $_SERVER['HTTP_X_forWARDED_for'];
	    else if($_SERVER['HTTP_X_forWARDED'])
	        $ipaddress = $_SERVER['HTTP_X_forWARDED'];
	    else if($_SERVER['HTTP_forWARDED_for'])
	        $ipaddress = $_SERVER['HTTP_forWARDED_for'];
	    else if($_SERVER['HTTP_forWARDED'])
	        $ipaddress = $_SERVER['HTTP_forWARDED'];
	    else if($_SERVER['REMOTE_ADDR'])
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}
	
	//variables
	$multiple = false;
	$illegals = array("'",'"',"\n");
	$replacements = array("&#39", "&#34", "<br>");
	$ip = get_client_ip();							
								
	date_default_timezone_set ("America/New_York");
	$now = date("F j, Y @ g:i a");
	$dateTime = date("Y-m-d H:i:s");
	$date = $_POST["Year"] . "-" . $_POST["Month"] . "-" . $_POST["Day"];
	$day = strftime("%A",strtotime($date));
	$update = $_POST['updateSwitch'];
	$update = 0;
	
	//collect the data from the form
	$DUPCHECK = "true";
	$summary = $_POST['summary'];
	$startOdo = $_POST['startodo'];
	$endOdo = $_POST['endodo'];
	$email = $_POST['email'];
	$vehicle = str_replace($illegals, $replacements, $_POST['vid']);
	$eList = new employeeList();
	$broswer = $_SERVER['HTTP_USER_AGENT'];
	$expire = time() + (60*60*24*90); // add seconds for a week
	
	//supervisor info
	$superName = $_POST['nameDrop'];
	$query = mysqli_query($con,"SELECT Name FROM employees WHERE id = $superName"); //TODO: need '' here?
	while($row = mysqli_fetch_array($query)) {
		$superName = $row['Name'];
	}
	$eList->name[0] = $superName;
	$empName[0] = $superName;
	
	if ($empName[0] == ""){
		exit("Something went wrong, press back");
	}
	
	//! Process time
	$nowFormat2 = date("Y-m-d g:i:s a");
	
	$dups = 0;
	$duplicateCheck = mysqli_query($con,"SELECT * FROM Data WHERE Name = '".$empName[0]."' AND Date = '".$date."'");
	while($row = mysqli_fetch_array($duplicateCheck)) {
		$dups++;
	}
	
	//load job numbers
	$tempjob = mysqli_query($con,"SELECT * FROM Jobs ORDER BY Number");	
	while($row = mysqli_fetch_array($tempjob)){
		$jobs[$row['Number']] = $row['Name'];	//dump the results into a job array
	}
	
	if (($dups > 0 && $update == 0) && $DEBUG == false){
		exit(include("exists.php"));
	}
	
	$eList->hours[0] = $_POST['hours'];
	$empHours[0] = $_POST['hours'];
	$eList->job[0] = $_POST['job'];
	$empJob[0] = $_POST['job'];
	$supMultHour = array();
	$supMultJob = array();
	$multHours = isset($_POST['multhours']);
	for ($i=1; $i<=$SUP_MULT_HOUR_COUNT; $i++){
		$supMultHour[$i] = $_POST['hoursm' . $i];
		$supMultJob[$i] = $_POST['jobm' . $i];
	}
	
	//employee info
	for ($i=1; $i<=$E_COUNT; $i++){
		$eList->name[$i] = $_POST['employee' . $i];
		$eList->hours[$i] = $_POST['hours' . $i];
		$eList->job[$i] = $_POST['job' . $i];
		$empName[$i] = $_POST['employee' . $i];
		$empHours[$i] = $_POST['hours' . $i];
		$empJob[$i] = $_POST['job' . $i];
	}
	for ($i=1; $i<=$E_COUNT; $i++){
		$subName[$i] = $_POST['semployee' . $i];
		$subHours[$i] = $_POST['sub' . $i];
		$subJob[$i] = $_POST['sjob' . $i];
	}
	
	//expenses
	$exp = new expense();
	for ($i=1; $i<=$E_COUNT; $i++){
		$exp->name[$i] = $_POST['expense' . $i];
		$exp->cost[$i] = $_POST['cost' . $i];
		$expName[$i] = $_POST['expense' . $i];
		$expCost[$i] = $_POST['cost' . $i];
		$expJob[$i] = $_POST['ejob' . $i];
	}
	
	
	$nameTest = explode(" ", $empName[0]);
	$empName[0] = $nameTest[0]." ".$nameTest[1];
	$eList->name[0] = $empName[0];
	
	//calculate total expenses
	for ($i=1; $i <= 10; $i++){
		$totalExpense += $exp->cost[$i];
	}
	
	
	//! cookies
	setcookie("email", $email, $expire);
	setcookie("name", $empName[0], $expire);
	setcookie("job", $empJob[0], $expire);
	include("nav2.php");
	
	
	//check db for duplicates
	/*$result = mysqli_query($con,"SELECT * FROM Data WHERE Date = '".$date."' AND Name = '".$empName[0]."'");
	while($row = mysqli_fetch_array($result)) {
		$duplicate .= "<h3>" . $row['Name'] . "</h3> <h4>Submitted: " . $row['Submitted'] . "</h4>" . $row['Summary'];
		if ($row['Name'] == $empName[0]){
			echo "<h1 style='color:#FF0000;'>It looks like you have already turned in a recap for ".$_POST['Month'] . "-" . $_POST['Day'] . "-" . $_POST['Year']."</h1><h2>Here is what you have already turned in</h2>";
			echo $duplicate;
			include 'index.php';
			exit();
		}
	}*/
	
	
	//find out what date sunday is
	$testdate = $date;
	for($i=0;$i<7;$i++){
		if (strftime("%A",strtotime($testdate)) == "Sunday") {
			$sunday = $testdate;
			if ($i == 0){
				$start = "Sunday";
			}
			else{
				$start = "Not Sunday";
			}
		}
		$testdate = date("Y-m-d", strtotime("-1 days", strtotime($testdate)));
	}
	
	for ($i=1; $i <= $SUP_MULT_HOUR_COUNT; $i++){
		if ($supMultHour[$i] > 0){
			$supHourTotal += $supMultHour[$i];
		}
	}
	$supHourTotal += $empHours[0];
	
	$unit[0] = "";
	if($empJob[0] == 200){
		$unit[0] = $_POST['unit0'];
	}
	
	//compose message
	//make table for employee hours
	if($update == 0){
		setHours($empName[0], $date, $empJob[0], $empHours[0], $empName[0], $unit[0]);
	}
	
	if($unit[0] != ""){
		$result = mysqli_query($eqcon,"SELECT * FROM Equipment WHERE EquipmentID ='".$unit[0]."'");
		while($row = mysqli_fetch_array($result)) {
			$uDescription[0] = " " . $row['MakeModel'] . " " . $row['Description'];
		}
		$unit[0] = " " . $unit[0];
	}
	else{
		$uDescription[0] = "";
	}
	
	$message .= "<table><th>Name</th><th>hours</th><th>job</th><th>week hours</th>";
	$message .= "<tr><td  align='center'>".$eList->name[0] . "</td><td  align='center'>" . $eList->hours[0] . "</td><td  align='center'>#" . $eList->job[0] . $unit[0] ."</td>";
	if (getWeeklyHours($empName[0], $date) > 40){
		$message .= "<td  align='center' bgcolor='#FF0000'><b>".getWeeklyHours($empName[0], $date)."</b></td></tr>";
	}
	else if(getWeeklyHours($empName[0], $date) > 30 && getWeeklyHours($empName[0], $date) <= 40){
		$message .= "<td  align='center' bgcolor='#FFFF00'><b>".getWeeklyHours($empName[0], $date)."</b></td></tr>";
	}
	else{
		$message .= "<td  align='center'>".getWeeklyHours($empName[0], $date)."</td></tr>";
	}
	$totalHours = $eList->hours[0];
	//!supervisor portion
	/*for ($i=1; $i <= 10; $i++){
		if ($supMultHour[$i] > 0){
			setHours($empName[0], $date, $supMultJob[$i], $supMultHour[$i], $empName[0]);
			$message .= "<tr><td>---></td><td>" . $supMultHour[$i] . "</td><td>#" . $supMultJob[$i] ."</td>";
			if (getWeeklyHours($empName[0], $date) > 40){
				$message .= "<td align='center' bgcolor='#FF0000'><b>".getWeeklyHours($empName[0], $date)."</b></td></tr>";
			}
			else if(getWeeklyHours($empName[0], $date) > 30 && getWeeklyHours($empName[0], $date) <= 40){
				$message .= "<td align='center' bgcolor='#FFFF00'><b>".getWeeklyHours($empName[0], $date)."</b></td></tr>";
			}
			else{
				$message .= "<td align='center'>".getWeeklyHours($empName[0], $date)."</td></tr>";
			}
			$totalHours += $supMultHour[$i];
		}
	}*/
	for ($i=1; $i <= $SUP_MULT_HOUR_COUNT; $i++){
		if ($supMultHour[$i] > 0){
			
			if($supMultJob[$i] == 200){
				$unit[$i] = $_POST['unit'.$i];
			}
			else{
				$unit[$i] = "";
			}
			
			if($update == 0){
				setHours($empName[0], $date, $supMultJob[$i], $supMultHour[$i], $empName[0], $unit[$i]);
			}
			
			if($unit[$i] != ""){
				$result = mysqli_query($eqcon,"SELECT * FROM Equipment WHERE EquipmentID ='".$unit[$i]."'");
				while($row = mysqli_fetch_array($result)) {
					$uDescription[$i] = " " . $row['MakeModel'] . " " . $row['Description'];
				}
				$unit[$i] = " " . $unit[$i];
			}
			else{
				$uDescription[$i] = "";
			}
			
			$message .= "<tr><td  align='center'>" . $empName[0] . "</td><td  align='center'>" . $supMultHour[$i] . "</td><td  align='center'>#" . $supMultJob[$i] . $unit[$i] ."</td>";
			if (getWeeklyHours($eList->name[0], $date) > 40){
				$message .= "<td  align='center' bgcolor='#FF0000'><b>".getWeeklyHours($eList->name[0], $date)."</b></td></tr>";
			}
			else if(getWeeklyHours($eList->name[0], $date) > 30 && getWeeklyHours($eList->name[0], $date) <= 40){
				$message .= "<td  align='center' bgcolor='#FFFF00'><b>".getWeeklyHours($eList->name[0], $date)."</b></td></tr>";
			}
			else{
				$message .= "<td  align='center'>".getWeeklyHours($eList->name[0], $date)."</td></tr>";
			}
			$totalHours += $supMultHour[$i];
		}
	}
	
	//print each employee that was entered in the form
	//hours
	//!employee hours
	$count = 1;
	for ($i=1; $i<=$E_COUNT; $i++){
		if ($eList->name[$i] != "" && $eList->name[$i] != "---Select Employee---"){
			if($update == 0){
				setHours($empName[$i], $date, $empJob[$i], $empHours[$i], $empName[0]);
			}
			$message .= "<tr><td  align='center'>" . $empName[$i] . "</td><td  align='center'>" . $empHours[$i] . "</td><td  align='center'>#" . $empJob[$i] ."</td>";
			if (getWeeklyHours($eList->name[$i], $date) > 40){
				$message .= "<td  align='center' bgcolor='#FF0000'><b>".getWeeklyHours($eList->name[$i], $date)."</b></td></tr>";
			}
			else if(getWeeklyHours($eList->name[$i], $date) > 30 && getWeeklyHours($eList->name[$i], $date) <= 40){
				$message .= "<td  align='center' bgcolor='#FFFF00'><b>".getWeeklyHours($eList->name[$i], $date)."</b></td></tr>";
			}
			else{
				$message .= "<td  align='center'>".getWeeklyHours($eList->name[$i], $date)."</td></tr>";
			}
			$count++;
			$totalHours += $eList->hours[$i];
		}
	}
	
	$message .= "<tr><td  align='center' colspan='4'><b>Total Hours for ".$count." employee(s): " . $totalHours . "</b></td></tr>";
	
	//------========---------Subs-----------========---------==========----------
	//!subs
	if($subName[1] != "" && $subName[$i] != "---Select Employee---"){
		$message .= "<tr><td  align='center' colspan='4'><hr></td></tr>";
	}
	$scount = 0;
	for ($i=1; $i<=$E_COUNT; $i++){
		if ($subName[$i] != "" && $subName[$i] != "---Select Employee---"){
			if($update == 0){
				setHours($subName[$i], $date, $subJob[$i], $subHours[$i], $empName[0]);
			}
			$message .= "<tr><td  align='center'>" . $subName[$i] . "</td><td  align='center'>" . $subHours[$i] . "</td><td  align='center'>#" . $subJob[$i] ."</td>";
			if (getWeeklyHours($subName[$i], $date) > 40){
				$message .= "<td  align='center' bgcolor='#FF0000'><b>".getWeeklyHours($subName[$i], $date)."</b></td></tr>";
			}
			else if(getWeeklyHours($subName[$i], $date) > 30 && getWeeklyHours($subName[$i], $date) <= 40){
				$message .= "<td  align='center' bgcolor='#FFFF00'><b>".getWeeklyHours($subName[$i], $date)."</b></td></tr>";
			}
			else{
				$message .= "<td  align='center'>".getWeeklyHours($subName[$i], $date)."</td></tr>";
			}
			$scount++;
			$count++;
			$sHours += $subHours[$i];
			$totalHours += $subHours[$i];
		}
	}
	$message .= "</table>";
	if($subName[1] != "" && $subName[$i] != "---Select Employee---"){
		$message = $message . "<b>Total Hours for ".$scount." sub(s): " . $sHours . "</b><hr>";//put some logic on this to hide when just 1 employee
		$message = $message . "<b>Total Hours for ".$count." people: " . $totalHours . "</b><hr>";
	}
	
	//Expenses------------_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_--_
	//!expenses
	for ($i=1; $i<=$E_COUNT; $i++){
		if ($expCost[$i] > 0 && $update == 0){
			$exsql = "INSERT INTO Expenses (Submitted, Date, Name, Job, Expense, Cost) VALUES ('$now', '$date', '$empName[0]', '$expJob[$i]', '$expName[$i]', '$expCost[$i]')";
			mysqli_query($con, $exsql);
		}
	}
	
	
	
	//put expenses in $message
	if ($exp->cost[1] > 0){
		$message.= "<table>";
	}
	for ($i=1; $i<=10; $i++){
		if ($exp->cost[$i] > 0){
			$message .= "<tr><td>".$exp->name[$i]."</td><td> $". $exp->cost[$i]."</td><td> #$expJob[$i]</td></tr>";
			$totalCost += $exp->cost[$i];
		}
	}
	if ($exp->cost[1] > 0){
		$message.= "</table>";
	}
	if ($totalCost > 0){
		$message = $message . "<b>Total expenses: $" . $totalCost . "</b><hr>";
	}
	
	//add odometer to $message
	//!odometer
	if ($startOdo != "" && $endOdo != "" && $update == 0){
		mysqli_query($con, "INSERT INTO Vehicle (VehicleID, Odometer, Submitter, Submitted, Date) VALUES ('$vehicle', '$endOdo', '$empName[0]', '$now', '$date')");
		$diff = $endOdo - $startOdo;
		$message = $message ."Vehicle #: ". $vehicle .", Odometer: " . $startOdo . " - " . $endOdo . "<br><b>Total Mileage: " . $diff .'</b><hr>';
	}
	
	//!cleanup message
	//replace line breaks with <BR>
	$summary = str_replace($illegals, $replacements, $summary);
	$planning = str_replace($illegals, $replacements, $_POST['planning']);
	$problems = str_replace($illegals, $replacements, $_POST['problems']);
	$discipline = str_replace($illegals, $replacements, $_POST['discipline']);
	$recognition = str_replace($illegals, $replacements, $_POST['recognition']);
	$technicalDifficulties = str_replace($illegals, $replacements, $_POST['technicalDifficulties']);
	
	//accumulate job summaries
	$summary = "<fieldset><legend>" . $empJob[0] . ": " . $jobs[$empJob[0]] ." ". $unit[0] ." ". $uDescription[0] . "</legend>" . $summary. "</fieldset><Br>";
	for($i=1;$i<$SUP_MULT_HOUR_COUNT;$i++){
		$temp = "summary" . $i;
		if($supMultJob[$i] > 90){
			$tempsummary = str_replace($illegals, $replacements, $_POST[$temp]);
			$summary .= "<fieldset><legend>" . $supMultJob[$i] . ": " . $jobs[$supMultJob[$i]] ." ". $unit[$i] ." ". $uDescription[$i] . "</legend>" . $tempsummary . "</fieldset><Br>";
		}
	}
	
	if ($planning != ""){
		$planning = "<hr><h4>Next Day Planning</h4>" . $planning;
	}
	if ($problems != ""){
		$problems = "<hr><h4>Problems</h4>" . $problems;
	}
	if ($discipline != ""){
		$discipline = "<hr><h4>Discipline</h4>" . $discipline;
	}
	if ($recognition != ""){
		$recognition = "<hr><h4>Recognition</h4>" . $recognition;
	}
	
	//output started here
	//! Technical Difficulties
	if($technicalDifficulties != ""){
		echo "Technical Difficulties:<br>$technicalDifficulties<Br>";
		//input into database
		$sql = "INSERT INTO Tickets (Submitter, Submitted, Status, Message) VALUES ('$empName[0]', '$now', 'New', '$technicalDifficulties')";
		mysqli_query($con, $sql);
		//email me and submitter
		$headers2 = "MIME-Version: 1.0" . "\r\n";
		$headers2 .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
		$headers2 .= "From: $empName[0]" . "\r\n" . "cc: jeremy@tsidisaster.com";
		if(mail($email, "Technical Difficulties", $technicalDifficulties, $headers2)){
			echo "<h2>A technical difficulties email has been successfully sent</h2>";
		}
		else{
			echo "<h2>Nobody has been notified about the technical difficulty, take a screenshot and send it in.</h2>";
		}
		//see if this email function will mess up the recap receipt email function
	}
	
	//! Job metrics
	if($_POST["job"] == 192){
		for ($i=0; $i<2; $i++){
			if(($_POST['kensingtonLake' . $i] != "---Select Lake---") && ($_POST['kensingtonLake' . $i] != 0)){

				$kensingtonLake = $_POST['kensingtonLake'.$i];
				$fabric = $_POST['fabric'.$i];
				$geowebPlaced = $_POST['geowebPlaced'.$i];
				$fillPlaced = $_POST['fillPlaced'.$i];
				$grading = $_POST['grading'.$i];
				$tieins = $_POST['tieins'.$i];
				$rockPlaced = $_POST['rockPlaced'.$i];
				$topsoilPlaced = $_POST['topsoilPlaced'.$i];
				$sodPlaced = $_POST['sodPlaced'.$i];
				$fillDelivered = $_POST['fillDelivered'.$i];
				$rockDelivered = $_POST['rockDelivered'.$i];
				$topsoilDelivered = $_POST['topsoilDelivered'.$i];

				if($kensingtonLake != 0){
					$KenData .= "Lake: $kensingtonLake";
				}
				if($fabric != ""){
					$KenData .= "<br>fabric: $fabric Linear feet";
				}				
				if($geowebPlaced != ""){
					$KenData .= "<Br>geoweb placed: $geowebPlaced Linear feet";	
				}
				if($fillPlaced != ""){
					$KenData .= "<Br>fill dirt placed: $fillPlaced Tons";
				}
				if($grading != ""){
					$KenData .= "<Br>grading done: $grading Linear feet";
				}
				if($tieins != ""){
					$KenData .= "<Br>tie-ins placed: $tieins Each";
				}
				if($rockPlaced != ""){
					$KenData .= "<Br>rock placed: $rockPlaced Linear feet";
				}
				if($topsoilPlaced != ""){
					$KenData .= "<Br>topsoil placed: $topsoilPlaced Linear feet";
				}
				if($sodPlaced != ""){
					$KenData .= "<Br>sod placed: $sodPlaced Square Feet";
				}
				if($fillDelivered != ""){
					$KenData .= "<Br>fill delivered: $fillDelivered Tons";
				}
				if($rockDelivered != ""){
					$KenData .= "<Br>rock delivered: $rockDelivered Tons";
				}
				if($topsoilDelivered != ""){
					$KenData .= "<Br>topsoil delivered: $topsoilDelivered Cubic Yards";
				}
				$KenData .= "<Br><BR>";
				
				$message.= $KenData;
				
				if($update == 0){
					$sql = "INSERT INTO Kensington (
						submitter, 
						Date, 
						submittedOn, 
						job, 
						lake, 
						filterFabric, 
						geoweb, 
						fillDirtPlaced, 
						graded, 
						tieIns, 
						rockPlaced, 
						topsoilPlaced, 
						sodPlaced, 
						fillDirtDelivered, 
						rockDelivered, 
						topsoilDelivered
					) 
					VALUES (
						'".$empName[0]."', 
						'$date', 
						'$dateTime', 
						'".$_POST['job']."', 
						'$kensingtonLake', 
						'$fabric', 
						'$geowebPlaced', 
						'$fillPlaced', 
						'$grading', 
						'$tieins', 
						'$rockPlaced', 
						'$topsoilPlaced', 
						'$sodPlaced', 
						'$fillDelivered',
						'$rockDelivered', 
						'$topsoilDelivered'
					)";
					
					mysqli_query($con, $sql);
				}
			}
		}
	}
	
	if($_POST["job"] == 195){
		$scarData = "Dikes: ".$_POST['dike']. " Truckloads
		<br>Land Smoothing: ".$_POST['landSmoothing']. " (1/4) mile intervals
		<Br>Silt Fence Placed: ".$_POST['siltFencePlaced']. " Truckloads
		<Br>Structures: ".$_POST['structures']. " Structures
		<Br>Berms: ".$_POST['berms']. " Loads hauled
		<Br><BR>";
		
		$message.= $scarData;
		
		$sql = "INSERT INTO WetlandData (
				submitter, 
				Date, 
				submittedOn, 
				job,
				dike,
				landSmoothing,
				siltFencePlaced,
				structures,
				berms
			)
			VALUES(
				'".$empName[0]."', 
				'$date', 
				'$dateTime', 
				'".$empJob[0]."',
				'".$_POST['dike']."',
				'".$_POST['landSmoothing']."',
				'".$_POST['siltFencePlaced']."',
				'".$_POST['structures']."',
				'".$_POST['berms']."
			')";
		mysqli_query($con, $sql);
	}
	?>
<html>
<head>
	
	<link rel='stylesheet' href='mystyle.css'>
	<style>
		legend{
			color: #25ACC1;
			font-size: 18px;
		}

	</style>
		
<title>Receipt</title>
</head>
<body>
	<?
	//print the page
	echo "<h1>Thank you for turning in your recap for today.</h1>
	<h2>Recap Receipt for " . $day . " " . $_POST['Month'] . "-" . $_POST['Day'] . "-" . $_POST['Year'] . "</h2>
	 <h4>A copy of your recap is below, and has been emailed to " . $email . " as a receipt. <br>if there are errors in your recap, please reply to the email sent to you with the corrections.</h4>";
	echo $message;
	echo $summary . $planning . $problems . $discipline . $recognition;
	echo "</body></html>";
	
	
	$message = $summary . $planning . $problems . $discipline . $recognition . "<hr>" . $message;
	
	$emessage = "
		<head><style>
			legend{
				color: #25ACC1;
				font-size: 18px;
			}
		</style></head>
	<h1>Recap Receipt for " . $day . " " . $_POST['Month'] . "-" . $_POST['Day'] . "-" . $_POST['Year'] . "</h1>" . $message . "<Br><BR>".$broswer;
	//!email message & DATA into database
	
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	
	//! check for reporting to
	$tmp = mysqli_query($con,"SELECT * FROM employees where recap != '' ORDER BY Name");
	while($row = mysqli_fetch_array($tmp)) {
		if($row['Name'] == $empName[0]){
			if($row['ReportingTo'] != ""){
				$headers .= "From: robot@tsidisaster.net" . "\r\n" . "Bcc: jeremy@tsidisaster.com, " . $row['ReportingTo'] . " ";
				$emessage .= "<h3>A copy of this email has been sent to " . $row['ReportingTo'];
			}
			else{
				$headers .= "From: robot@tsidisaster.net" . "\r\n" . "Bcc: jeremy@tsidisaster.com";
			}
			break;
		}
	}
	
	$emessage = wordwrap($emessage, 70, "\r\n");


	if(mail($email, "Recap Receipt", $emessage, $headers)){
		echo "<h2>An email has been successfully sent</h2>";
	}
	else{
		echo "<h1 style='color: #FF0000;'>for some reason, a Recap Receipt has not been sent to your email but your recap has been submitted</h1>";
	}
	
	$message = mysqli_real_escape_string($con, $message);
	//DATABASE
	
	
	$update = 0;
	if($update == 0){
		$sql = "INSERT INTO Data (
			Name, 
			Submitted, 
			Email, 
			Summary, 
			Date, 
			IP, 
			Hours, 
			Expenses,
			Mileage, 
			userAgent,
			StartTime,
			EndTime
		) 
		VALUES (
			'$empName[0]', 
			'$now', 
			'$email', 
			'$message',
			'$date', 
			'$ip', 
			'$totalHours', 
			'$totalCost', 
			'$diff', 
			'$broswer',
			'".$_POST['load_time']."', 
			'$nowFormat2'
		)";
	}
	else{
		echo "<h1>UPDATED</h1>";
		$sql = "UPDATE Data SET Name='$empName[0]', Submitted='$now', Email='$email', Summary='$message', Date='$date', IP='$ip', Hours='$totalHours', Expenses='$totalCost', Mileage='$diff', userAgent='$broswer' WHERE Name='$empName[0]' AND Date='$date'";
	}
	
	mysqli_query($con, $sql);
	
	// add hours 
	$theJob = $_POST['job'];
	for($i=0; $i<=10; $i++){
		if($empHours[$i] != ""){
			if($eList->job[$i] == $theJob){
				$jobHours += $eList->hours[$i];
			}
		}
	}
	

?>
</body>
</html>
