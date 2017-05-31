<?php

class Lead {

  private static $count = 0;

  private $id;

  private $tasks = [];

  public function __construct($tasks = []) {
    self::$count++;
    $this->id = self::$count;
    $this->tasks = $tasks;
  }

  public function getId() {
    return $this->id;
  }

  public function getTasks() {
    return $this->tasks;
  }

  public function addTask($task) {
    $this->tasks[] = $task;
  }

}
