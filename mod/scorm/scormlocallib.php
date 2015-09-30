<?php

class scormlocallib {
	public $tincandata;
	
	private $tincanfields = [
		'cmi.exit',
		'cmi.core.exit',
		'cmi.score.raw',
		'cmi.core.score.raw',
		'cmi.score.scaled',
		'cmi.core.score.scaled',
		'cmi.score.max',
		'cmi.core.score.max',
		'cmi.score.min',
		'cmi.core.score.min',
		'cmi.core.lesson_status',
		'cmi.success_status',
		'cmi.completion_status',
// 		'cmi.interactions.n.learner_response',
// 		'cmi.interactions.n.student_response',
	];
	
	public function trackData ($element, $value)
	{
		//if element in fields
		if(in_array($element, $this->tincanfields)){
			//add to tincandata array
			$this->tincandata[$element] = $value;
		}
	}
	
	public function triggerScormEvents($eventData)
	{
		//if there are active $tincan statements, trigger event
		if(count($this->tincandata)){
			$events = [];
			foreach($this->tincandata as $element => $value){
				if(($element == 'cmi.core.lesson_status' || $element == 'cmi.success_status' || $element == 'cmi.completion_status') && $value == 'passed') {
					$events['passed'] = 'triggerScoPassed';
					if(!empty($events['scored'])){
						unset($events['scored']);
					}
					if(!empty($events['suspended'])){
						unset($events['suspended']);
					}
				}elseif(($element == 'cmi.core.lesson_status' || $element == 'cmi.success_status' || $element == 'cmi.completion_status') && $value == 'failed'){
					$events['failed'] = 'triggerScoFailed';
					if(!empty($events['scored'])){
						unset($events['scored']);
					}
					if(!empty($events['suspended'])){
						unset($events['suspended']);
					}
				}elseif(($element == 'cmi.core.lesson_status' || $element == 'cmi.success_status' || $element == 'cmi.completion_status') && $value == 'completed'){
					$events['completed'] = 'triggerScoCompleted';
					if(!empty($events['scored'])){
						unset($events['scored']);
					}
					if(!empty($events['suspended'])){
						unset($events['suspended']);
					}
				}elseif(($element == 'cmi.score.raw' || $element == 'cmi.core.score.raw' || $element == 'cmi.score.scaled' || $element == 'cmi.core.score.scaled') && (!isset($events['passed']) && !isset($events['failed']) && !isset($events['completed']))){
					$events['scored'] = 'triggerScoScored';
				}elseif(($element == 'cmi.exit' || $element == 'cmi.core.exit') && (!isset($events['passed']) && !isset($events['failed']) && !isset($events['completed']))){
					$events['suspended'] = 'triggerScoExited';
				}
			}
			foreach($events as $event){
				$this->$event($eventData);
			}
		}
	}
	
	// Events to trigger
	private function triggerScoPassed($eventData)
	{
		// Trigger a Sco passed event.
		$otherData = ['instanceid' => $eventData['scormid']];
		$otherData = $this->populateScores($otherData);
		$event = \mod_scorm\event\sco_passed::create(array(
				'objectid' => $eventData['scoid'],
				'context' => $eventData['context'],
				'other' => $otherData
		));
		$event->add_record_snapshot('course_modules', $eventData['cm']);
		$event->add_record_snapshot('scorm', $eventData['scorm']);
		$event->add_record_snapshot('scorm_scoes', $eventData['sco']);
		$event->trigger();
	}
	
	private function triggerScoFailed($eventData)
	{
		// Trigger a Sco failed event.
		$otherData = ['instanceid' => $eventData['scormid']];
		$otherData = $this->populateScores($otherData);
		$event = \mod_scorm\event\sco_failed::create(array(
				'objectid' => $eventData['scoid'],
				'context' => $eventData['context'],
				'other' => $otherData
		));
		$event->add_record_snapshot('course_modules', $eventData['cm']);
		$event->add_record_snapshot('scorm', $eventData['scorm']);
		$event->add_record_snapshot('scorm_scoes', $eventData['sco']);
		$event->trigger();
	}
	
	private function triggerScoScored($eventData)
	{
		// Trigger a Sco launched event.
		$otherData = ['instanceid' => $eventData['scormid']];
		$otherData = $this->populateScores($otherData);
		$event = \mod_scorm\event\sco_scored::create(array(
				'objectid' => $eventData['scoid'],
				'context' => $eventData['context'],
				'other' => $otherData
		));
		$event->add_record_snapshot('course_modules', $eventData['cm']);
		$event->add_record_snapshot('scorm', $eventData['scorm']);
		$event->add_record_snapshot('scorm_scoes', $eventData['sco']);
		$event->trigger();
	}

	private function triggerScoCompleted($eventData)
	{
		// Trigger a Sco launched event.
		$otherData = ['instanceid' => $eventData['scormid']];
		$otherData = $this->populateScores($otherData);
		$event = \mod_scorm\event\sco_completed::create(array(
				'objectid' => $eventData['scoid'],
				'context' => $eventData['context'],
				'other' => $otherData
		));
		$event->add_record_snapshot('course_modules', $eventData['cm']);
		$event->add_record_snapshot('scorm', $eventData['scorm']);
		$event->add_record_snapshot('scorm_scoes', $eventData['sco']);
		$event->trigger();
	}
	
	private function triggerScoExited($eventData)
	{
		// Trigger a Sco launched event.
		$event = \mod_scorm\event\sco_exited::create(array(
				'objectid' => $eventData['scoid'],
				'context' => $eventData['context'],
				'other' => array('instanceid' => $eventData['scormid'])
		));
		$event->add_record_snapshot('course_modules', $eventData['cm']);
		$event->add_record_snapshot('scorm', $eventData['scorm']);
		$event->add_record_snapshot('scorm_scoes', $eventData['sco']);
		$event->trigger();
	}
	
	private function populateScores($otherData){
		if(isset($this->tincandata['cmi.score.raw'])){
			$otherData['score.raw'] = $this->tincandata['cmi.score.raw'];
		} elseif (isset($this->tincandata['cmi.core.score.raw'])) {
			$otherData['score.raw'] = $this->tincandata['cmi.core.score.raw'];
		}
		if(isset($this->tincandata['cmi.score.max'])){
			$otherData['score.max'] = $this->tincandata['cmi.score.max'];
		} elseif (isset($this->tincandata['cmi.core.score.max'])) {
			$otherData['score.max'] = $this->tincandata['cmi.core.score.max'];
		}
		if(isset($this->tincandata['cmi.score.min'])){
			$otherData['score.min'] = $this->tincandata['cmi.score.min'];
		} elseif (isset($this->tincandata['cmi.core.score.min'])) {
			$otherData['score.min'] = $this->tincandata['cmi.core.score.min'];
		}
		if(isset($this->tincandata['cmi.score.scaled'])){
			$otherData['score.scaled'] = $this->tincandata['cmi.score.scaled'];
		} elseif (isset($this->tincandata['cmi.core.score.scaled'])) {
			$otherData['score.scaled'] = $this->tincandata['cmi.core.score.scaled'];
		}
		return $otherData;
	}
}
