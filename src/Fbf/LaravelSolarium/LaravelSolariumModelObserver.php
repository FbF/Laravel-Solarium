<?php namespace Fbf\LaravelSolarium;

abstract class LaravelSolariumModelObserver {

    protected $_indexer;

    public function __construct()
    {
        $this->_indexer = new LaravelSolariumIndexer;
    }

    public function activeCores()
    {
        return array();
    }

    public function ignoredFields()
    {
        return array();
    }

    public function getIndexUrl($model, $core)
    {
        return FALSE;
    }

    public function schemaMap()
    {
        return array();
    }

    public function addUrlToIndexData($index_data, $model, $core)
    {
        if ( ( $url = $this->getIndexUrl($model, $core) ) && is_array($index_data) )
        {
            $index_data['url'] = $url;
        }

        return $index_data;
    }

    public function getIndexData($model, $core)
    {
        $index_data = FALSE;

        return $index_data;
    }

    public function mapIndexData($index_data, $core)
    {
        $schema_map = $this->schemaMap();

        if ( empty($schema_map) || ! is_array($schema_map) )
        {
            return $index_data;
        }

        if ( ! isset($schema_map[$core]) || empty($schema_map[$core]) || ! is_array($schema_map[$core]) )
        {
            return $index_data;
        }

        $mapped_data = array();

        if ( is_array($index_data) )
        {
            foreach( $schema_map[$core] as $solr_key => $model_keys )
            {
                if ( is_array($model_keys) )
                {
                    $mapped_data[$solr_key] = '';

                    foreach ( $model_keys as $model_key )
                    {
                        if ( isset($index_data[$model_key]) )
                        {
                            $mapped_data[$solr_key] .= $index_data[$model_key].' ';
                        }
                    }
                }
                else
                {
                    if ( isset($index_data[$model_keys]) )
                    {
                        $mapped_data[$solr_key] = $index_data[$model_keys];
                    }
                }
            }
        }

        return $mapped_data;
    }

    public function tidyIndexData($index_data, $core)
    {
        $ignored_fields = $this->ignoredFields();

        if ( is_array($index_data) && isset($ignored_fields[$core]) && is_array($ignored_fields[$core]) )
        {
            foreach ( $index_data as $field => $value )
            {
                if ( in_array($field, $ignored_fields[$core]) )
                {
                    unset($index_data[$field]);
                }
            }
        }

        return $index_data;
    }

    public function saved($model)
    {
        $cores = $this->activeCores();

        if ( empty($cores) || ! is_array($cores) )
        {
            // TODO - logging
            // 'No solr core defined for model '.get_class($model)
            return FALSE;
        }

        foreach ( $cores as $core )
        {
            // Find the data to index from the model
            $index_data = $this->getIndexData($model, $core);

            $index_data = $this->tidyIndexData($index_data, $core);

            $index_data = $this->mapIndexData($index_data, $core);

            $index_data = $this->addUrlToIndexData($index_data, $model, $core);

            // No index data defined for this core - so skip.
            if ( $index_data !== FALSE )
            {
                $this->_indexer->update($index_data, $core);
            }
        }
    }

    public function deleted($model)
    {
        $cores = $this->activeCores();

        if ( empty($cores) || ! is_array($cores) )
        {
            // TODO - logging
            // 'No solr core defined for model '.get_class($model)
            return FALSE;
        }

        foreach ( $cores as $core )
        {
            $this->_indexer->delete($model->id, $core);
        }
    }
}