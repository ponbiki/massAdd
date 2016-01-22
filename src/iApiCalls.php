<?php

namespace ns1\apiCheat;

interface iApiCalls
{
    const BASEURL = 'https://api.nsone.net/v1/';
    public function keyValidate($key);
    public function getRecords($zone);
    public function getMatches($answer);
}
