<?php
//TODO: might have to put the report into a word doc that is already formatted with the right number of columns
//https://github.com/PHPOffice/PHPWord  &  https://blog.mayflower.de/6699-phpword-create-documents.html
//  or just ...
//https://webc0heatsheet.com/php/create_word_excel_csv_files_with_php.php

$printclass = new PrintClass($db, $_GET['type']);
$report = $printclass->getReport();
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark print-page'>Print</button>
</div>

<div class="print-listings">
	<?php echo $report; ?>
</div>

<script>
$(document).ready(function(){
	$('.print-page').on('click', function(){
		window.print();
	});
});
</script>
