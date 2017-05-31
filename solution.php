<?php
function SetEmptyTasks() {
  $iterFunc = function($counterLeads) use(&$iterFunc){
    $leads = getLeads($counterLeads);
    if (count($leads) == 0) {
      return;
    }
    foreach ($leads as $lead) {
      setLeadEmptyTask($lead);
    }
    //return $iterFunc($counterLeads + count($leads));
  };

  function getLeads($counterLeads) {
    return range(1, 10);
  }

  function getTasks($lead) {
    return range(1, 10);
  }

  function setLeadEmptyTask($lead) {
    $hasEmptyTasks = function($lead) {
      return true;
    };

    $filteredLeads = array_filter(getTasks($lead), $hasEmptyTasks);
    foreach ($filteredLeads as $currentLead) {
      setEmptyTask($currentLead);
    }
  };

  function setEmptyTask($lead) {
    $task['request']['tasks']['add'] = array(
      array(
        'text' => 'Deal without task'
      ),
      $lead
    );
    print('setEmpty');
  }

  return $iterFunc(0);
}
?>
