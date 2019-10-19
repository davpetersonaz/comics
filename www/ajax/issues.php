<?php if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php } ?>

<?php
include_once('../../config.php');
//logDebug('ajax/issues REQUEST: '.var_export($_REQUEST, true)); //NOTE: this is very verbose

//TODO: maybe make this work per-page, or just default each page however i want, and then add these as user-defined settings.
//if(!isset($_SESSION['table_length']['home']) || $_SESSION['table_length']['home'] != $_REQUEST['length']){
//	$_SESSION['table_length']['home'] = $_REQUEST['length'];
//}

$collections = $db->getAllCollections();
$series = $db->getAllSeries();

$collectionChoice = (isset($_GET['coll']) ? intval($_GET['coll']) : false);
$seriesChoice = (isset($_GET['ser']) ? intval($_GET['ser']) : false);

// Array of database columns which should be read and sent back to DataTables.
// Use a space where you want to insert a non-database field (for example a counter or static image)
$columns = array('c.image_thumb', 'l.collection_name', 's.series_name', 'c.issue', 'c.chrono_index', 
					'c.cover_date', 'c.grade', 'c.comicvine_issue_id', 'c.notes', '', 
					'c.image_full', 'l.collection_id', 'c.series_id', 'c.issue_id', 
					'c.user_id', 's.comicvine_series_id', 's.comicvine_series_full', 's.volume', 's.year');
$table = 'comics c';
$indexColumn = 'issue_id';
$bindParams = array();

//search filtering
//NOTE this does not match the built-in DataTables filtering which does it word by word on any field. 
//It's possible to do here, but concerned about efficiency on very large tables, and MySQL's regex functionality is very limited
$where = "";
if(!empty($_REQUEST['search']['value'])){
//	logDebug('REQUEST[search][value]: '.$_REQUEST['search']['value']);
	$where = "WHERE (";
	for ($i=0; $i<count($columns); $i++){
		if(!empty(trim($columns[$i])) && strpos($columns[$i], 'MAX(') === false){
			$asPos = strpos($columns[$i], ' AS ');
			$column = ($asPos !== false ? substr($columns[$i], 0, $asPos) : $columns[$i]);
			$bindParam = substr($column, 2);//remove the table-label and the dot
			$bindParams["{$bindParam}"] = "%{$_REQUEST['search']['value']}%";
			$where .= "{$column} LIKE :{$bindParam} OR ";
		}
	}
	$where = substr_replace($where, "", -3).')';//remove the ending OR
}

//individual column sorting
for($i=0; $i<count($columns); $i++){
	if(!empty($_REQUEST['columns'][$i]['searchable']) && !empty($_REQUEST['columns'][$i]['search']['value'])){
//		logDebug('REQUEST[columns]['.$i.']: '.var_export($_REQUEST['columns'][$i], true));
		$asPos = strpos($columns[$i], ' AS ');//remove the column alias
		$column = ($asPos !== false ? substr($columns[$i], 0, $asPos) : $columns[$i]);
		$bindParam = substr($column, 2);//remove the table-label and the dot
		$where .= (empty($where) ? "WHERE " : " AND ");
		$bindParams["{$bindParam}"] = "%{$_REQUEST['columns'][$i]['search']['value']}%";
		$where .= "{$column} LIKE :{$bindParam} ";
	}
}

//logDebug('sWhere: '.$where);
$where .= (empty($where) ? 'WHERE ' : ' AND ')."c.user_id=".$_SESSION['siteUser'];
$where .= ($collectionChoice ? " AND c.collection_id={$collectionChoice}" : '');
$where .= ($seriesChoice ? " AND c.series_id={$seriesChoice}" : '');

//ordering
$order = '';
if (isset($_REQUEST['order'])){
//	logDebug('REQUEST[order]: '.var_export($_REQUEST['order'], true));
	for ($i=0; $i<count($_REQUEST['order']); $i++){
//		logDebug('REQUEST[order]['.$i.'][column]: '.var_export($_REQUEST['order'][$i]['column'], true));
		$order .= (empty($order) ? 'ORDER BY ' : ', ');
		$sortingColumn = $columns[intval($_REQUEST['order'][$i]['column'])];
//		$sortingColumn = substr($sortingColumn, 2);//remove the table-label and the dot
		$asPos = strpos($sortingColumn, ' AS ');//remove the column alias
		$orderColumn = ($asPos !== false ? substr($sortingColumn, 0, $asPos) : $sortingColumn);
		$direction = (strtolower($_REQUEST['order'][$i]['dir']) === 'asc' || strtolower($_REQUEST['order'][$i]['dir']) === 'desc' ? strtoupper($_REQUEST['order'][$i]['dir']) : 'DESC');
		$order .= $orderColumn.' '.$direction;
	}
}
//logDebug('sOrder: '.$order);
if(empty($order)){ $order = 'ORDER BY s.name ASC, c.volume ASC, c.issue, g.position ASC'; }

//paging
$limit = "";
if(isset($_REQUEST['start']) && $_REQUEST['length'] != '-1'){
	$limit = "LIMIT ".intval($_REQUEST['start']).", ".intval($_REQUEST['length']);
}

