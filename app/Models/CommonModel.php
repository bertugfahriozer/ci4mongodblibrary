<?php

namespace App\Models;

use App\Libraries\Mongo;
use CodeIgniter\Model;

class CommonModel extends Model
{
    protected $m;

    public function __construct()
    {
        parent::__construct();
        $this->m = new Mongo();
    }

    public function getIndexes(string $collection)
    {
        return $this->m->listindexes($collection);
    }

    public function create(string $collection, array $credentials)
    {
        return $this->m->insert($collection, $credentials);
    }

    public function createOne(string $collection, array $credentials)
    {
        return $this->m->insertOne($collection, $credentials);
    }

    public function getList(string $collection, array $where = [], array $options = [],array $select=[])
    {
        return $this->m->options($options)->select($select)->where($where)->find($collection)->toArray();
    }

    public function getOne(string $collection, array $where = [], array $options = [],array $select=[])
    {
        return $this->m->options($options)->select($select)->where($where)->findOne($collection);
    }

    public function get_where(array $credentials, string $collection)
    {
        return $this->m->options(['limit' => 1])->where($credentials)->count($collection);
    }

    public function updateOne(string $collection, array $where, array $set, array $options = [])
    {
        return $this->m->options($options)->where($where)->set($set)->updateOne($collection);
    }

    public function deleteOne(string $collection, array $where,array $options = [])
    {
        return $this->m->options($options)->where($where)->deleteOne($collection);
    }

    public function count(string $collection)
    {
        return $this->m->count($collection);
    }
}
