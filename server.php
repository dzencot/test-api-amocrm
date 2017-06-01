<?php
require_once 'Application.php';
require_once 'Lead.php';
require_once 'Task.php';

const MAX_ARRAY = 500;
$leads = array_map(function($i) {
  $tasks = [];
  if ($i % 2 == 0) {
    //каждой второй сделке добавим задачу
    $tasks = array_map(function() {
      $task = new Task('hi');
      $task->setStatusOn();
      return $task;
    }, range(1, 10));
  }
  return new Lead($tasks);
}, range(1, 10));

$app = new Application();

$app->get('/', function() use($leads) {
  echo($leads);
});

$app->get('/leads/list', function() use($leads) {
  $lengthArr = intval(array_key_exists('limit_rows', $_GET) ? $_GET['limit_rows'] : MAX_ARRAY);
  $offset = intval(array_key_exists('limit_offset', $_GET) ? $_GET['limit_offset'] : 0);
  if ($offset > count($leads)) {
    echo(json_encode('{ response: null }'));
    return;
  } else {
    $result = array_map(function($item) {
      return array('id' => $item->getId());
    }, array_slice($leads, $offset, $lengthArr));
    echo(json_encode(array('response' => array('leads' => $result))));
    return;
  }
});

$app->get('/tasks/list', function() use($leads) {
  $idLead = intval(array_key_exists('element_id', $_GET) ? $_GET['element_id'] : 0);
  foreach ($leads as $lead) {
    if ($lead->getId() == $idLead) {
      $tasks = $lead->getTasks();
      $result = array_map(function($task) {
        return array('id' => $task->getId(), 'status' => $task->getStatus());
      }, $tasks);
      echo(json_encode(array('response' => array('tasks' => $result))));
    }
  }
  return;
});

$app->post('/tasks/set', function() use(&$leads) {
  $jsonStr = file_get_contents("php://input");
  $req = json_decode($jsonStr);
  $tasks = (Array) (((Array) (((Array) (((Array) $req)['request']))['tasks']))['add']);
  $parsedTasks = array_map(function($task) {
    return (Array) $task;
  }, $tasks);
  // отсутствует логика добавления тасков
  echo(json_encode(array('response' => array('tasks' => $parsedTasks))));
});

$app->run();

