<?php namespace Fbf\LaravelSolarium;

use Config;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;

class LaravelSolariumQuery {

    protected $_config = array();

    protected $_core = FALSE;

    protected $_search_term;

    protected $_fields;

    protected $_order_by_field;

    protected $_order_by_direction;

    protected $_start_index;

    protected $_count_index;

    protected $_filters;

    public function __construct($core=FALSE)
    {
        $this->_core = $core;

        $this->_config = Config::get('laravel-solarium::solr');

        $this->_reset();
    }

    protected function _reset()
    {
        $this->_search_term = FALSE;

        $this->_fields = array();

        $this->_order_by_field = FALSE;

        $this->_order_by_direction = FALSE;

        $this->_start_index = 1;

        $this->_count_index = 10;

        $this->_filters = array();
    }

    public function search($search_term, $core=FALSE)
    {
        if ( $core !== FALSE )
        {
            $this->_core = $core;
        }

        $this->_search_term = $search_term;

        return $this;
    }

    public function order_by($field='id', $direction='ASC')
    {
        $this->_order_by_field = $field;

        if ( strtolower($direction) == 'asc' )
        {
            $this->_order_by_direction = Query::SORT_ASC;
        }

        if ( strtolower($direction) == 'desc' )
        {
            $this->_order_by_direction = Query::SORT_DESC;
        }

        return $this;
    }

    public function fields($fields=array())
    {
        $this->_fields = $fields;

        return $this;
    }

    public function page($page, $items)
    {
        return $this->limit( ($page * $items) - $items, $items);
    }

    public function limit($start, $count)
    {
        if ( is_numeric($start) )
        {
            $this->_start_index = $start;
        }

        if ( is_numeric($count) )
        {
            $this->_count_index = $count;
        }

        return $this;
    }

    public function filter($filter_name, $filter)
    {
        $this->_filters[$filter_name] = $filter;

        return $this;
    }

    public function get($search_term=NULL, $core=FALSE)
    {
        if ( $search_term !== NULL )
        {
            $this->search($search_term, $core);
        }

        if ( empty($this->_core) )
        {
            return FALSE;
        }

        $config = Config::get('laravel-solarium::solr');

        $config['endpoint']['localhost']['path'] = '/solr/'.$this->_core.'/';

        $client = new Client($config);

        // get a select query instance
        $query = $client->createSelect();

        if ( empty($this->_search_term) )
        {
            return FALSE;
        }

        $query->setQuery($this->_search_term);

        // set start and rows param (comparable to SQL limit) using fluent interface
        $query->setStart( $this->_start_index )->setRows($this->_count_index);

        if ( is_array($this->_fields) && ! empty($this->_fields) )
        {
            $query->setFields($this->_fields);
        }

        if ( $this->_order_by_field !== FALSE && $this->_order_by_direction !== FALSE )
        {
            // sort the results by price ascending
            $query->addSort($this->_order_by_field, $this->_order_by_direction);
        }

        if ( is_array($this->_filters) && ! empty($this->_filters) )
        {
            foreach ($this->_filters as $filter_name => $filter)
            {
                $query->createFilterQuery($filter_name)->setQuery($filter);
            }
        }

        $result = $client->select($query);

        $this->_reset();

        // this executes the query and returns the result
        return $result;
    }
}