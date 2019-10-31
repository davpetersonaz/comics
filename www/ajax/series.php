<?php
include_once('../../config.php');
logDebug('ajax/series REQUEST: '.var_export($_REQUEST, true)); //NOTE: this is very verbose

//TODO: maybe make this work per-page, or just default each page however i want, and then add these as user-defined settings.
//if(!isset($_SESSION['table_length']['home']) || $_SESSION['table_length']['home'] != $_REQUEST['length']){
//	$_SESSION['table_length']['home'] = $_REQUEST['length'];
//}

$collections = $db->getAllCollections();

$seriesChoice = (isset($_GET['ser']) ? intval($_GET['ser']) : false);

// Array of database columns which should be read and sent back to DataTables.
// Use a space where you want to insert a non-database field (for example a counter or static image)
$columns = array('s.image_thumb', 's.series_id', 's.series_name', 's.volume', 's.year', 's.publisher', 's.first_issue', 
					's.last_issue', 's.comicvine_series_id', 's.comicvine_series_full', 'COUNT(i.issue_id) AS issue_count', 's.user_id', 's.image_full');
$table = 'series s';
$indexColumn = 'series_id';
$bindParams = array();

//search filtering
//NOTE this does not match the built-in DataTables filtering which does it word by word on any field. 
//It's possible to do here, but concerned about efficiency on very large tables, and MySQL's regex functionality is very limited
$where = "";
if(!empty($_REQUEST['search']['value'])){
//	logDebug('REQUEST[search][value]: '.$_REQUEST['search']['value']);
	$where = "WHERE (";
	for ($i=0; $i<count($columns); $i++){
		if(!empty(trim($columns[$i])) && isset($_REQUEST['columns'][$i]) && $_REQUEST['columns'][$i]['searchable'] === 'true' && strpos($columns[$i], 'MAX(') === false){
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
$where .= (empty($where) ? 'WHERE ' : ' AND ')."s.user_id=".$_SESSION['siteUser'];
$where .= ($seriesChoice ? " AND s.series_id={$seriesChoice}" : '');

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
if(empty($order)){ $order = 'ORDER BY s.name ASC, s.year ASC, s.volume ASC'; }

//paging
$limit = "";
if(isset($_REQUEST['start']) && $_REQUEST['length'] != '-1'){
	$limit = "LIMIT ".intval($_REQUEST['start']).", ".intval($_REQUEST['length']);
}

$query = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $columns))."
		FROM {$table}
		LEFT JOIN comics i USING (series_id)
		{$where}
		GROUP BY s.series_id
		{$order}
		{$limit}
";
//logDebug('bindParam: '.var_export($bindParams, true));
//logDebug('size of bindParams: '.count($bindParams));
Func::logQueryAndValues($query, $bindParams, 'ajax/series');
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
		$image_div =	"<div id='picture{$row['series_id']}' class='picture'>".
							"<a href='/series_details?id={$row['series_id']}' class='small' title='small image'>".
								"<img src='{$row['image_thumb']}' class='img-responsive'>".
								"<img src='{$row['image_full']}' class='large popup-on-hover'>".
							"</a>".
						"</div>";
	}
	$id_div = $row['series_id'];
	$series_div = "<input type='text' class='series_name' id='series{$row['series_id']}' value=\"{$row['series_name']}\">";
	$volume_div = "<input type='text' class='volume' id='volume{$row['series_id']}' value=\"{$row['volume']}\">";
	$year_div = $row['year'];
	$publisher_div = $row['publisher'];
	$first_div = $row['first_issue'];
	$last_div = $row['last_issue'];
	$comicvine_id_div = $row['comicvine_series_id'];
	$comicvine_full_div = "<span id='comicvine{$row['comicvine_series_full']}' class='comicvine-link'>{$row['comicvine_series_full']}</span>";
	$usage_div = "<a href='/issues?ser={$row['series_id']}'>{$row['issue_count']}</a>";
	$delete_div = "<span class='delete' id='delete{$row['series_id']}' data-series-text=\"".Series::getDisplayTextStatic($row['series_name'], $row['volume'], $row['year'])."\"><i class='fa fa-times'></i></span>";
	$image_full_div = $row['image_full'];

	$datatablerows[] = array(
		$image_div,
		$id_div,
		$series_div,
		$volume_div,
		$year_div,
		$publisher_div,
		$first_div,
		$last_div,
		$comicvine_id_div,
		$comicvine_full_div,
		$usage_div,
		$delete_div,
		$image_full_div
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