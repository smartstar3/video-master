<?php namespace MotionArray\Repositories;

use Illuminate\Support\Facades\Redis;
use MotionArray\Models\User;

class UserUploadRepository
{
    public function getUploadingRecords(User $user)
    {
        $keysList = 'user:uploads:' . $user->id;

        $keys = Redis::lrange($keysList, 0, -1);

        $values = array_filter(array_map(function ($key) {
            $value = Redis::get($key);

            if ($value) {
                return json_decode($value);
            }
        }, $keys));

        if (!count($values)) {
            Redis::del($keysList);
        }

        return $values;
    }

    public function addUploadingRecord(User $user, Array $data)
    {
        $keysList = 'user:uploads:' . $user->id;

        $fileKey = 'user:uploads:file:' . $data['key'];

        Redis::set($fileKey, json_encode($data));

        Redis::expire($fileKey, 10);

        Redis::lrem($keysList, 0, $fileKey);

        return Redis::rpush($keysList, $fileKey);
    }

    public function deleteUploadingRecord($key)
    {
        $fileKey = 'user:uploads:file:' . $key;

        Redis::del($fileKey);
    }
}
