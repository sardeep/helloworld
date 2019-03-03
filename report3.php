<?php
require_once("../interface/globals_custom.php");
require_once("includes/db_functions.php");

$pid 		= $_GET['pid'];
$abpmid		= $_GET['abpm'];
$pdet 		= $db->getpatientdetials($pid);
$ambdet		= $db->getpatientambulatorydet($pid,$abpmid);
$abpmbarsysdet	= $db->fngetbarsysabpmreport($pid,$abpmid);;
$abpmbardiadet	= $db->fngetbardiaabpmreport($pid,$abpmid);
$abpmbarpuldet	= $db->fngetbarpulabpmreport($pid,$abpmid);
$docsql 		= sqlQuery("select doctor from patient_abpm where patid= $pid and id= $abpmid");

$primaryconsdoc	= $db->getRefnameById($docsql['doctor']);
$vardocspec	= ($primaryconsdoc['specilization']!='')?' ('.$primaryconsdoc['specilization'].')':'';
$primaryconsult	= 'Dr. '.$primaryconsdoc['name'].$vardocspec;
$referrerdoc	= $db->getRefnameById($pdet['referrer']);
$vardocspec	= ($referrerdoc['specilization']!='')?' ('.$referrerdoc['specilization'].')':'';
$refererdocname	= 'Dr. '.$referrerdoc['name'].$vardocspec;
$arravgin['patid'] = $pid;
$arravgin['abmpid'] = $abpmid;
$measureddate	= $db->fngetabpmdate($pid, $abpmid);
$field		= 'AVG(sys) as avgsys, AVG(dia) as avgdia';
$avgsysdia	= $db->getavgabpmreport($field,$arravgin);
$arravgin['today'] = $measureddate;//'2015-06-07';//date('Y-m-d');
$arravgin['tmrw'] = date($measureddate, time()+86400);
$arravgin['type'] = 'day';
$avgdaysysdia	= $db->getavgabpmreport($field,$arravgin);
$arravgin['type'] = 'eve';
$avgngtsysdia	= $db->getavgabpmreport($field,$arravgin);
$field		= 'sys, measuredtime';
$arravgin	= array();
$arravgin['patid'] = $pid;
$arrorder['order'] = 'sys desc';
$arrorder['limit'] = 1;
$arravgin['abmpid'] = $abpmid;
$maxsys		= $db->getavgabpmreport($field,$arravgin,$arrorder);
$arrorder['order'] = 'sys asc';
$arrorder['limit'] = 1;
$minsys		= $db->getavgabpmreport($field,$arravgin,$arrorder);
$field		= 'dia, measuredtime';
$arravgin	= array();
$arravgin['patid'] = $pid;
$arrorder['order'] = 'dia desc';
$arrorder['limit'] = 1;
$arravgin['abmpid'] = $abpmid;
$maxdia		= $db->getavgabpmreport($field,$arravgin,$arrorder);
$arrorder['order'] = 'dia asc';
$arrorder['limit'] = 1;
$mindia		= $db->getavgabpmreport($field,$arravgin,$arrorder);
//echo'<pre>';print_r($avgsysdia);print_r($avgdaysysdia);print_r($avgngtsysdia);print_r($maxsys);print_r($minsys);echo'</pre>';
$patname 	= $pdet['title'].' '.$pdet['fname'].' '.$pdet['lname']; 
$patextid	= $pdet['pubpid'];
function fnround($val,$rnd=1){
	return round($val,$rnd);
}

$arrambval	= $ambdet['ambval'];
$arrsys 	= $arrambval['sys'];
$arrdia 	= $arrambval['dia'];
$arrpr 		= $arrambval['pr'];
$arrtime 	= $arrambval['time'];
$curcnt		= $ambdet['curcnt'];
$arrsyscount	= $abpmbarsysdet['arrsyscount'];
$arrdiacount	= $abpmbardiadet['arrdiacount'];
$arrpulcount	= $abpmbarpuldet['arrpulcount'];
$arrsyspiecnt	= $ambdet['arrsyspiecnt'];
$arrdiapiecnt	= $ambdet['arrdiapiecnt'];
$arrpulpiecnt	= $ambdet['arrpulpiecnt'];
$arrpultime	= $arrambval['pulline'];

