<?php

namespace ns1\apiCheat;

interface iApiCalls
{
    const BASEURL = 'https://api.nsone.net/v1/';
    public function keyValidate($key);
    public function getRecords($zone);
    public function getMatches($answer);
    public function replaceAnswer($new_answer, $change_list);
    public function findOrphans();
    public function delPtr($del_list);
}
