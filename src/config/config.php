<?php

//

return array(
    'default_core' => 'laravel',  // default Solr index core name
    'models' => array( // Array of models to observe
	    'Fbf\LaravelPages\Page' => array(
            'active_cores' => array( // Models can be saved ot more than one Sols index core
                'laravel' => array( // Array of mapping of Solr schema fields to Eloquent model fields.
				    'id' => 'id',
				    'model_id' => 'model_id',
				    'model_name' => 'model_name',
				    'title' => 'heading',
				    'content' => 'content',
                    'search_content' => array( // A Solr schema field can be populated by more than one Eloquent field
					    'heading',
					    'page_title',
					    'content',
					    'meta_description',
					    'meta_keywords',
				    ),
				    'status' => 'status',
				    'published_date' => 'published_date',
				    'url' => 'url',
			    ),
		    ),
		    'url' => function($model, $core) // Callback to get the URL for this Solr search index record
		    {
			    return $model->getUrl();
		    },
	        'conditional_save' => function($model, $core) // Callback returns boolean to determine if this model should be indexed.
            {
                return TRUE;
            },
            'extra_index_data' => function($model, $core) // Callback to populate search index fields with additional Eloquent data. i.e. relationships etc.
            {
                $data = array();

                return $data;
            },
	    ),
//	    'Fbf\LaravelBlog\Post' => array(
//		    'active_cores' => array(
//			    'laravel' => array(
//				    'id' => 'id',
//				    'model_id' => 'model_id',
//				    'model_name' => 'model_name',
//				    'title' => 'title',
//				    'content' => 'content',
//				    'search_content' => array(
//					    'heading',
//					    'summary',
//					    'content',
//					    'meta_description',
//					    'meta_keywords',
//				    ),
//				    'status' => 'status',
//				    'published_date' => 'published_date',
//				    'url' => 'url',
//			    ),
//		    ),
//		    'url' => function($model, $core)
//			    {
//				    return $model->getUrl();
//			    },
//	    ),
    ),
    'solr' => array(
        'endpoint' => array(
            'localhost' => array(
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr/',
                // only add these if Solr is setup up to use basic auth
                // 'username' => '',
                // 'password' => '',
            ),
        ),
    ),
	'results' => array(
		'view' => 'laravel-solarium::results',
		'items_per_page' => 10,
	),
	'uri' => 'search',

	'use_package_routes' => true,
);