//ksort($arrsyscount);ksort($arrdiacount);ksort($arrpulcount);ksort($arrsyspiecnt);ksort($arrdiapiecnt);ksort($arrpulpiecnt);ksort($arrpulcount);
function fnconvertpercent($curcnt,$totcnt){
	return ($curcnt / $totcnt) * 100;
}
$syscnt = 0;$diacnt = 0;$pulcnt = $piesysnct = $piedianct = $piepulcnt = 0;
foreach($arrsyscount as $key=>$val){
	$arrsysperc[$key] = round(fnconvertpercent($val,$ambdet['totcnt']),2);
	$syscnt++;
}
//echo'<pre>';print_r($arrsysperc);echo'</pre>';
foreach($arrdiacount as $key=>$val){
	$arrdiaperc[$key] = round(fnconvertpercent($val,$ambdet['totcnt']),2);
	$diacnt++;
}
foreach($arrpulcount as $key=>$val){
	$arrpulperc[$key] = round(fnconvertpercent($val,$ambdet['totcnt']),2);
	$pulcnt++;
}
foreach($arrsyspiecnt as $key=>$val){
	$arrpiesysperc[$key] = round(fnconvertpercent($val,$ambdet['totcnt']),2);
	$piesysnct++;
}
foreach($arrdiapiecnt as $key=>$val){
	$arrpiediaperc[$key] = round(fnconvertpercent($val,$ambdet['totcnt']),2);
	$piedianct++;
}
foreach($arrpulpiecnt as $key=>$val){
	$arrpiepulperc[$key] = round(fnconvertpercent($val,$ambdet['totcnt']),2);
	$piepulcnt++;
}
//echo'<pre>';print_r($arrsysperc);print_r($arrdiaperc);echo'</pre>';
$mydate1 	= date('M d, Y',strtotime('2015-06-07'));
$mydate		= date('M d, Y', strtotime($mydate1 .' -1 day')).' 23:00:00';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>ABPM REPORT</title>
        <!--link rel="stylesheet" href="style.css" type="text/css"-->
	 <script>
           var chart;
           var chartData = [];
	   var givendate = '<?php echo $mydate?>';
	   var sys = '<?php echo json_encode($arrsys)?>';
	   sys = eval('('+sys+')');
	   var dia = '<?php echo json_encode($arrdia)?>';
	   dia = eval('('+dia+')');
	   var pulse = '<?php echo json_encode($arrpr)?>';
	   pulse = eval('('+pulse+')');
	   var time = '<?php echo json_encode($arrtime)?>';
	   time = eval('('+time+')');
	   var curcnt = '<?php echo $curcnt?>';
	   var arrpultime = '<?php echo json_encode($arrpultime)?>';
	   arrpultime  = eval('('+arrpultime+')');
	   var arrsysperc = '<?php echo json_encode($arrsysperc)?>';
	   arrsysperc = eval('('+arrsysperc+')');  
	   var syscnt = '<?php echo $syscnt?>';   
	   var arrdiaperc = '<?php echo json_encode($arrdiaperc)?>';
	   arrdiaperc = eval('('+arrdiaperc+')');  
	   var diacnt = '<?php echo $diacnt?>'; 
	   var arrpulperc = '<?php echo json_encode($arrpulperc)?>';
	   arrpulperc = eval('('+arrpulperc+')');  
	   var pulcnt = '<?php echo $pulcnt?>'; 
	   var arrpiesysperc = '<?php echo json_encode($arrpiesysperc)?>';
	   arrpiesysperc = eval('('+arrpiesysperc+')');  
	   var piesysnct = '<?php echo $piesysnct?>';   
	   var arrpiediaperc = '<?php echo json_encode($arrpiediaperc)?>';
	   arrpiediaperc = eval('('+arrpiediaperc+')');  
	   var piedianct = '<?php echo $piedianct?>'; 
	   var arrpiepulperc = '<?php echo json_encode($arrpiepulperc)?>';
	   arrpiepulperc = eval('('+arrpiepulperc+')');  
	   var piepulcnt = '<?php echo $piepulcnt?>';        
        </script>
	<script src="assets/js/jquery.2.1.3.min.js"></script>
        <script src="amcharts/amcharts.js"  type="text/javascript"></script>
        <script src="amcharts/serial.js"    type="text/javascript"></script>
        <script src="amcharts/pie.js"       type="text/javascript"></script>
	<script src="assets/js/sysdiajs.js" type="text/javascript"></script>
	<script src="assets/js/pulselinejs.js" type="text/javascript"></script>
	<script src="assets/js/barsysjs.js" type="text/javascript"></script>
	<script src="assets/js/bardiajs.js" type="text/javascript"></script>
	<script src="assets/js/barpuljs.js" type="text/javascript"></script>
	<script src="assets/js/piesysjs.js" type="text/javascript"></script>
	<script src="assets/js/piediajs.js" type="text/javascript"></script>
	<script src="assets/js/piepuljs.js" type="text/javascript"></script>
	<style>
		.chartdivs{width:100%;height:500px;float:left;}
		.chartdivs1{width:70%;height:500px;float:left;}
		.chartdivs2{width:70%;height:500px;float:left;}
		.pdl15{padding-left:15px;}
	</style>
    <!--<link rel="stylesheet" href="assets/css/bootstrap.min.css">
 <link rel="stylesheet" href="assets/css/main.min.css">-->
	<link href="assets/css/core.css" rel="stylesheet" media="screen" type="text/css" />
	<link href="assets/css/core.css" rel="stylesheet" media="print" type="text/css" />
	<script src="assets/js/jquery.PrintArea.js_4.js"></script>
	<script src="assets/js/core-print.js"></script>
	<style type="text/css" media="print">
	@page{
        size: auto;
        margin: 3mm;
     	}

