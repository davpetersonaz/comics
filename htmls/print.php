<?php
$printclass = new PrintClass($db, $_GET['type']);
$report = $printclass->getReport();
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark print-page'>Print</button>
</div>

<?php echo $report; ?>

<script>
$(document).ready(function(){
	$('.print-page').on('click', function(){
		window.print();
	});
});
</script>
