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
		//this will be tricky
		//if a new series, then retrieve all issues in that series from comicvine
		//then keep track of each issue i have, and array_reverse against comicvine's all-issues
		//and print out the remainder when a new series is encountered.
		
		//may want to organize by collection!!!! (or not? -- which series' would be relevant? not Defenders crossovers in the Avengers collection, for example)
	}
	
	public function getReport(){
		return $this->report;
	}
	
	public function isValid(){
		return $this->type !== false;
	}
	
	public function __construct(DB $db, $type=false){
		$this->db = $db;
		if(in_array($type, array('summary', 'detailed', 'missing'))){
			$this->type = $type;
			$this->createReport();
		}
	}

	protected $db = false;
	private $issues = array();
	private $report = '';
	private $type = false;
}