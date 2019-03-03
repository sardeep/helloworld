<?php require_once('header.php');?>
<?php require_once('leftpanel.php');?>

<?php
$query	= "select patient_abpm.id, patient_abpm.patid, doctor, measureddate, fname, lname, pubpid from patient_abpm left join patient_data on patient_data.pid = patient_abpm.patid order by patient_abpm.id desc";
$docres = sqlStatement($query);
while ($row = sqlFetchArray($docres)) {
	$arrabpmreport[$row['id']]['msdt'] 	= $row['measureddate'];
	$arrabpmreport[$row['id']]['pid'] 	= $row['patid'];
	$arrabpmreport[$row['id']]['doc'] 	= $row['doctor'];	
	$arrabpmreport[$row['id']]['name']   	= $row['fname'].' '.$row['lname'];
	$arrabpmreport[$row['id']]['pubpid']  	= $row['pubpid'];
}
 ?>

      <div id="content">
        <div class="outer">
          <div class="inner bg-light lter">

            <!--Begin Datatables-->
            <div class="row">
              <div class="col-lg-12">
                <div class="box">
                  <header>
                    <div class="icons">
                      <i class="fa fa-table"></i>
                    </div>
                    <h5>ABPM Report List(s)</h5>
                  </header>
                  <div id="collapse4" class="body">
                    <table id="dataTable" class="table table-bordered table-condensed table-hover table-striped">
                      <thead>
                        <tr>
			  <th>Nmae</th>
			  <th>UHID</th>
			  <th>Doctor</th>
			  <th>Measured Date</th>
			  <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
			<?php foreach($arrabpmreport as $did=>$repdet){
				$arrdocname = $db->getRefnameById($repdet['doc']);
				$varspec    = ($arrdocname['specilization']!='')?' ('.$arrdocname['specilization'].')':'';	
				$vardocname = $arrdocname['name'].$varspec;
			?>
			  <tr>
                            <td><a href="patientview.php?pid=<?php echo base64_encode($repdet['pid'])?>" target="_blank"><?php echo $repdet['name']?></a></td>
                            <td><?php echo $repdet['pubpid']?></td>
                            <td><?php echo $vardocname?></td>
                            <td><?php echo date('d-m-Y h:i a', strtotime($repdet['msdt']))?></td>
			   <td><a class="btn btn-primary btn-xs btn-grad" href="abpm_report.php?pid=<?php echo $repdet['pid']?>&abpm=<?php echo $did?>" target="_blank">Print</a></td> 
                         </tr>
			 <?php }?>
		      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div><!-- /.row -->

            <!--End Datatables-->
          </div><!-- /.inner -->
        </div><!-- /.outer -->
      </div><!-- /#content -->
    </div><!-- /#wrap -->

 <?php require_once('footer.php');?>
<script>
$(function() {
	Metis.MetisTable();
	//Metis.metisSortable();
});
</script>
