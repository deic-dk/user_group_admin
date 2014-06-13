<?php

$this->create('myapp_index', '/')->action(
    function($params){
    require __DIR__ . '/../index.php';
  }
);