</style>
    </head>

    <body>
	<div id="wrapper">
		<div id="content">
		<table align="center" width="100%" cellpadding="10">
			<tr>
			<td>
			<table align="center" border="0" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr><td align="center"><img src="assets/img/logo_front.gif" /></td></tr>
			<tr><td align="center"><font size="3px">Ambulatory Blood Pressure Report</font></td></tr>
			</table>
			</td>
			</tr>
			<tr>
			<td align="center">
			<table align="center" border="1" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td colspan="6"><h4>Patient Information</h4></td>
			</tr>
			<tr>
			<td>Patient Name</td><td><b><?php echo $patname; ?></b></td>
			<td>Date of birth</td><td><?php echo ($pdet['DOB']!='0000-00-00')?$pdet['DOB']:''?></td>
			<td>Age</td><td><?php echo ($pdet['age']>0)?$pdet['age']:''?></td>
			</tr>
			<tr>
			<td>UHID </td><td><b><?php echo $pdet['pubpid']?></b></td>
			<td>Sex  </td><td><?php echo $pdet['sex']?></td>
			<td>Contact No.</td><td><?php echo $pdet['phone_cell']?></td>
			</tr>
			<tr>
			<td>Primary Consultant </td><td><b><?php echo $primaryconsult?></b></td>
			<td colspan="3">Referred By  </td><td><b><?php echo $refererdocname?></b></td>
			</tr>
			</table>
			</td>
			</tr>
			<tr>
			<td align="center">

			<table align="center" border="1" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td>All BP Averages</td><td>:</td><td><?php echo fnround($avgsysdia['avgsys']).' / '.fnround($avgsysdia['avgdia'])?> mmHg</td>
			</tr>
			<tr>
			<td>Night BP Averages</td><td>:</td><td><?php echo fnround($avgngtsysdia['avgsys']).' / '.fnround($avgngtsysdia['avgdia'])?></td>

			</tr>
			</table>
			<br />
			<table align="center" border="0" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td>Max Sys</td><td>:</td><td><?php echo fnround($maxsys['sys'])?> mmHg</td>
			<td>On</td><td>:</td><td><?php echo date('d-m-Y H:i',strtotime($maxsys['measuredtime']))?></td>
			<td></td><td></td><td></td>
			</tr>
			<tr>
			<td>Min Sys</td><td>:</td><td><?php echo fnround($minsys['sys'])?> mmHg</td>
			<td>On</td><td>:</td><td><?php echo date('d-m-Y H:i',strtotime($minsys['measuredtime']))?></td>	
			<td></td><td></td><td></td>
			</tr>
			<tr>
			<td>Max Dia</td><td>:</td><td><?php echo fnround($maxdia['dia'])?> mmHg</td>
			<td>On</td><td>:</td><td><?php echo date('d-m-Y H:i',strtotime($maxdia['measuredtime']))?></td>	
			<td></td><td></td><td></td>
			</tr>
			<tr>
			<td>Min Dia</td><td>:</td><td><?php echo fnround($mindia['dia'])?> mmHg</td>
			<td>On</td><td>:</td><td><?php echo date('d-m-Y H:i',strtotime($mindia['measuredtime']))?></td>	
			<td></td><td></td><td></td>
			</tr>
			</table>
			</td>
			</tr>
			</table>
		
			<table align="center" border="1" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td><div id="chartdiv"    class="chartdivs" ></div></td>
			</tr>
			</table>
		<DIV style="page-break-after:always"></DIV><br /><br /><br />
			<table align="center" border="1" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td><div id="chartpulseline"    class="chartdivs" ></div></td>
			</tr>
			<tr>
			<td align="center"><div id="chartbarsys" class="chartdivs1" ></div></td>
			</tr>
			</table>
		<DIV style="page-break-after:always"></DIV>
			<table align="center" border="1" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td align="center"><div id="chartbardia" class="chartdivs2" ></div></td>
			</tr>
			</table>
		
			<table align="center" border="1" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td align="center"><div id="chartbarpul" class="chartdivs2" ></div></td>
			</tr>
			</table>
		<DIV style="page-break-after:always"></DIV>
			<table align="center" border="0" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td><div id="chartpiesys" class="chartdivs" ></div></td>
			</tr>
			<tr>
			<td align="center"><b>Systolic pie chart</b></td>
			</tr>
			<tr>
			<td><div id="chartpiedia" class="chartdivs" ></div></td>
			</tr>
			<tr>
			<td align="center"><b>Diastolic pie chart</b></td>
			</tr>
			</table>
		<DIV style="page-break-after:always"></DIV>
			<table align="center" border="0" style="border-collapse:collapse" width="100%" cellpadding="10">
			<tr>
			<td><div id="chartpiepul" class="chartdivs" ></div></td>
			</tr>
			<tr>
			<td align="center"><b>Pulse rate</b></td>
			</tr>
			</table>
		
		<DIV style="page-break-after:always"></DIV>
		<br /><br /><br />
			<center><font size="3"><b>Measured Data</b></font></center>
			<div ><b>*  MAP - Mean Arterial Pressure</b></div>
			<table align="center" border="1" style="border-collapse:collapse" width="70%" cellpadding="10">
			<th>SL.No</th><th>Measured time</th><th>SYSTOLIC</th><th>DIASTOLIC</th><th>PULSE</th><th>MAP</th><th>Comments</th>
			<?php
			$abpmRes 	= sqlStatement("SELECT * FROM patient_abpm_report where patid=$pid and abmpid = $abpmid");
			$k=1;
			while ($abpmRow = sqlFetchArray($abpmRes)) {
			?>
			<tr><td align="center"><?php echo $k++; ?></td><td align="center"><?php echo $abpmRow['measuredtime']; ?></td>
			<td align="center"><?php echo $abpmRow['sys']; ?></td><td align="center"><?php echo $abpmRow['dia']; ?></td>
			<td align="center"><?php echo $abpmRow['pr']; ?></td><td align="center"><?php echo $abpmRow['map']; ?></td><td align="center"><?php echo $abpmRow['comments']; ?></td></tr>			
			<?php
			} 
			?>
			</table>
			
		</table>
		</div>
		<a href="#" class="print" rel="content">Print</a>
	</div>
    </body>

</html>
