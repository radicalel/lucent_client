<?php namespace Lucy;

class Client
{
    private $channel;
    private static $options = [];
    private $query = null;

    public static function setConfig(array $options = []){
        $default = [
            'previewUrl' => 'https://preview.lucentcms.com/api/v2/', 
            'liveUrl' => 'https://live.lucentcms.com/api/v2/', 
            'deployment' => 'preview', 
            'apiKey' => null, 
            'locale' => null, 
        ];
        
        static::$options = array_merge($default,$options);
    }

    public static function getConfig($key,$default = null){
        
        if(!isset(static::$options[$key])){
            return $default;
        }
        return static::$options[$key];
    }

    public static function go(){
        $lucy = new static();
        $lucy->channel = static::getConfig('channel');
        $lucy->query = new Query;
        $lucy->query->add('apiKey',static::getConfig('apiKey'));
        $lucy->query->add('locale',static::getConfig('locale'));
        return $lucy;
    }

    public function query($contentType,$withLinks = false){
        $this->query->add('contentType',$contentType);
        if($withLinks)$this->query->add('withLinks',$withLinks);
        return $this;
    }

    public function group($group,$withLinks = false,int $limit = 20, int $skip = 0){
        $this->query->add('group',$group);
        if($withLinks)$this->query->add('withLinks',$withLinks);
        $sort = 'groupSort.'.$group;
        $this->orderBy($sort,'asc');
        $this->limit($limit);
        $this->skip($skip);

        $endpoint = static::getGroupEndpoint().$group;
        return  $this->get($endpoint);
    }

    public function find($contentType,$id){
      if (is_array($id)) {
        $id = implode(',',$id);
      }
      $this->query($contentType);
      $endpoint = static::getEndpoint().id();
      return $this->get($endpoint)['data'];
    }

    public function findBySlug($contentType,$slug){
      $this->query($contentType);
      $endpoint = static::getEndpoint();
      $this->query->add('slug',$slug);
      $results = $this->get($endpoint);
      if (count($results['data']) == 0) {
        return null;
      }else{
        return $results['data'][0];
      }

    }

    public function where($key,$op,$value = null){
        $this->query->where($key,$op,$value);
        return $this;
    }

    public function orderBy($field,$dir){
        $this->query->orderBy($field,$dir);
        return $this;
    }

    public function limit(int $num){
        $this->query->limit($num);
        return $this;
    }

    public function skip(int $num){
        $this->query->skip($num);
        return $this;
    }


    public static function getEndpoint(){
   
        if(static::getConfig('deployment') == 'preview'){
            return static::getConfig('previewUrl').static::getConfig('channel').'/documents/';
        }elseif(static::getConfig('deployment') == 'live'){
            return static::getConfig('liveUrl').static::getConfig('channel').'/documents/';
        } else{
            return false;
        }   
    }
    public static function getGroupEndpoint(){
   
        if(static::getConfig('deployment') == 'preview'){
            return static::getConfig('previewUrl').static::getConfig('channel').'/group/';
        }elseif(static::getConfig('deployment') == 'live'){
            return static::getConfig('liveUrl').static::getConfig('channel').'/group/';
        } else{
            return false;
        }   
    }

    public function first(){
        $endpoint = static::getEndpoint();
        $results = $this->get($endpoint);
        if (count($results['data']) == 0) {
          return null;
        }else{
          return $results['data'][0];
        }
    }

    public function all(){
        $endpoint = static::getEndpoint();
        return  $this->get($endpoint);
    }

    public function getQueryParams(){
      return $this->query->getQuery();
    }

    public function get($endpoint){
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->request('GET', $endpoint, [
                //'json' => $params,
                'query' => $this->getQueryParams(),
                'headers' => [
                    'Accept'     => 'application/json',
                ]
            ]);
        } catch (\Exception $e) {
            // if ($e->getCode() == 404) abort(404);
            $response = $e->getResponse()->getBody()->getContents();
            return json_decode($response);
        }

       $decoded = json_decode((string)$res->getBody(), true);
       if (isset($decoded->status) && $decoded->status == 'ERROR') {
           die('error occured: ' . $decoded->response->errormessage);
       }

       return  $decoded;

    }




}
