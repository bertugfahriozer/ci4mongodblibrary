<?php namespace ci4mongodblibrary\Libraries;

use \Config\MongoConfig;
use MongoDB\BSON\Regex;
use MongoDB\Client as client;
use MongoCollection;

class Mongo
{
    private $m;
    private $selects = array();
    private $updates = array();
    private $wheres = array();
    private $offset = 0;
    private $sorts = array();
    private $limit = 0;
    private $options = array();
    private $mongoConnectionInfos;

    function __construct($selectedDB='default')
    {
        try {
            $this->mongoConnectionInfos = new MongoConfig();
            foreach ($this->mongoConnectionInfos->dbInfo as $key=>$dbInfo) {
                if($key===$selectedDB) {
                    $this->m = new client($this->mongoConnectionInfos->dbInfo[$key]->srv . "://{$this->mongoConnectionInfos->dbInfo[$key]->hostname}:{$this->mongoConnectionInfos->dbInfo[$key]->port}/{$this->mongoConnectionInfos->dbInfo[$key]->db}",
                        [$this->mongoConnectionInfos->dbInfo[$key]->authMechanism,
                            'username' => $this->mongoConnectionInfos->dbInfo[$key]->userName,
                            'password' => $this->mongoConnectionInfos->dbInfo[$key]->password,
                            'journal' => $this->mongoConnectionInfos->dbInfo[$key]->journal,
                            'w' => $this->mongoConnectionInfos->dbInfo[$key]->write_concerns,
                            'readConcern' => $this->mongoConnectionInfos->dbInfo[$key]->read_concern,
                            'readPreference' => $this->mongoConnectionInfos->dbInfo[$key]->read_preference,
                        ], $this->mongoConnectionInfos->dbInfo[$key]->ca_file);
                    $this->mongoConnectionInfos->db = $dbInfo->db;
                    $this->mongoConnectionInfos->prefix = $dbInfo->prefix;
                }
            }
        } catch (\CodeIgniter\UnknownFileException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Listindexes
     * --------------------------------------------------------------------------------
     *
     * @usage $this->>m->listindexes('collection');
     */
    public function listindexes($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->listIndexes();
    }

    /**
     * --------------------------------------------------------------------------------
     * Select
     * --------------------------------------------------------------------------------
     *
     * Determine which fields to include OR which to exclude during the query process.
     * If you want to only choose fields to exclude, leave $includes an empty array().
     *
     * @usage: $this->m->select(array('foo', 'bar'));
     */
    public function select($includes = array(), $excludes = array())
    {
        if (!is_array($includes)) {
            $includes = array();
        }
        if (!is_array($excludes)) {
            $excludes = array();
        }
        if (!empty($includes)) {
            foreach ($includes as $key => $col) {
                if (is_array($col)) {
                    //support $elemMatch in select
                    $this->selects[$key] = $col;
                } else {
                    $this->selects[$col] = 1;
                }
            }
        }
        if (!empty($excludes)) {
            foreach ($excludes as $col) {
                $this->selects[$col] = 0;
            }
        }

        $this->options['projection'] = $this->selects;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Where
     * --------------------------------------------------------------------------------
     *
     * Get the documents based on these search parameters. The $wheres array should
     * be an associative array with the field as the key and the value as the search
     * criteria.
     *
     * @usage : $this->m->where(array('foo' => 'bar'))->otherFunction('foobar');
     */
    public function where($wheres, $value = null)
    {
        if (is_array($wheres)) {
            foreach ($wheres as $wh => $val) {
                $this->wheres[$wh] = $val;
            }
        } else {
            $this->wheres[$wheres] = $value;
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * or where
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field may be something else
     *
     * @usage : $this->m->where_or(array('foo'=>'bar', 'bar'=>'foo'))->otherFunction('foobar');
     */
    public function where_or($wheres = array())
    {
        if (is_array($wheres) && count($wheres) > 0) {
            if (!isset($this->wheres['$or']) || !is_array($this->wheres['$or'])) {
                $this->wheres['$or'] = array();
            }
            foreach ($wheres as $wh => $val) {
                $this->wheres['$or'][] = array($wh => $val);
            }
            return ($this);
        } else {
            throw new \Exception("Where value should be an array.(where_or)");
        }
    }

    /**
     * @param array $options
     * @return $this
     * @throws \Exception
     */
    public function options($options = array())
    {
        $ops = [];
        if (is_array($options)) {
            foreach ($options as $wh => $val) {
                $ops[$wh] = $val;
            }
            $this->_clear();
            $this->options = $ops;
            if ($this->offset > 0)
                $option['skip'] = $this->offset;
        } else {
            throw new \Exception("Where value should be an array.(options)");
        }

        return $this;
    }

    /**
     * @param string|null $field
     * @param array $in
     * @return Mongo
     * @throws \Exception
     */
    public function where_in(?string $field, $in = array())
    {
        if (empty($field)) {
            throw new \CodeIgniter\Router\Exceptions\RedirectException($route, 500);
            throw new \Exception("Mongo field is require to perform where in query.", 500);
        }
        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$in'] = $in;
            return ($this);
        } else {
            throw new \Exception("where_in in value should be an array.");
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where in all
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is in all of a given $in array().
     *
     * @usage : $this->mongo->where_in_all('foo', array('bar', 'zoo', 'blah'))->otherFunction('foobar');
     */
    public function where_in_all(?string $field, $in = array())
    {
        if (empty($field)) {
            throw new \Exception("Mongo field is require to perform where all in query.", 500);
        }
        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$all'] = $in;
            return ($this);
        } else {
            throw new \Exception("where_in_all in value should be an array.");
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where not in
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is not in a given $in array().
     *
     * @usage : $this->mongo->where_not_in('foo', array('bar', 'zoo', 'blah'))->otherFunction('foobar');
     */
    public function where_not_in(?string $field, $in = array())
    {
        if (empty($field)) {
            throw new \Exception("Mongo field is require to perform where not in query.", 500);
        }
        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$nin'] = $in;
            return ($this);
        } else {
            throw new \Exception("where_not_in in value should be an array.", 500);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where greater than
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is greater than $x
     *
     * @usage : $this->mongo->where_gt('foo', 20);
     */
    public function where_gt(?string $field, $x)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform greater then query.");
        }
        if (!isset($x)) {
            throw new \Exception("Mongo field's value is require to perform greater then query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$gt'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where greater than or equal to
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is greater than or equal to $x
     *
     * @usage : $this->mongo->where_gte('foo', 20);
     */
    public function where_gte(?string $field, $x)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform greater then or equal query.");
        }
        if (!isset($x)) {
            throw new \Exception('warning', "Mongo field's value is require to perform greater then or equal query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$gte'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where less than
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is less than $x
     *
     * @usage : $this->m->where_lt('foo', 20);
     */
    public function where_lt(?string $field, $x)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform less then query.");
        }
        if (!isset($x)) {
            throw new \Exception("Mongo field's value is require to perform less then query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$lt'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where less than or equal to
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is less than or equal to $x
     *
     * @usage : $this->m->where_lte('foo', 20);
     */
    public function where_lte(?string $field, $x)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform less then or equal to query.");
        }
        if (!isset($x)) {
            throw new \Exception("Mongo field's value is require to perform less then or equal to query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$lte'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where between
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is between $x and $y
     *
     * @usage : $this->m->where_between('foo', 20, 30);
     */
    public function where_between(?string $field, $x, $y)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform greater then or equal to query.");
        }
        if (!isset($x)) {
            throw new \Exception("Mongo field's start value is require to perform greater then or equal to query.");
        }
        if (!isset($y)) {
            throw new \Exception("Mongo field's end value is require to perform greater then or equal to query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where between and but not equal to
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is between but not equal to $x and $y
     *
     * @usage : $this->m->where_between_ne('foo', 20, 30);
     */
    public function where_between_ne($field, $x, $y)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform between and but not equal to query.");
        }
        if (!isset($x)) {
            throw new \Exception("Mongo field's start value is require to perform between and but not equal to query.");
        }
        if (!isset($y)) {
            throw new \Exception("Mongo field's end value is require to perform between and but not equal to query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where not equal
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is not equal to $x
     *
     * @usage : $this->m->where_ne('foo', 1)->get('foobar');
     */
    public function where_ne($field, $x)
    {
        if (!isset($field)) {
            throw new \Exception("Mongo field is require to perform Where not equal to query.");
        }
        if (!isset($x)) {
            throw new \Exception("Mongo field's value is require to perform Where not equal to query.");
        }
        $this->_w($field);
        $this->wheres[$field]['$ne'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Like
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the (string) value of a $field is like a value. The defaults
     * allow for a case-insensitive search.
     *
     * @param $flags
     * Allows for the typical regular expression flags:
     * i = case insensitive
     * m = multiline
     * x = can contain comments
     * l = locale
     * s = dotall, "." matches everything, including newlines
     * u = match unicode
     *
     * @param $enable_start_wildcard
     * If set to anything other than TRUE, a starting line character "^" will be prepended
     * to the search value, representing only searching for a value at the start of
     * a new line.
     *
     * @param $enable_end_wildcard
     * If set to anything other than TRUE, an ending line character "$" will be appended
     * to the search value, representing only searching for a value at the end of
     * a line.
     *
     * @usage : $this->m->like('foo', 'bar', 'im', FALSE, TRUE);
     */
    public function like(?string $field, $value = "", $flags = "i", $enable_start_wildcard = TRUE, $enable_end_wildcard = TRUE)
    {
        if (empty($field)) {
            throw new \Exception("Mongo field is require to perform like query.");
        }
        if (empty($value)) {
            throw new \Exception("Mongo field's value is require to like query.");
        }
        $field = (string)trim($field);
        $this->_w($field);
        $value = (string)trim($value);
        $value = quotemeta($value);
        if ($enable_start_wildcard !== TRUE) {
            $value = "^" . $value;
        }
        if ($enable_end_wildcard !== TRUE) {
            $value .= "$";
        }
        $this->wheres[$field] = new Regex($value, $flags);
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Count
     * --------------------------------------------------------------------------------
     *
     * Count the documents based upon the passed parameters
     *
     * @usage : $this->m->count('foo');
     */
    public function count(string $collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->countDocuments($this->wheres, $this->options);
    }

    /**
     * --------------------------------------------------------------------------------
     * Set
     * --------------------------------------------------------------------------------
     *
     * Sets a field to a value
     *
     * @usage: $this->m->where(array('blog_id'=>123))->set(array('posted' => 1, 'time' => time()))->update('blog_posts');
     */
    public function set($fields)
    {
        $this->_u('$set');
        foreach ($fields as $field => $value) {
            $this->updates['$set'][$field] = $value;
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Unset
     * --------------------------------------------------------------------------------
     *
     * Unsets a field (or fields)
     *
     * @usage: $this->m->where(array('blog_id'=>123))->unset('posted')->update('blog_posts');
     * @usage: $this->m->where(array('blog_id'=>123))->set(array('posted','time'))->update('blog_posts');
     */
    public function unset_field($fields)
    {
        $this->_u('$unset');
        if (is_string($fields)) {
            $this->updates['$unset'][$fields] = 1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field) {
                $this->updates['$unset'][$field] = 1;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Add to set
     * --------------------------------------------------------------------------------
     *
     * Adds value to the array only if its not in the array already
     *
     * @usage: $this->m->where(array('blog_id'=>123))->addtoset('tags', 'php')->update('blog_posts');
     * @usage: $this->m->where(array('blog_id'=>123))->addtoset('tags', array('php', 'codeigniter', 'mongodb'))->update('blog_posts');
     */
    public function addtoset($field, $values)
    {
        $this->_u('$addToSet');
        if (is_string($values)) {
            $this->updates['$addToSet'][$field] = $values;
        } elseif (is_array($values)) {
            $this->updates['$addToSet'][$field] = array('$each' => $values);
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Push
     * --------------------------------------------------------------------------------
     *
     * Pushes values into a field (field must be an array)
     *
     * @usage: $this->m->where(array('blog_id'=>123))->push('comments', array('text'=>'Hello world'))->update('blog_posts');v
     * @usage: $this->m->where(array('blog_id'=>123))->push(array('comments' => array('text'=>'Hello world')), 'iewed_by' => array('Alex')->update('blog_posts');
     */
    public function push($fields, $value = array())
    {
        $this->_u('$push');
        if (is_string($fields)) {
            $this->updates['$push'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$push'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Pop
     * --------------------------------------------------------------------------------
     *
     * Pops the last value from a field (field must be an array)
     *
     * @usage: $this->m->where(array('blog_id'=>123))->pop('comments')->update('blog_posts');
     * @usage: $this->m->where(array('blog_id'=>123))->pop(array('comments', 'viewed_by'))->update('blog_posts');
     */
    public function pop($field)
    {
        $this->_u('$pop');
        if (is_string($field)) {
            $this->updates['$pop'][$field] = -1;
        } elseif (is_array($field)) {
            foreach ($field as $pop_field) {
                $this->updates['$pop'][$pop_field] = -1;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Pull
     * --------------------------------------------------------------------------------
     *
     * Removes by an array by the value of a field
     *
     * @usage: $this->m->pull('comments', array('comment_id'=>123))->update('blog_posts');
     */
    public function pull(?string $field, $value = array())
    {
        $this->_u('$pull');
        $this->updates['$pull'] = array($field => $value);
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Rename field
     * --------------------------------------------------------------------------------
     *
     * Renames a field
     *
     * @usage: $this->m->where(array('blog_id'=>123))->rename_field('posted_by', 'author')->update('blog_posts');
     */
    public function rename_field($old, $new)
    {
        $this->_u('$rename');
        $this->updates['$rename'] = array($old => $new);
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Inc
     * --------------------------------------------------------------------------------
     *
     * Increments the value of a field
     *
     * @usage: $this->m->where(array('blog_id'=>123))->inc(array('num_comments' => 1))->update('blog_posts');
     */
    public function inc($fields = array(), $value = 0)
    {
        $this->_u('$inc');
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Multiple
     * --------------------------------------------------------------------------------
     *
     * Multiple the value of a field
     *
     * @usage: $this->m->where(array('blog_id'=>123))->mul(array('num_comments' => 3))->update('blog_posts');
     */
    public function mul($fields = array(), $value = 0)
    {
        $this->_u('$mul');
        if (is_string($fields)) {
            $this->updates['$mul'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$mul'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Maximum
     * --------------------------------------------------------------------------------
     *
     * The $max operator updates the value of the field to a specified value if the specified value is greater than the current value of the field.
     *
     * @usage: $this->m->where(array('blog_id'=>123))->max(array('num_comments' => 3))->update('blog_posts');
     */
    public function max($fields = array(), $value = 0)
    {
        $this->_u('$max');
        if (is_string($fields)) {
            $this->updates['$max'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$max'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Minimum
     * --------------------------------------------------------------------------------
     *
     * The $min updates the value of the field to a specified value if the specified value is less than the current value of the field.
     *
     * @usage: $this->m->where(array('blog_id'=>123))->min(array('num_comments' => 3))->update('blog_posts');
     */
    public function min($fields = array(), $value = 0)
    {
        $this->_u('$min');
        if (is_string($fields)) {
            $this->updates['$min'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$min'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * //! distinct
     * --------------------------------------------------------------------------------
     *
     * Finds the distinct values for a specified field across a single collection
     *
     * @usage: $this->m->distinct('collection', 'field');
     */
    public function distinct(?string $collection, ?string $field)
    {
        if (empty($collection)) {
            throw new \Exception("No Mongo collection selected for update", 500);
        }
        if (empty($field)) {
            throw new \Exception("Need Collection field information for performing distinct query", 500);
        }
        try {
            $documents = $this->mongoConnectionInfos->db->{$this->mongoConnectionInfos->prefix . $collection}->distinct($field, $this->wheres);
            $this->_clear();
            if ($this->return_as == 'object') {
                return (object)$documents;
            } else {
                return $documents;
            }
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                throw new \Exception("MongoDB Distinct Query Failed: {$e->getMessage()}", 500);
            } else {
                throw new \Exception("MongoDB failed", 500);
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * // Order by
     * --------------------------------------------------------------------------------
     *
     * Sort the documents based on the parameters passed. To set values to descending order,
     * you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
     * set to 1 (ASC).
     *
     * @usage : $this->m->order_by(array('foo' => 'ASC'))->get('foobar');
     */
    public function order_by($fields = array())
    {
        foreach ($fields as $col => $val) {
            if ($val == -1 || $val === FALSE || strtolower($val) == 'desc') {
                $this->sorts[$col] = -1;
            } else {
                $this->sorts[$col] = 1;
            }
        }
        $this->options['sort'] = $this->sorts;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * // Offset
     * --------------------------------------------------------------------------------
     *
     * Offset the result set to skip $x number of documents
     *
     * @usage : $this->m->offset($x);
     */
    public function offset($x = 0)
    {
        if ($x !== NULL && is_numeric($x) && $x >= 1) {
            $this->offset = (int)$x;
        }
        return ($this);
    }

    /**
     *  Converts document ID and returns document back.
     *
     * @param stdClass $document [Document]
     * @return  stdClass
     */
    private function convert_document_id($document)
    {
        if ($this->legacy_support === TRUE && isset($document['_id']) && $document['_id'] instanceof MongoDB\BSON\ObjectId) {
            $new_id = $document['_id']->__toString();
            unset($document['_id']);
            $document['_id'] = new \stdClass();
            $document['_id']->{'$id'} = $new_id;
        }
        return $document;
    }

    /**
     * --------------------------------------------------------------------------------
     * _clear
     * --------------------------------------------------------------------------------
     *
     * Resets the class variables to default settings
     */
    private function _clear()
    {
        $this->selects = array();
        $this->updates = array();
        $this->wheres = array();
        $this->offset = 0;
        $this->sorts = array();
        $this->options = array(); // ?? düşünülecek
    }

    /**
     * --------------------------------------------------------------------------------
     * Where initializer
     * --------------------------------------------------------------------------------
     *
     * Prepares parameters for insertion in $wheres array().
     */
    private function _w($param)
    {
        if (!isset($this->wheres[$param])) {
            $this->wheres[$param] = array();
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Update initializer
     * --------------------------------------------------------------------------------
     *
     * Prepares parameters for insertion in $updates array().
     */
    private function _u($method)
    {
        if (!isset($this->updates[$method])) {
            $this->updates[$method] = array();
        }
    }

    /**
     * @param $collection
     * @param array $insertArray
     * @return mixed
     */
    public function insertOne($collection, $insertArray = [])
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->insertOne($insertArray)->getInsertedId();
    }

    /**
     * Insert a new document into the passed collection
     *
     * @usage : $m->insert('foo', $insertArray = array());
     * @param string $collection
     * @param array $insertArray
     * @return : last insert id
     */
    public function insertMany($collection, $insertArray = [])
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->insertMany($insertArray)->isAcknowledged();
    }

    /**
     * Get the documents based on these search parameters. The $wheres array should
     * be an associative array with the field as the key and the value as the search
     * criteria.
     *
     * @usage $this->m->find(array('foo'=>'bar'))->get('foobar);
     */
    public function findOne($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->findOne($this->wheres, $this->options);
    }

    /**
     * @usage $this->m->find(
     * [
     * 'cuisine' => 'Italian',
     * 'borough' => 'Manhattan',
     * ],
     * [
     * 'projection' => [
     * 'name' => 1,
     * 'borough' => 1,
     * 'cuisine' => 1,
     * ],
     * 'limit' => 4,
     * ]
     * );
     */
    public function find($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->find($this->wheres, $this->options);
    }

    /**
     * @param $collection
     * @param array $update
     * @return mixed
     */
    public function findOneAndUpdate($collection, $update = [])
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->findOneAndUpdate($this->wheres, ['$set' => $update], $this->options);
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function findOneAndDelete($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->findOneAndDelete($this->wheres, $this->options);
    }

    /**
     * @usage $m->aggregate([
     * ['$group' => ['_id' => '$state', 'count' => ['$sum' => 1]]],
     * ['$sort' => ['count' => -1]],
     * ['$limit' => 5],
     * ['$lookup'=> [
     *          'from'=> "tableFrom",
     *          'localField'=> "item",
     *          'foreignField'=> "sku",
     *          'as'=> "newName"
     *      ]
     * ],
     * ['$project'=>[
     *          'field'=> true/false
     *      ]
     * ]
     * ]);
     */
    public function aggregate($collection, $pipeline = [])
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->aggregate($pipeline, $this->options);
    }

    /**
     * @usage $m->updateOne(
     * ['name' => 'Bob'],
     * ['$set' => ['state' => 'ny']],
     * ['upsert' => true]
     * );
     */
    public function updateOne($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->updateOne($this->wheres, $this->updates, $this->options)->isAcknowledged();
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function updateMany($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->updateMany($this->wheres, $this->updates, $this->options)->isAcknowledged();
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function deleteOne($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->deleteOne($this->wheres, $this->options)->isAcknowledged();
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function deleteMany($collection)
    {
        return $this->m->selectCollection($this->mongoConnectionInfos->db, $this->mongoConnectionInfos->prefix . $collection)->deleteMany($this->wheres, $this->options)->isAcknowledged();
    }

    /**
     * --------------------------------------------------------------------------------
     * // Limit results
     * --------------------------------------------------------------------------------
     *
     * Limit the result set to $x number of documents
     *
     * @usage : $this->m->limit($x);
     */
    public function limit($x = 99999)
    {
        if ($x !== NULL && is_numeric($x) && $x >= 1) {
            $this->limit = (int)$x;
        }
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Mongo Date
     * --------------------------------------------------------------------------------
     *
     * Create new MongoDate object from current time or pass timestamp to create
     * mongodate.
     *
     * @usage : $this->mongo_db->date($timestamp);
     */
    public function date($stamp = FALSE)
    {
        if ($stamp == FALSE) {
            return new MongoDB\BSON\UTCDateTime();
        } else {
            return new MongoDB\BSON\UTCDateTime($stamp);
        }
    }
}