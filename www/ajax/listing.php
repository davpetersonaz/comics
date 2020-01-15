<?php
include_once('../../config.php');
//logDebug('ajax/listing REQUEST: '.var_export($_REQUEST, true)); //NOTE: this is very verbose

if(!isset($_SESSION['table_length']['home']) || $_SESSION['table_length']['home'] != $_REQUEST['length']){
	$_SESSION['table_length']['home'] = $_REQUEST['length'];
}

// Array of database columns which should be read and sent back to DataTables.
// Use a space where you want to insert a non-database field (for example a counter or static image)
$columns = array('c.image_thumb', 'l.collection_name', 's.series_name', 's.volume', 'c.issue', 'c.cover_date', 'c.grade', 
					'c.issue_id', 'l.collection_id', 'c.series_id', 's.comicvine_series_id', 
					's.comicvine_series_full', 'c.image_full', 'g.grade_name', 'g.short_desc', 'c.user_id');
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
$where .= (empty($where) ? 'WHERE ' : ' AND ')."c.user_id=".$_SESSION['siteuser'];

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
if(empty($order)){ $order = 'ORDER BY c.collection_id ASC, s.name ASC, c.volume ASC, c.issue ASC'; }//want to add 'grade', but not sure how to order it

//paging
$limit = "";
if(isset($_REQUEST['start']) && $_REQUEST['length'] != '-1'){
	$limit = "LIMIT ".intval($_REQUEST['start']).", ".intval($_REQUEST['length']);
}

$query = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $columns))."
		FROM {$table}
		LEFT JOIN series s USING (series_id)
		LEFT JOIN collections l ON l.collection_id=c.collection_id
		LEFT JOIN grades g ON c.grade=g.position
		$where
		$order
		$limit
";
//logDebug('bindParam: '.var_export($bindParams, true));
//logDebug('size of bindParams: '.count($bindParams));
Func::logQueryAndValues($query, $bindParams, 'ajax/listing');
$timer = microtime(true);
$mainQueryResult = $db->genericSelect($query, $bindParams);
//logDebug('mainQueryResult: '.var_export($mainQueryResult, true));

$total = $db->selectFoundRows();
//logDebug('SELECT FOUND_ROWS(): '.var_export($total, true));
//logDebug('query complete, took '.(number_format((microtime(true)-$timer)*1000, 2)).' ms');

//total dataset length
$resultTotal = $db->selectCountFromTable($indexColumn, $table);
//logDebug('aResultTotal: '.var_export($resultTotal, true));
$totalTableRows = $resultTotal[0]['rowcount'];
$datatablerows = array();

//finally, format the actual results
foreach($mainQueryResult as $row){
//	logDebug('row: '.var_export($row, true));
	$image_div = '';
	if($row['image_thumb'] && $row['image_full']){
		$image_div =	"<div class='picture'>".
							"<a class='small' href='#nogo' title='small image'>".
								"<img src='{$row['image_thumb']}' class='img-responsive'>".
								"<img src='{$row['image_full']}' class='large popup-on-hover'>".
							"</a>".
						"</div>";
	}
	$coverdate = '';
	if($row['cover_date'] > 0){
		$coverdate = new DateTime("{$row['cover_date']}");
		$coverdate = ($coverdate->format('Y-m-d 00:00:00'));
	}
	$datatablerows[] = array(
		$image_div,
		($row['collection_name'] ? $row['collection_name'] : ''), 
		($row['series_name'] ? $row['series_name'] : ''), 
		$row['volume'], 
		Func::fancifyIssueNumber($row['issue']),
		$coverdate, //format using javascript
		($row['grade'] ? $row['grade'] : ''), 
		($row['issue_id'] ? $row['issue_id'] : ''), 
		($row['collection_id'] ? $row['collection_id'] : ''), 
		($row['series_id'] ? $row['series_id'] : ''),
		($row['comicvine_series_id'] ? $row['comicvine_series_id'] : ''),
		($row['comicvine_series_full'] ? $row['comicvine_series_full'] : ''),
		($row['grade_name'] ? $row['grade_name'] : ''),
		($row['short_desc'] ? $row['short_desc'] : '')
	);
}	

$output = array(
	"iTotalRecords" => $totalTableRows,//all the rows in the table
	"iTotalDisplayRecords" => $total[0][0],//all the rows in the filtered dataset
	"aaData" => $datatablerows//the rows in this limited window of the filtered dataset
);

//logDebug('total rows in table: '.$output['iTotalRecords']);
//logDebug('rows in filtered dataset: '.$output['iTotalDisplayRecords']);
//logDebug('rows in displayed subset: '.count($datatablerows));

$json_return = json_encode($output, JSON_UNESCAPED_SLASHES);
//logDebug('json return: '.$json_return);
echo $json_return;
exit;