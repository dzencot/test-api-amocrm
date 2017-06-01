<?php

const ADDRESS = 'https://test.amocrm.ru/private/api/v2/json';
// для теста:
//const ADDRESS = 'http://localhost:3000';

function SetEmptyTasks() {

  // чтобы обработать все сделки(если их больше 500)
  // будем итеративно обрабатывать их со смещением
  $iterLeadsHeader = function($offsetLeads, $acc = []) use(&$iterLeadsHeader){
    $leads = getLeads($offsetLeads);
    if (count($leads) == 0) {
      return;
    }
    foreach ($leads as $lead) {
      // также с тасками:
      $iterHasOpenTask = function($offsetTasks) use(&$iterTasksHeader, $lead) {
        $tasks = getTasks($lead['id'], $offsetTasks);
        if (count($tasks) == 0) {
          return false;
        } else if (hasOpenTask($tasks)) {
          return true;
        } else {
          return $iterHasOpenTask($offsetTasks + count($tasks));
        }
      };
      if (!$iterHasOpenTask(0)) {
        setEmptyTask($lead);
      }
    }
    return $iterLeadsHeader($offsetLeads + count($leads));
  };

  function getLeads($offsetLeads) {
    // в соответствии с правилами работы с API не более одного запроса в секунду,
    // (далее перед каждым запросом):
    sleep(1);

    $link = ADDRESS . "/leads/list?limit_rows=500&limit_offset=$offsetLeads";
    $curl = getCurl('GET', $link);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    handlerError($code);
    return parseResponse($out)['leads'];
  }

  function getTasks($leadId, $offsetTasks) {
    sleep(1);

    $link = ADDRESS . "/tasks/list?type=lead&element_id=$leadId&limit_rows=500&limit_offset=$offsetTasks";
    $curl = getCurl('GET', $link);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    handlerError($code);
    return parseResponse($out)['tasks'];
  }

  function hasOpenTask($tasks) {
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
        'text' => 'Сделка без задачи',
        'status' => 0
      )
    );
    $link = ADDRESS . '/tasks/set';
    $curl = getCurl('POST', $link, $task);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    handlerError($code);
    // debug print:
    //print_r(parseResponse($out));
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

  function parseResponse($data) {
    $result = json_decode($data, true);
    return $result['response'];
  }

  return $iterLeadsHeader(0);
}

// предполагается, что мы уже авторизованы
SetEmptyTasks();

/* TODO:
 * - необходимо заменить отдельные запросы на добавление тасков для каждой сделки
 *   одним запросом с передачей массива тасков.
 * - учитывая время жизни сессии, скрипт ограничевается максимум 900 запросами
 * - скрипт не протестирован на больших объемах данных(более 500 сделок и задач)
 *
 */

// для теста написал небольшой сервер эмулирующий апи, есть в репозитории(включая этот файл):
// https://github.com/dzencot/test-api-amocrm.git
