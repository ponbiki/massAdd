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
    public $fieldset;
    public $status;
    public $interim_answer;
    
    protected function baseCurl($arg_array) {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, self::BASEURL . $arg_array['arg']);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, array("X-NSONE-Key: {$arg_array['key']}"));
        if (isset($arg_array['opt'])) {
            \curl_setopt($ch, \CURLOPT_POST, true);
            \curl_setopt($ch, \CURLOPT_SAFE_UPLOAD, false);
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $arg_array['opt']);
            \curl_setopt($ch, \CURLOPT_HEADER, false);
            \curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
            \curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
        }
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        $this->body = \json_decode(\curl_exec($ch));
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
            $this->zone_hold[] = $zones->zone;
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
        $this->interim_answer = $this->search_answer = $answer;
        $this->fieldset = $this->search_answer;
        $this->status = "Records containing answer ";
        $search_arg = "search?q=$answer&type=answers";
        $record_array = self::baseCurl(["key" => $this->valid_key, "arg" => $search_arg]);
        if (\count($record_array) < 1) {
            unset($this->matches_array);
            $_SESSION['error'][] = "$answer is not associated with any records!";
        } else {
            $this->matches_array = $record_array;
        }
    }
    
    public function replaceAnswer($new_answer, $change_list)
    {
        $this->new_answer = $new_answer;
        foreach ($this->matches_array as $key1 => $val1) {
            foreach ($val1 as $key2 => $val2) {
                if ($key2 == 'answers') {
                    foreach ($val2 as $key3 => $val3) {
                        foreach ($val3 as $key4 => $val4) {
                            if ($key4 == 'answer') {
                                if ($val4[0] == $this->search_answer) {
                                     $this->matches_array[$key1]->answers[$key3]->answer[0] = $this->new_answer;
                                }
                            }
                        }
                    }
                }
            }
        }
        $temp_matches_array = [];
        foreach ($this->matches_array as $post_records) {
            if (\in_array($post_records->domain, $change_list)) {
                $arg = "zones/{$post_records->zone}/{$post_records->domain}/{$post_records->type}";
                $json_up = \json_encode($post_records);
                $body = $this->baseCurl(["key" => $this->valid_key, "arg" => $arg, "opt" => $json_up]);
                if (\array_key_exists('message', $body)) {
                    $_SESSION['error'][] = "$post_records->domain update failed -- Invalid Input: $body->message";
                    break;
                } else {
                    $temp_matches_array[] = $post_records;
                }
            }
        }
        $this->matches_array = $temp_matches_array;
        $this->fieldset = $this->new_answer;
        $this->status = "Records changed to ";
        $_SESSION['info'][] = \count($this->matches_array) . ((\count($change_list) < 2)?" record's":" records'") 
                . " answers updated from $this->interim_answer to $this->new_answer";
        $this->interim_answer = $this->new_answer;
    }
}
