<?php namespace Fbf\LaravelSolarium;

use Config;
use Solarium\Client;

class LaravelSolariumIndexer {

    protected $_config = array();

    public function __construct()
    {
        $this->_config = Config::get('laravel-solarium::solr');
    }

    public function update($index_data, $core)
    {
        try
        {
            $this->_config['endpoint']['localhost']['path'] = '/solr/'.$core.'/';

            // create a client instance
            $client = new Client($this->_config);

            // get an update query instance
            $update = $client->createUpdate();

            // create a new document for the data
            $doc = $update->createDocument();

            // Add the fields from the saved model to the Solarium request
            foreach ( $index_data as $field => $value )
            {
                $doc->$field = $value;
            }

            // add the document and a commit command to the update query
            $update->addDocument($doc);

            $update->addCommit();

            // this executes the query and returns the result
            $result = $client->update($update);

            if ( $result->getStatus() )
            {
                throw new Exception('Invalid result returned from solr.');
            }
        }
        catch( Exception $e )
        {
            $this->_log($e);
        }
    }

    public function delete($id, $core)
    {
        try
        {
            $this->_config['endpoint']['localhost']['path'] = '/solr/'.$core.'/';

            // create a client instance
            $client = new Client($this->_config);

            // get an update query instance
            $update = $client->createUpdate();

            // add the delete id and a commit command to the update query
            $update->addDeleteById($id);

            $update->addCommit();

            // this executes the query and returns the result
            $result = $client->update($update);

            if ( $result->getStatus() )
            {
                throw new Exception('Invalid result returned from solr.');
            }
        }
        catch( Exception $e )
        {
            $this->_log($e);
        }
    }

    protected function _log(Exception $exception)
    {
        Log::error($exception);
    }
}