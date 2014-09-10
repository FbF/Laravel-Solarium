<?php namespace Fbf\LaravelSolarium;

class LaravelSolariumModelObserver {

    protected $_indexer;

    public function __construct( LaravelSolariumIndexer $indexer )
    {
        $this->_indexer = $indexer;
    }

	protected function getModelConfig($model)
	{
		$nameSpacedClass = get_class($model);
		return \Config::get('laravel-solarium::models.'.$nameSpacedClass);
	}

    public function activeCores($model)
    {
	    $config = $this->getModelConfig($model);
        return array_keys($config['active_cores']);
    }

    public function getIndexUrl($model, $core)
    {
	    $config = $this->getModelConfig($model);
	    $url = $config['url'];
	    return is_callable($url) ? $url($model, $core) : FALSE;
    }

    public function getStripTagsFields($model, $core)
    {
        $config = $this->getModelConfig($model);
	    return ( isset($config['strip_tags']) && is_array($config['strip_tags']) ) ? $config['strip_tags'] : array();
    }

    public function schemaMap($model, $core)
    {
	    $config = $this->getModelConfig($model);
	    return $config['active_cores'][$core];
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
        $config = $this->getModelConfig($model);
        $extra_index_data = array();
        if ( isset($config['extra_index_data']) && is_callable($config['extra_index_data']) )
        {
            $extra_index_data = $config['extra_index_data']($model, $core);
        }

        return array_merge($model->toArray(), array(
          'id' => $model->id.'-'.strtolower(get_class($model)),
          'model_name' => get_class($model),
          'model_id' => $model->id,
        ), $extra_index_data);
    }

    public function mapIndexData($index_data, $model, $core)
    {
        $schema_map = $this->schemaMap($model, $core);

        if ( empty($schema_map) || ! is_array($schema_map) )
        {
            return $index_data;
        }

        $strip_tag_fields = $this->getStripTagsFields($model, $core);

        $mapped_data = array();

        if ( is_array($index_data) )
        {
            foreach( $schema_map as $solr_key => $model_keys )
            {
                if ( is_array($model_keys) )
                {
                    $mapped_data[$solr_key] = '';

                    foreach ( $model_keys as $model_key )
                    {
                        if ( isset($index_data[$model_key]) )
                        {
                            if ( in_array($model_keys, $strip_tag_fields) )
                            {
                                $mapped_data[$solr_key] .= strip_tags($index_data[$model_key]).' ';
                            }
                            else
                            {
                                $mapped_data[$solr_key] .= $index_data[$model_key].' ';
                            }
                        }
                    }
                }
                else
                {
                    if ( isset($index_data[$model_keys]) )
                    {
                        if ( in_array($model_keys, $strip_tag_fields) )
                        {
                            $mapped_data[$solr_key] = strip_tags($index_data[$model_keys]);
                        }
                        else
                        {
                            $mapped_data[$solr_key] = $index_data[$model_keys];
                        }
                    }
                }
            }
        }

        return $mapped_data;
    }

    public function saved($model)
    {
        $cores = $this->activeCores($model);

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

            $index_data = $this->mapIndexData($index_data, $model, $core);

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
        $cores = $this->activeCores($model);

        if ( empty($cores) || ! is_array($cores) )
        {
            // TODO - logging
            // 'No solr core defined for model '.get_class($model)
            return FALSE;
        }

        foreach ( $cores as $core )
        {
            $id = strtolower($model->id.'-'.get_class($model));

            $this->_indexer->delete($id, $core);
        }
    }
}