$query = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $columns))."
		FROM {$table}
		LEFT JOIN series s ON s.series_id=c.series_id
		LEFT JOIN collections l ON l.collection_id=c.collection_id
		LEFT JOIN grades g ON c.grade=g.position
		$where
		$order
		$limit
";
//logDebug('bindParam: '.var_export($bindParams, true));
//logDebug('size of bindParams: '.count($bindParams));
Func::logQueryAndValues($query, $bindParams, 'ajax/issues');
$timer = microtime(true);
$mainQueryResult = $db->genericSelect($query, $bindParams);
//logDebug('mainQueryResult: '.var_export($mainQueryResult, true));

$total = $db->selectFoundRows();
//logDebug('SELECT FOUND_ROWS(): '.var_export($total, true));
//logDebug('query complete, took '.(number_format((microtime(true)-$timer)*1000, 2)).' ms');

//total dataset length
$resultTotal = $db->selectCountFromTable($indexColumn, $table);
logDebug('aResultTotal: '.var_export($resultTotal, true));
$totalTableRows = $resultTotal[0]['rowcount'];
$datatablerows = array();

//finally, format the actual results
foreach($mainQueryResult as $row){
//	logDebug('row: '.var_export($row, true));
	$image_div = '';
	if($row['image_thumb'] && $row['image_full']){
		$image_div =	"<div id='picture{$row['issue_id']}' class='picture'>".
							"<a class='small' href='#nogo' title='small image'>".
								"<img src='{$row['image_thumb']}' class='img-responsive'>".
								"<img src='{$row['image_full']}' class='large popup-on-hover'>".
							"</a>".
						"</div>";
	}
	$collection_div = "<select id='collection{$row['issue_id']}' class='collection'>";
	foreach($collections as $collection){
		$selected = (intval($collection['collection_id']) === intval($row['collection_id']) ? ' selected' : '');
		$collection_div .= "<option value='{$collection['collection_id']}' {$selected}>{$collection['collection_name']}</option>";
	}
	$collection_div .= "</select>";
	$series_div = "<select id='series{$row['issue_id']}' class='series'>";
	foreach($series as $serie){
		$selected = (intval($serie['series_id']) === intval($row['series_id']) ? ' selected' : '');
		$series_div .= "<option value='{$serie['series_id']}' {$selected}>".Series::getDisplayTextStatic($serie['series_name'], $serie['volume'], $serie['year'])."</option>";
	}
	$series_div .= "</select>";
	$issue_div = "<input type='text' class='issue' id='issue{$row['issue_id']}' value='".Func::trimFloat($row['issue'])."'/>";
	$chrono = Func::trimFloat($row['chrono_index']);
	$chrono_div = "<input type='text' class='chrono' id='chrono{$row['issue_id']}' value='".($chrono ? $chrono : '')."'/>";
	$coverdate_div = "<span title='{$row['cover_date']}'>".Func::makeDisplayDate($row['cover_date']).'</span>';
	$grade_div = "<select id='grade{$row['issue_id']}' class='grade'>";
	foreach($grades->getAllGrades() as $grade_array){
//		logDebug('grade_array: '.var_export($grade_array, true));
		$selected = (intval($grade_array['position']) === intval($row['grade']) ? ' selected' : '');
		$grade_div .= "<option value='{$grade_array['position']}' title='{$grade_array['short_desc']}' {$selected}>{$grade_array['grade_name']}</option>";
	}
	$grade_div .= "</select>";
	$comicvine_issue_id_div = "<span id='comicvine{$row['issue_id']}' class='comicvine-link' data-comicvine-issue-id='{$row['comicvine_issue_id']}'>{$row['comicvine_issue_id']}</span>";
	$notes_div = "<input type='text' class='notes' id='notes{$row['issue_id']}' value='{$row['notes']}'/>";
	$issueDisplayText = "{$row['series_name']} ".($row['volume'] > 1 ? "vol.{$row['volume']}" : '')."({$row['year']}) #{$row['issue']}";
	$delete_div = "<span class='delete' id='delete{$row['issue_id']}' data-issue-text='{$issueDisplayText}'><i class='fa fa-times'></i></span>";

	$datatablerows[] = array(
		$image_div,
		$collection_div,
		$series_div,
		$issue_div, 
		$chrono_div,
		$coverdate_div,
		$grade_div, 
		$comicvine_issue_id_div,
		$notes_div,
		$delete_div
	);
}	

$output = array(
	"iTotalRecords" => $totalTableRows,//all the rows in the table
	"iTotalDisplayRecords" => $total[0][0],//all the rows in the filtered dataset
	"aaData" => $datatablerows//the rows in this limited window of the filtered dataset
);

logDebug('total rows in table: '.$output['iTotalRecords']);
logDebug('rows in filtered dataset: '.$output['iTotalDisplayRecords']);
logDebug('rows in displayed subset: '.count($datatablerows));

$json_return = json_encode($output, JSON_UNESCAPED_SLASHES);
//logDebug('json return: '.$json_return);
echo $json_return;
exit;