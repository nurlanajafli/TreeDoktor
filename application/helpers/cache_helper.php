<?php

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

function last_updated($key=null)
{
    if (null === $key) {
        return 0;
    }

    if ($value = json_decode(Cache::get($key), true)) {
        return strtotime($value['last_update_date']);
    }

    return 0;
}

function save_to_bucket($key_prefix)
{
    if ($keys = get_keys_to_save($key_prefix)) {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = json_decode(Cache::get($key), true);
        }

        return $data;
    }

    return 0;
}

function get_keys_to_save($prefix)
{
    $data           = [];
    $timeout        = 20*60; // timeout in seconds
    $current_time   = Carbon::now()->timestamp;

    foreach (Cache::keys("{$prefix}*") as $key) {
        if ($current_time - last_updated($key) < $timeout) {
            $data[] = $key;
        }
    }

    return $data;
}

