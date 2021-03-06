<?php

namespace ns1\apiCheat;

class ApiCalls implements iApiCalls
{

    protected $body;
    protected $zone_hold;
    protected $rev_zones;
    public $valid_key;
    public $zone_list;
    public $record_list;
    public $search_answer;
    public $matches_array;
    public $new_answer;
    public $fieldset;
    public $status;
    public $interim_answer;
    public $rep_hide;
    public $replaced;
    public $orphan_array;
    public $orphans;    

    protected function baseCurl($arg_array)
    {
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
        if (isset($arg_array['del'])) {
            \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "DELETE");
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

    protected function zoneList($zones_array)
    {
        foreach ($zones_array as $zones) {
            $this->zone_hold[] = $zones->zone;
        }
        return $this->zone_hold;
    }

    public function getRecords($zone) {
        $this->clean_zone = \filter_var($zone, \FILTER_SANITIZE_STRING);
        $zone_arg = "zones/$this->clean_zone";
        $this->record_list = self::baseCurl(["key" => $this->valid_key, "arg" => $zone_arg]);
    }

    public function getMatches($answer)
    {
        $this->rep_hide = \FALSE;
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
        $this->replaced = \FALSE;
    }

    public function replaceAnswer($new_answer, $change_list)
    {
        unset($this->fieldset);
        unset($this->status);
        $this->rep_hide = \TRUE;
        $this->new_answer = $new_answer;
        foreach ($this->matches_array as $key1 => $val1) {
            foreach ($val1 as $key2 => $val2) {
                if ($key2 === 'answers') {
                    foreach ($val2 as $key3 => $val3) {
                        foreach ($val3 as $key4 => $val4) {
                            if ($key4 === 'answer') {
                                if (\preg_match("/$this->search_answer/i", $val4[0])) {
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
                $arg = "zones/$post_records->zone/$post_records->domain/$post_records->type";
                $json_up = \json_encode($post_records);
                $body = $this->baseCurl(["key" => $this->valid_key, "arg" => $arg, "opt" => $json_up]);
                if (\array_key_exists('message', $body)) {
                    $_SESSION['error'][] = "$post_records->domain update failed -- Invalid Input: $body->message";
                    $this->matches_array = [];
                    $this->fieldset = $body->message;
                    $this->status = "Warning, Invalid Replacement Input -- ";
                    return;
                } else {
                    $temp_matches_array[] = $post_records;
                }
            }
        }
        $this->matches_array = $temp_matches_array;
        $this->fieldset = $this->new_answer;
        $this->status = "Records with answers changed to ";
        $_SESSION['info'][] = \count($this->matches_array) . ((\count($change_list) < 2)?" record":" records") 
                . " with answers matching $this->interim_answer changed to $this->new_answer";
        $this->interim_answer = $this->new_answer;
        $this->replaced = \TRUE;
    }

    public function findOrphans()
    {
        unset($this->fieldset);
        unset($this->status);
        $x = 0;
        if (!empty($this->orphan_array)) {
            unset($this->orphan_array);
        }
        $this->revZones();
        foreach ($this->rev_zones as $zone) {
            $this->getRecords($zone);
            if (!empty($this->record_list->records)) {
                foreach ($this->record_list->records as $record) {
                    if ($record->type === 'PTR') {
                        $pieces = \explode('.', $record->domain);
                        $param = "{$pieces[3]}.{$pieces[2]}.{$pieces[1]}.{$pieces[0]}";
                        $search_arg = "search?q=$param&type=answers";
                        $record_array = self::baseCurl(["key" => $this->valid_key, "arg" => $search_arg]);
                        if (\array_key_exists('message', $record_array)) {
                            $_SESSION['error'][] = "There was an error searching for $param. Please contact support";
                            return;
                        } elseif (\count($record_array) >= 1) {
                            continue;
                        } else {
                            $this->orphan_array[$x]['zone'] = $zone;
                            $this->orphan_array[$x]['record'] = $record->domain;
                            $this->orphan_array[$x]['answer'] = $param;
                            $x++;
                        }
                    }
                }
            }
        }
        if (empty($this->orphan_array)) {
            $_SESSION['info'][] = "There are no orphaned PTR records.";
            $this->orphans = \FALSE;
        } else {
            $_SESSION['info'][] = \count($this->orphan_array) . " orphaned PTR records found!";
            $this->fieldset = count($this->orphan_array);
            $this->status = "Total orphaned PTR records: ";
            $this->orphans = \TRUE;
        }
    }

    protected function revZones()
    {
        unset($this->zone_hold);
        self::keyValidate($this->valid_key);
        unset($this->rev_zones);
        foreach ($this->zone_hold as $zone) {
            if (end(explode(".", $zone)) === "arpa") {
                $this->rev_zones[] = $zone;
            }
        }
    }

    public function delPtr($del_list)
    {
        $holder = [];
        foreach ($this->orphan_array as $orphan) {
            if (\in_array($orphan['record'], $del_list)) {
                $arg = "zones/{$orphan['zone']}/{$orphan['record']}/PTR";
                $body = self::baseCurl(["key" => $this->valid_key, "arg" => $arg, "del" => \TRUE]);
                if (\array_key_exists('message', $body)) {
                    $_SESSION['error'][] = "There was a problem deleting {$orphan['record']}.";
                } else {
                    $holder[] = $orphan['record'];
                }
            }
        }
        $number = \count($holder);
        $_SESSION['info'][] = "$number PTR records deleted.";
    }
}