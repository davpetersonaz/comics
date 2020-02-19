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
		
		//TODO: AFTER CREATING SERIES-TABLE ALL-ISSUE-NUMBERS COLUMN, THEN USE IT TO DETERMINE IF (ALL) SHOULD BE ADDED TO THE SERIES (IF I HAVE ALL ISSUES)
		
		$this->report = '';
		$previousIssue = $previousSeries = false;
		foreach($this->issues as $issue){
//			logDebug('processing issue: '.var_export($issue, true));
			$issue_series_id = intval($issue['series_id']);
			$issue_number = intval($issue['issue']);
			if($issue_series_id !== $previousSeries){
				//new series
				if(substr($this->report, strlen($this->report)-1) === '-'){
					//fill in the last issue number from the previous series
					$this->report .= $previousIssue;
				}
				if($previousSeries !== false){
					$this->report .= "<br />";
				}
				$volume = (intval($issue['volume']) === 1 ? '' : ' vol.'.$issue['volume']);
				$this->report .= "<b>{$issue['series_name']}</b>{$volume} ({$issue['year']}): ";
				//and add the (first) issue
				$this->report .= $issue_number;
			}else//same series
			if($issue_number !== $previousIssue){
				//new issue
				if($issue_number - 1 === $previousIssue){
					//consecutive issues, add the dash if its not already in the report
					if(substr($this->report, strlen($this->report)-1) !== '-'){
						$this->report .= '-';
					}
				}else{
					//non-consecutive issues
					if(substr($this->report, strlen($this->report)-1) === '-'){
						//fill in the last issue number
						$this->report .= $previousIssue;
					}
					$this->report .= ','.$issue_number;
				}
			}
			$previousSeries = $issue_series_id;
			$previousIssue = $issue_number;
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
		
		//TODO: THIS AINT GONNA WORK, GOTTA ADD NEW COLUMN TO SERIES TABLE FOR COMMA-SEPARATED LIST OF ALL ISSUE NUMBERS IN THE SERIES
		
//		$this->report = '';
//		$previousIssue = false;
//		$have_issues = array();
//		foreach($this->issues as $issue){
//			$issue_series_id = intval($issue['series_id']);
//			$issue_number = intval($issue['issue']);
//			logDebug("processing series [{$issue['series_name']}][{$issue_series_id}] issue [{$issue_number}]");
//			//new series,  process the previous series
//			if($previousIssue !== false && $issue_series_id !== intval($previousIssue['series_id'])){
//				$this->processPreviousSeriesForMissingReport($have_issues, $previousIssue);
//				//and now start the new series
//				$have_issues = array();
//			}
//			$have_issues[] = $issue_number;
//			$previousIssue = $issue;
//		}
//		//finish off the last series
//		$this->processPreviousSeriesForMissingReport($have_issues, $previousIssue);
//		logDebug('createMissingReport complete');
	}

	private function processPreviousSeriesForMissingReport($have_issues, $previousIssue){
		//retrieve all issues in that series from comicvine
		logDebug("get all issues for [{$previousIssue['series_name']}][{$previousIssue['series_id']}]");
		$issues_in_series = $this->curl->getAllIssuesInSeries($previousIssue['comicvine_series_id']);
		$need_issues = array_diff($issues_in_series, $have_issues);
		logDebug('need_issues: '.var_export($need_issues, true));
		//don't report it if none of the issues are needed
		if($need_issues){
			$volume = (intval($previousIssue['volume']) === 1 ? '' : ' vol.'.$previousIssue['volume']);
			$this->report .= "<b>{$previousIssue['series_name']}</b>{$volume} ({$previousIssue['year']}): ";
			$prevIsh = false;
			foreach($need_issues as $issuenum){
				if($prevIsh === false){
					$this->report .= $issuenum;
				}elseif($issuenum - 1 === $prevIsh){
					//consecutive issues, add the dash if its not already in the report
					if(substr($this->report, strlen($this->report)-1) !== '-'){
						$this->report .= '-';
					}
				}else{
					//non-consecutive issues
					if(substr($this->report, strlen($this->report)-1) === '-'){
						//fill in the last issue number
						$this->report .= $prevIsh;
					}
					$this->report .= ','.$issuenum;
				}
				$prevIsh = $issuenum;
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