<html>
	<?php
		
		date_default_timezone_set ("America/New_York");
		include("database.php");
		include("functions.php");
		include("nav.html");
		
		//----------------------------------------------------------------------------
		//! Backend
		//============================================================================
			
			//if(isset($_POST['firstname'])){
				/*
					echo "<pre>";
					print_r($_POST);
					echo "</pre>";
				*/
				/*
				$illegals = array("'",'"',"\n");
				$replacements = array("&#39", "&#34", "<br>");
				
				foreach($_POST as $key=>$value){
					$_POST[$key] = str_replace($illegals, $replacements, $value);
				}
				
				
				$newb = new TSIemployee();
				
				$newb->name = ucfirst(trim($_POST['firstname'])) . " " . ucfirst(trim($_POST['lastname']));
				$newb->firstName = ucfirst(trim($_POST['firstname']));
				$newb->status = ucfirst(trim($_POST['status']));
				if(strtoupper(trim($_POST['company'])) == "TSI"){
					$newb->company = "TSI";
				}
				else{
					$newb->company = $_POST['company'];
				}
				foreach($_POST as $key => $value){
					if($key == "exempt"){
						if($value == "on"){
							$newb->exempt = "exempt";
						}
						else{
							$newb->exempt = "";
						}
					}
					if($key == "recap"){
						if($value == "on"){
							$newb->recap = "yes";
						}
						else{
							$newb->recap = "";
						}
					}
				}
				$newb->email = trim($_POST['email']);
				$newb->daysMissing = trim($_POST['daysmissing']);
				$newb->reportsTo = trim($_POST['reportingto']);
				$newb->sqlId = $_POST['id'];
				
				
				echo "<pre>";
				print_r($newb);
				echo "</pre>";
				
				
				if($_POST['action'] == "add"){
					$sql = "INSERT INTO employees (
						Name, 
						Status, 
						Company, 
						recap,
						email,
						exempt,
						daysMissing,
						ReportingTo,
						Firstname
					)
					VALUES(
						'".$newb->name."', 
						'".$newb->status."', 
						'".$newb->company."', 
						'".$newb->recap."', 
						'".$newb->email."',
						'".$newb->exempt."',
						'".$newb->daysMissing."',
						'".$newb->reportsTo."',
						'".$newb->firstName."')";
				}
				else if($_POST['action'] == "edit"){
					$sql = "UPDATE employees SET 
						Name = '".$newb->name."',
						Status = '".$newb->status."', 
						Company = '".$newb->company."',
						recap = '".$newb->recap."',
						email = '".$newb->email."',
						exempt = '".$newb->exempt."',
						daysMissing = '".$newb->daysMissing."',
						ReportingTo = '".$newb->reportsTo."',
						Firstname = '".$newb->firstName."'
						WHERE id='".$newb->sqlId."'";
				}
				if(mysqli_query($con, $sql)){
					echo "<h2>Entered into database successfully</h2>";
				}
				else{
					echo "<h2>There was a problem entering into database</h2>";
				}
			}
		*/
		//----------------------------------------------------------------------------
		//! Front End
		//============================================================================
		
		$database_results = mysqli_query($con,"SELECT * FROM Hours WHERE Date != '0000-00-00' ");
		while($row = mysqli_fetch_array($database_results)) {
			$submitter[] = $row['Submitter'];
			$date[] = $row['Date'];
			$name[] = $row['Name'];
			$job[] = $row['Job'];
			$hours[] = $row['Hours'];
			$weekHours[] = $row['WeeklyHours'];
			$submitted[] = $row['Submitted'];
			$overTime[] = $row['OT'];
		}
		krsort($date);
		unset($database_results);
		unset($row);
		
		$database_results = mysqli_query($con,"SELECT DISTINCT(Date) FROM Hours WHERE Date != '0000-00-00' group by Date desc");
		while($row = mysqli_fetch_array($database_results)) {
			$distinctDates[] = $row['Date'];
		}
		unset($database_results);
		unset($row);
		
		$database_results = mysqli_query($con,"SELECT * FROM Jobs WHERE Number != '' GROUP BY Number");
		while($row = mysqli_fetch_array($database_results)) {
			$jobList[$row['Number']] = $row['Name'];
		}
		unset($database_results);
		unset($row);
		
		$eCount = 0;
		$database_results = mysqli_query($con,"SELECT * FROM employees GROUP BY Name");
		while($row = mysqli_fetch_array($database_results)) {
			$allNames[$eCount] = $row['Name'];
			$status[$eCount] = $row['Status'];
			if($row['email'] != ""){
				$recapNames[$eCount] = $row['Name'];
			}
			$eCount++;
		}
		unset($database_results);
		unset($row);
		
		//echo "<pre>";
		//print_r($recapNames);
		//echo "</pre>";
		
		
	?>
			
		
	<head>
		<style>
			table{
				table-layout: fixed;
			}
			td { 
				width: 33%; 
			}
			th { 
				width: 33%; 
			}
		</style>
		
		<script type='text/javascript'>
			<?//!TO DO: only pass if $post is set
			/*var jSubmitter = [<?
				for($i=0; $i<sizeof($submitter); $i++){
					echo "'$submitter[$i]',";
				}
			?>];
			var jDate = [<?
				for($i=0; $i<sizeof($date); $i++){
					echo "'$date[$i]',";
				}
			?>];
			var jName = [<?
				for($i=0; $i<sizeof($name); $i++){
					echo "'$name[$i]',";
				}
			?>];
			var jJob = [<?
				for($i=0; $i<sizeof($job); $i++){
					echo "'$job[$i]',";
				}
			?>];
			var jHours = [<?
				for($i=0; $i<sizeof($hours); $i++){
					echo "'$hours[$i]',";
				}
			?>];
			var jWeekHours = [<?
				for($i=0; $i<sizeof($weekHours); $i++){
					echo "'$weekHours[$i]',";
				}
			?>];
			var jSubmitted = [<?
				for($i=0; $i<sizeof($submitted); $i++){
					echo "'$submitted[$i]',";
				}
			?>];
			var jOverTime = [<?
				for($i=0; $i<sizeof($overTime); $i++){
					echo "'$overTime[$i]',";
				}
			?>];
			*/?>
			
			function Populate(id){
				var lastname = eName[id];
				lastname = lastname.split(" ");
				
				document.getElementById("edit").checked = true;
				document.getElementById("button").value = "Edit Employee";
				document.getElementById("lastname").value = lastname[1];
				document.getElementById("email").value = eEmail[id];
				document.getElementById("status").value = eStatus[id];
				document.getElementById("company").value = eCompany[id];
				document.getElementById("daysmissing").value = eDaysmissing[id];
				document.getElementById("reportingto").value = eReportingto[id];
				document.getElementById("id").value = eID[id];
				
				if(eFirstname[id] != ""){
					document.getElementById("firstname").value = eFirstname[id];
				}
				else{
					document.getElementById("firstname").value = lastname[0];
				}
				
				var temp = eExempt[id].toUpperCase();
				if(temp == "EXEMPT"){
					document.getElementById("exempt").checked = true;
				}
				else{
					document.getElementById("exempt").checked = false;					
				}
				temp = eRecap[id].toUpperCase();
				if(temp == "YES"){
					document.getElementById("recap").checked = true;
				}
				else{
					document.getElementById("recap").checked = false;					
				}
			}
			
			function ChangeAction(sender){
				if(sender.value == "add"){
					document.getElementById("employee_form").reset();
					document.getElementById(sender.id).checked = true;
					document.getElementById("nameDrop").value = "";
					document.getElementById("firstname").focus();
					document.getElementById("button").value = "Add Employee";
				}
				else{
					document.getElementById("button").value = "Edit Employee";
				}
			}
			
			function Explain(sender){
				var explanation;
				if(sender.name == "status"){
					explanation = "<ul><li>Must be 'Active' to be in the drop down.</li></ul>";
				}
				else if(sender.name == "reportingto"){
					explanation = "<ul><li>Put email address of who you want a copy of the recap receipt to go to.</li><li>Leave blank to send to nobody.</li></ul>";
				}
				else if(sender.name == "company"){
					explanation = "<ul><li>Enter TSI if they are a regular TSI employee.</li><li>Enter name of company if a sub or other.</li></ul>";
				}
				else if(sender.name == "daysmissing"){
					explanation = "<ul><li>This is the number of consecutive days that they have not had hours reported for</li>";
					explanation = explanation + "<li>After 31, they are considered not to be working and the status is set to 'Expired'</li>";
					explanation = explanation + "<li>Leave blank for a new employee</li></ul>";
				}
				else if(sender.name == "exempt"){
					explanation = "<ul><li>Check this box if the employee is salaried.</li></ul>";
				}
				else if(sender.name == "recap"){
					explanation = "<ul><li>Check this box if this employee is supposed to enter a daily recap.</li>";
					explanation = explanation + "<li>If this is checked, then they will get a reminder email and their name will be in the supervisor drop-down on the recap page.</li>";
					explanation = explanation + "<li>If this is checked, then an email address for the employee must be entered</li></ul>";
				}
				else{
					explanation = "";
				}
				
				document.getElementById("explain").innerHTML = explanation;
			}
			
			function validate(){
				var validationPassed = true;
				var errorMessage;
				var theID;
				
				document.getElementById("button").disabled = true;
				
				if(document.getElementById("firstname").value == ""){
					validationPassed = false;
					errorMessage = "Please enter the first name."
					theID = "firstname";
				}
				if(document.getElementById("lastname").value == ""){
					validationPassed = false;
					errorMessage = "Please enter the last name."
					theID = "lastname";
				}
				if(document.getElementById("status").value == ""){
					validationPassed = false;
					errorMessage = "Please enter the status."
					theID = "status";
				}
				if(document.getElementById("company").value == ""){
					validationPassed = false;
					errorMessage = "Please enter the company."
					theID = "company";
				}
				if(document.getElementById("recap").checked == true){
					if(document.getElementById("email").value == ""){
						validationPassed = false;
						errorMessage = "Please enter the email address."
						theID = "email";
					}
				}
				
				if(validationPassed == false){
					document.getElementById(theID).focus();
					alert(errorMessage);
					document.getElementById("button").disabled = false;
					return false;
				}
				else{
					document.getElementById("button").disabled = false;
					document.getElementById("id").disabled = false;
					document.forms["employee_form"].submit();
				}
				
				
			}
			
		</script>
	</head>
	<body>
		<?
			echo "<select id='nameDrop' onchange=Populate(this.value)>";
			echo "<option value='-1'>---Recap Submitter---</option>";
			foreach($recapNames as $i=>$value){
				echo "<option value='".$i."'>" . $value;
				if(strtoupper($status[$i]) != "ACTIVE"){
					echo " (". $status[$i] . ")";
				}
				echo "</option>\n";
			}
			echo "</select>";	
		
			echo "<select id='allNameDrop' onchange=Populate(this.value)>";
			echo "<option value='-1'>---Employee---</option>";
			for($i=1;$i<sizeof($allNames);$i++){
				echo "<option value='".$i."'>" . $allNames[$i];
				if(strtoupper($status[$i]) != "ACTIVE"){
					echo " (". $status[$i] . ")";
				}
				echo "</option>\n";
			}
			echo "</select><BR>";
			
			echo "<select>";
			echo "<option>---Date---</option>";
			for($i=0; $i<sizeof($distinctDates);$i++){
				echo "<option>" . $distinctDates[$i] . "</option>";
			}
			echo "</select>";	
			
			echo "<select>";
			echo "<option>---Job---</option>";
			foreach($jobList as $jnum=>$jname){
				echo "<option>$jnum $jname</option>";
			}
			echo "</select>";
			
			//! TO DO: pass selected items as the sql query
		?>
		
		<form name="employee_form" id="employee_form" action="employees.php" method="post">
		
			<p id="description"></p><br>
			
			<input type="button" id="button" name="button" onclick="validate()" value="Add Employee"></button>
		
		</form>
		
		
		
	</body>
</html>