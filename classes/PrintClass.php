<?php
class PrintClass{
	
	private function createReport(){
		$this->issues = $this->db->getAllIssues();
		if($this->type === 'summary'){
			$this->createSummaryReport();
		}elseif($this->type === 'detailed'){
			$this->createDetailedReport();
		}elseif($this->type === 'missing'){
			$this->createMissingReport();
		}
	}
	
	private function createSummaryReport(){
		$this->report = '';
		$previousIssue = $previousSeries = $notWritten = false;
		$previousSeriesIssues = array();
		foreach($this->issues as $issue){
//			logDebug('processing issue: '.var_export($issue, true));
//			logDebug("processing series [{$issue['series_name']}] issue [{$issue['issue']}]");
			$issue_series_id = intval($issue['series_id']);
			$issue_number = Func::fancifyIssueNumber($issue['issue']);
			$issue_number = (floatval($issue_number) === $issue_number ? number_format(floatval($issue_number)) : $issue_number);
			if($issue_series_id !== $previousSeries){
				//new series -- finish the previous
				if($previousSeries !== false){
					if(substr($this->report, strlen($this->report)-1) === '-'){
						//fill in the last issue number from the previous series
						$this->report .= $previousIssue;
						$previousSeriesIssues[] = $previousIssue;
					}
					$missingIssues = Series::getMissingIssuesStatic($this->db, $previousSeries, $previousSeriesIssues);
					if(empty($missingIssues)){
						$this->report .= " (all)";
					}
					$this->report .= "<br />";
					$previousSeriesIssues = array();
				}
				//start the new series
				$volume = (intval($issue['volume']) === 1 ? '' : ' vol.'.$issue['volume']);
				$this->report .= "<b>{$issue['series_name']}</b>{$volume} ({$issue['year']}): ";
				//and add the (first) issue
				$this->report .= $issue_number;
				$previousSeriesIssues[] = $issue_number;
				$notWritten = false;
			}else//same series
			if($issue_number !== $previousIssue){
				//new issue
//				logDebug("intval(issue_number): ".var_export(intval($issue_number), true));
				if(is_numeric($issue_number) && intval($issue_number) == $issue_number && $issue_number - 1 == $previousIssue){
					//consecutive issues, add the dash if its not already in the report
					if(substr($this->report, strlen($this->report)-1) !== '-'){
						$this->report .= '-';
						$notWritten = true;
					}
					$previousSeriesIssues[] = $issue_number;
				}else{
					//non-consecutive issues, or issues like: 1/2, infinity, decimal issues, etc
					if(substr($this->report, strlen($this->report)-1) === '-'){
						//fill in the last issue number
						$this->report .= $previousIssue;
						$previousSeriesIssues[] = $previousIssue;
					}
					$this->report .= ','.$issue_number;
					$previousSeriesIssues[] = $issue_number;
					$notWritten = false;
				}
			}
			$previousSeries = $issue_series_id;
			$previousIssue = $issue_number;
		}
		//just in case we were using a dash as we hit the last issue in the array, gotta tack on the previous issue number
		if($notWritten){
			$this->report .= $previousIssue;
			$missingIssues = Series::getMissingIssuesStatic($this->db, $previousSeries, $previousSeriesIssues);
			if(empty($missingIssues)){
				$this->report .= " (all)";
			}
		}
		$this->report .= "<br />";
	}
	
	private function createDetailedReport(){
		$this->report = '';
		$currentSeries = false;
		foreach($this->issues as $issue){
//			logDebug('processing issue: '.var_export($issue, true));
			if($issue['series_id'] !== $currentSeries){
				if($currentSeries !== false){
					$this->report .= "<br />";
				}
				$this->report .= "<b>{$issue['series_name']}</b><br />";
				$currentSeries = $issue['series_id'];
			}
			$issue_number = intval($issue['issue']);
			$volume = (intval($issue['volume']) === 1 ? '' : ' vol.'.$issue['volume']);
			if($issue['cover_date']){
				$issue_date = date_create_from_format('Y-m-d', $issue['cover_date']);
				$issue_date = ', '.date_format($issue_date, 'M Y');
			}else{
				$issue_date = '';
			}
			$notes = ($issue['notes'] ? ', '.$issue['notes'] : '');
			$this->report .= "{$issue['series_name']}{$volume} #{$issue_number}{$issue_date} - {$issue['abbr']}{$notes}<br />";
		}
	}
	
	private function createMissingReport(){
		$this->report = '';
		$previousIssue = false;
		$have_issues = array();
		foreach($this->issues as $issue){
			$issue_series_id = intval($issue['series_id']);
			$issue_number = Func::fancifyIssueNumber($issue['issue']);
//			logDebug("processing series [{$issue['series_name']}][{$issue_series_id}] issue [{$issue_number}]");
			//new series,  process the previous series
			if($previousIssue !== false && $issue_series_id !== intval($previousIssue['series_id'])){
				$this->processPreviousSeriesForMissingReport($have_issues, $previousIssue);
				//and now start the new series
				$have_issues = array();
			}
			$have_issues[] = $issue_number;
			$previousIssue = $issue;
		}
		//finish off the last series
		$this->processPreviousSeriesForMissingReport($have_issues, $previousIssue);
		logDebug('createMissingReport complete');
	}

	private function processPreviousSeriesForMissingReport($have_issues, $previousIssue){
		//retrieve all issues in that series from comicvine
//		logDebug("get all issues for [{$previousIssue['series_name']}][{$previousIssue['series_id']}]");
		$need_issues = Series::getMissingIssuesStatic($this->db, $previousIssue['series_id'], $have_issues);
//		logDebug('need_issues: '.implode(',', $need_issues));
		//don't report it if none of the issues are needed
		$notWritten = false;
		if($need_issues){
			$volume = (intval($previousIssue['volume']) === 1 ? '' : ' vol.'.$previousIssue['volume']);
			$this->report .= "<b>{$previousIssue['series_name']}</b>{$volume} ({$previousIssue['year']}): ";
			$prevIsh = false;
			foreach($need_issues as $issuenum){
				if($prevIsh === false){
					$this->report .= $issuenum;
					$notWritten = false;
				}elseif(is_numeric($issuenum) && intval($issuenum) == $issuenum && $issuenum - 1 == $prevIsh){
					//consecutive issues, add the dash if its not already in the report
					if(substr($this->report, strlen($this->report)-1) !== '-'){
						$this->report .= '-';
						$notWritten = true;
					}
				}else{
					//non-consecutive issues, or issues like: 1/2, infinity, decimal issues, etc
					if(substr($this->report, strlen($this->report)-1) === '-'){
						//fill in the last issue number
						$this->report .= $prevIsh;
					}
					$this->report .= ','.$issuenum;
					$notWritten = false;
				}
				$prevIsh = $issuenum;
			}
			if($notWritten){
				$this->report .= $prevIsh;
			}
			$this->report .= "<br />";
		}
	}
	
	public function getReport(){
		return $this->report;
	}
	
	public function isValid(){
		return $this->type !== false;
	}
	
	public function __construct(DB $db, Curl $curl, $type=false){
		$this->db = $db;
		$this->curl = $curl;
		if(in_array($type, array('summary', 'detailed', 'missing'))){
			$this->type = $type;
			$this->createReport();
		}
	}

	protected $db = false;
	protected $curl = false;
	private $issues = array();
	private $report = '';
	private $type = false;
}