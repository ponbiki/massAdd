<?php

namespace ns1\apiCheat;

class ApiCalls implements iApiCalls
{
    
    protected $body;
    protected $zone_hold;
    public $valid_key;
    public $zone_list;
    public $record_list;
    public $search_answer;
    public $matches_array;
    public $new_answer;
    
    protected function baseCurl($key, $arg)
    {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, self::BASEURL . $arg);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, array("X-NSONE-Key: $key"));
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        $this->body = \json_decode(\curl_exec($ch), true);
        \curl_close($ch);
        return $this->body;
    }
    
    public function keyValidate($key)
    {
        $arg = "zones";
        $body = self::baseCurl($key, $arg);
        if (array_key_exists('message', $body)) {
            $this->valid_key = \FALSE;
        } else {
            $this->valid_key = $key;
            self::zoneList($body);
        }
    }
    
    private function zoneList($zones_array)
    {
        foreach ($zones_array as $zones) {
            $this->zone_hold[] = $zones['zone'];
        }
        return $this->zone_hold;
    }
 
    public function getRecords($zone) {
        $this->clean_zone = \filter_var($zone, \FILTER_SANITIZE_STRING);
        $zone_arg = "zones/$this->clean_zone";
        $this->record_list = self::baseCurl($this->clean_key, $zone_arg);
    }
    
    public function getMatches($answer)
    {
        $this->search_answer = $answer;
        $search_arg = "search?q=$answer&type=answers";
        $record_array = $this->baseCurl($this->valid_key, $search_arg);
        if (\count($record_array) < 1) {
            unset($this->matches_array);
            $_SESSION['error'][] = "$answer is not associated with any records!";
        } else {
            $this->matches_array = $record_array;
        }
    }
    
    public function replaceAnswer($new_answer, $change_list) {
        $this->new_answer = $new_answer;
        $stinky = [];
        foreach ($this->matches_array as $key1 => $val1) {
            foreach ($val1 as $key2 => $val2) {
                if ($key2 == 'answers') {
                    foreach ($val2 as $key3 => $val3) {
                        foreach ($val3 as $key4 => $val4) {
                            if ($key4 == 'answer') {
                                if ($val4[0] == $this->search_answer) {
                                    $this->matches_array[$key1]['answers'][$key3]['answer'][0] = $this->new_answer;
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($this->matches_array as $post_records) {
            $type = $post_records['type'];
            $zone = $post_records['zone'];
            $rec_name = $post_records['domain'];
        }
    }
}

/*
$chand = curl_init();
curl_setopt($chand, CURLOPT_URL, 'https://api.nsone.net/v1/zones');
curl_setopt($chand, CURLOPT_HTTPHEADER, array("X-NSONE-Key: $key"));
curl_setopt($chand, CURLOPT_RETURNTRANSFER, true);
$a = json_decode(curl_exec($chand), true);
foreach ($a as $zones) {
     $b[] = $zones['zone'];
}

foreach ($b as $fullrec) {
    $chand = curl_init();
    curl_setopt($chand, CURLOPT_URL, 'https://api.nsone.net/v1/zones/'.$fullrec);
    curl_setopt($chand, CURLOPT_HTTPHEADER, array("X-NSONE-Key: $key"));
    curl_setopt($chand, CURLOPT_RETURNTRANSFER, true);
    $c = json_decode(curl_exec($chand), true);
    $list[] = $c;
}

foreach ($list as $d) {
    $zone = $d['zone'];
    foreach ($d['records'] as $e) {
        $records[] = ['domain' => $e['domain'], 'type' => $e['type']];
    }
    $zonez[] = ['zone' => $d['zone'], $records];
}

print_r($zonez);
*/