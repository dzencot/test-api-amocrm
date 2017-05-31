<?php

//const ADDRESS = 'https://test.amocrm.ru/private/api/v2/json';
const ADDRESS = 'http://localhost:3000';

function SetEmptyTasks() {


  // чтобы обработать все сделки(если их больше 500)
  // будем итеративно обрабатывать их со смещением
  $iterFunc = function($offsetLeads) use(&$iterFunc){
    $leads = getLeads($offsetLeads)['leads'];
    if (count($leads) == 0) {
      return;
    }
    foreach ($leads as $lead) {
      $emptyLead = !hasOpenTask($lead);
      if ($emptyLead) {
        setEmptyTask($lead);
      }
    }
    $iterFunc($offsetLeads + count($leads));
  };

  function getLeads($counterLeads) {
    // в соответствии с правилами работы с API не более одного запроса в секунду,
    // (далее перед каждым запросом):
    sleep(1);

    $link = ADDRESS . "/leads/list?limit_rows=500&limit_offset=$counterLeads";
    $curl = getCurl('GET', $link);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    handlerError($code);
    $response = json_decode($out, true);
    return $response['response'];
  }

  function getTasks($leadId) {
    sleep(1);

    $link = ADDRESS . "/tasks/list?type=lead&element_id=$leadId";
    $curl = getCurl('GET', $link);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    handlerError($code);
    $response = json_decode($out, true);
    return $response['response'];
  }

  function hasOpenTask($lead) {
    $tasks = getTasks($lead['id'])['tasks'];
    if (count($tasks) == 0) {
      return false;
    }
    $filteredTasks = array_filter($tasks, function($task) {
      // { status : 0 } - открытая задача
      if (key_exists('status', $task) && $task['status'] == 0) {
        return true;
      }
      return false;
    });
    return count($filteredTasks) > 0;
  };

  function setEmptyTask($lead) {
    sleep(1);

    $task['request']['tasks']['add'] = array(
      array(
        'element_id' => $lead['id'],
        'text' => 'Lead without tasks',
        'status' => 0
      )
    );
    $link = ADDRESS . '/tasks/set';
    $curl = getCurl('POST', $link, $task);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    handlerError($code);
    //print_r($out);
  }
    
  function getCurl($method, $link, $data = null) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    if ($method == 'POST' && $data != null) {
      $data = json_encode($data);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($data)
      ));
    }
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    return $curl;
  }

  function handlerError($code) {
    $code=(int)$code;
    $errors=array(
      301=>'Moved permanently',
      400=>'Bad request',
      401=>'Unauthorized',
      403=>'Forbidden',
      404=>'Not found',
      500=>'Internal server error',
      502=>'Bad gateway',
      503=>'Service unavailable'
    );
    try {
      if($code!=200 && $code!=204)
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
    }
    catch(Exception $E) {
      die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
    }
  }

  return $iterFunc(0);
}

function print_result() {
  $link = ADDRESS;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $link);
  $out = curl_exec($curl);
  curl_close($curl);
  $response = json_decode($out, true);
  print_r($response['response']);
}
print("Before:\n");
print_result();
// предполагаю, что мы уже авторизованы
SetEmptyTasks();
print("After:\n");
print_result();

