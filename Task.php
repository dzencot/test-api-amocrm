<?php

class Task {

  private static $count = 0;

  private $id;

  private $text;

  private $status = 0;

  public function __construct($text = null) {
    self::$count++;
    $this->id = self::$count;
    $this->text = $text;
  }

  public function getId() {
    return $this->id;
  }

  public function getText() {
    return $this->text;
  }

  public function setStatusOn() {
    $this->status = 0;
  }

  public function setStatusOff() {
    $this->status = 1;
  }

  public function getStatus() {
    return $this->status;
  }

}
