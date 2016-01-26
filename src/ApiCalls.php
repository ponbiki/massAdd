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
    
    protected function baseCurl($arg_array) {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, self::BASEURL . $arg_array['arg']);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, array("X-NSONE-Key: {$arg_array['key']}"));
        if (isset($arg_array['opt'])) {
            \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "POST");
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $arg_array['opt']);
        }
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        $this->body = \json_decode(\curl_exec($ch), true);
        \curl_close($ch);
        return $this->body;
    }
    
    public function keyValidate($key)
    {
        $body = self::baseCurl(["key" => $key, "arg" => "zones"]);
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
        $this->record_list = self::baseCurl(["key" => $this->clean_key, "arg" => $zone_arg]);
    }
    
    public function getMatches($answer)
    {
        $this->search_answer = $answer;
        $search_arg = "search?q=$answer&type=answers";
        $record_array = self::baseCurl(["key" => $this->valid_key, "arg" => $search_arg]);
        if (\count($record_array) < 1) {
            unset($this->matches_array);
            $_SESSION['error'][] = "$answer is not associated with any records!";
        } else {
            $this->matches_array = $record_array;
        }
    }
    
    public function replaceAnswer($new_answer, $change_list) {
        $this->new_answer = $new_answer;
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
            if (\in_array($post_records['domain'], $change_list)) {
                $arg = "{$post_records['zone']}/{$post_records['domain']}/{$post_records['type']}";
                $json_up = \json_encode($post_records);
                $body = self::baseCurl(["key" => $this->valid_key, "arg" => $arg, "opt" => $json_up]);
                if (\array_key_exists('message', $body)) {
                    $_SESSION['error'][] = "Invalid Input: {$body['message']}";
                    exit;
                }
            }
        }
        $_SESSION['info'][] = \count($change_list) . ((\count($change_list) < 2)?" record's":" records'") 
                . " answers updated from $this->search_answer to $this->new_answer";
    }
}