<?php namespace Lucy;

class Query
{
    private $query = [];

    public function where($key,$op,$value = null){
      if (is_null($value)) {
        $value = $op;
        $op = 'eq';
      }

      $key = $this->makeKey($key,$op);
      $this->add($key,$value);
      return $this;
    }


    public function add($key,$value){
      $this->query[$key] = $value;
      return $this;
    }

    public function makeKey($key,$op){
      if (substr( $key, 0, 7 ) === "fields.") {
        return $key.'['.$op.']';
      }
      return $key.'['.$op.']';
    }

    public function orderBy($field,$dir){
      if ($dir =='desc') {
        $field = '-'.$field;
      }
      $this->add('sort',$field);
      return $this;
    }

    public function limit($num){
      $this->add('limit',$num);
      return $this;
    }

    public function skip($num){
      $this->add('skip',$num);
      return $this;
    }

    public function getQuery(){
      return $this->query;
    }





}
