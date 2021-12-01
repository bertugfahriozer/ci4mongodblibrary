<?php namespace ci4mongodblibrary\Models;

use ci4mongodblibrary\Libraries\Mongo;

class CommonModel
{
    /**
     * @var Mongo
     */
    protected $m;

    /**
     *
     */
    public function __construct()
    {
        $this->m = new Mongo();
    }

    /**
     * @param string $collection
     * @return mixed
     */
    public function getIndexes(string $collection)
    {
        return $this->m->listindexes($collection);
    }

    /**
     * @param string $collection
     * @param array $credentials
     * @return mixed
     */
    public function create(string $collection, array $credentials)
    {
        return $this->m->insertMany($collection, $credentials);
    }

    /**
     * @param string $collection
     * @param array $credentials
     * @return mixed
     */
    public function createOne(string $collection, array $credentials)
    {
        return $this->m->insertOne($collection, $credentials);
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $options
     * @param array $select
     * @return mixed
     * @throws \Exception
     */
    public function getList(string $collection, array $where = [], array $options = [], array $select = [])
    {
        return $this->m->options($options)->select($select)->where($where)->find($collection)->toArray();
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $options
     * @param array $select
     * @return mixed
     * @throws \Exception
     */
    public function getOne(string $collection, array $where = [], array $options = [], array $select = [])
    {
        return $this->m->options($options)->select($select)->where($where)->findOne($collection);
    }

    /**
     * @param array $credentials
     * @param string $collection
     * @return mixed
     * @throws \Exception
     */
    public function get_where(array $credentials, string $collection)
    {
        return $this->m->options(['limit' => 1])->where($credentials)->count($collection);
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $set
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function updateMany(string $collection, array $where, array $set, array $options = [])
    {
        return $this->m->options($options)->where($where)->set($set)->updateMany($collection);
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $set
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function updateOne(string $collection, array $where, array $set, array $options = [])
    {
        return $this->m->options($options)->where($where)->set($set)->updateOne($collection);
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function deleteOne(string $collection, array $where, array $options = [])
    {
        return $this->m->options($options)->where($where)->deleteOne($collection);
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function deleteMany(string $collection, array $where, array $options = [])
    {
        return $this->m->options($options)->where($where)->deleteMany($collection);
    }

    /**
     * @param string $collection
     * @param array $where
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function count(string $collection, array $where, array $options = [])
    {
        return $this->m->options($options)->where($where)->count($collection);
    }
}