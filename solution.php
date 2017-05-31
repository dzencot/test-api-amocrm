<?php
function SetEmptyTasks()
{
  $countDeals;

  $iterFunc = function($counterDeals) use ($countDeals)
  {
    if ($counterDeals >= $countDeals) {
      return;
    }
    foreach ($deals as $deal) {
      setDealEmptyTasks($deal);
    }
  };

  $hasEmptyTask = function($deal)
  {

  };

  $setDealEmptyTasks = function($deal) use($hasEmptyTask)
  {
    $tasks = array_filter(getTasks($deal), $hasEmptyTaks);
    $message = "Deal without task.";
    foreach ($tasks as $task) {
      createDeal($message);
    }
  };

  function createDeal($message)
  {

  }
}
?>
