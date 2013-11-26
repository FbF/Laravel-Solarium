Laravel-Solarium
================

Laravel Framework package for using Solarium



<h1>Setting up Solr</h1>

Download the latest version of solr : http://lucene.apache.org/solr/

Unpackage :  tar xvzf solr-4.*.*.tgz

Rename the example directory in this case : site-search

Then rename the collection1 directory to what you would like to call your index.

In this case the index will be called search.

If you require more than one index, then copy and rename the site-search folder.

Then update the solr.xml file to tell the solr server how many indexes you have and their names.

for example :

<?xml version="1.0" encoding="UTF-8" ?>

<solr persistent="false">

  <cores adminPath="/admin/cores" host="${host:}" hostPort="${jetty.port:8983}" hostContext="${hostContext:solr}">
    <core name="search" instanceDir="search" />
    <!-- only include this is you have created the second index folder -->
    <core name="index2" instanceDir="index2" />
  </cores>

  <shardHandlerFactory name="shardHandlerFactory"
    class="HttpShardHandlerFactory">
    <int name="socketTimeout">${socketTimeout:0}</int>
    <int name="connTimeout">${connTimeout:0}</int>
  </shardHandlerFactory>

</solr>


Now for each created index folder you need to update the schema.xml and solrconfig.xml files in the index config folder.

example schema.xml file :

<?xml version="1.0" ?>
<schema name="search" version="1.1">
  <types>
    <fieldType name="string"  class="solr.StrField"  sortMissingLast="true" omitNorms="true" />
    <fieldType name="long"    class="solr.TrieLongField" precisionStep="0" positionIncrementGap="0"/>
  </types>

  <fields>
    <field name="id"              type="string"  indexed="true"  stored="true"  multiValued="false" required="true"/>
    <field name="model_id"        type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="model_name"      type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="title"           type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="content"         type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="search_content"  type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="published_date"  type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="archive_date"    type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="status"          type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="url"             type="string"  indexed="true"  stored="true"  multiValued="false" />
    <field name="_version_"       type="long"    indexed="true"  stored="true"/>
  </fields>

  <!-- field to use to determine and enforce document uniqueness. -->
  <uniqueKey>id</uniqueKey>

  <!-- field for the QueryParser to use when an explicit fieldname is absent -->
  <defaultSearchField>title</defaultSearchField>

  <!-- SolrQueryParser configuration: defaultOperator="AND|OR" -->
  <solrQueryParser defaultOperator="OR"/>
</schema>


example solrconfig.xml :

This is best created by copying the matching folder in the provided example directory.

<?xml version="1.0" encoding="UTF-8" ?>
<!--
 Licensed to the Apache Software Foundation (ASF) under one or more
 contributor license agreements.  See the NOTICE file distributed with
 this work for additional information regarding copyright ownership.
 The ASF licenses this file to You under the Apache License, Version 2.0
 (the "License"); you may not use this file except in compliance with
 the License.  You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
-->

<!--
 This is a stripped down config file used for a simple example...
 It is *not* a good example to work from.
-->
<config>
  <luceneMatchVersion>4.5</luceneMatchVersion>
  <!--  The DirectoryFactory to use for indexes.
        solr.StandardDirectoryFactory, the default, is filesystem based.
        solr.RAMDirectoryFactory is memory based, not persistent, and doesn't work with replication. -->
  <directoryFactory name="DirectoryFactory" class="${solr.directoryFactory:solr.StandardDirectoryFactory}"/>

  <dataDir>${solr.search.data.dir:}</dataDir>

  <!-- To enable dynamic schema REST APIs, use the following for <schemaFactory>:

       <schemaFactory class="ManagedIndexSchemaFactory">
         <bool name="mutable">true</bool>
         <str name="managedSchemaResourceName">managed-schema</str>
       </schemaFactory>

       When ManagedIndexSchemaFactory is specified, Solr will load the schema from
       he resource named in 'managedSchemaResourceName', rather than from schema.xml.
       Note that the managed schema resource CANNOT be named schema.xml.  If the managed
       schema does not exist, Solr will create it after reading schema.xml, then rename
       'schema.xml' to 'schema.xml.bak'.

       Do NOT hand edit the managed schema - external modifications will be ignored and
       overwritten as a result of schema modification REST API calls.

       When ManagedIndexSchemaFactory is specified with mutable = true, schema
       modification REST API calls will be allowed; otherwise, error responses will be
       sent back for these requests.
  -->
  <schemaFactory class="ClassicIndexSchemaFactory"/>

  <updateHandler class="solr.DirectUpdateHandler2">
    <updateLog>
      <str name="dir">${solr.search.data.dir:}</str>
    </updateLog>
  </updateHandler>

  <!-- realtime get handler, guaranteed to return the latest stored fields
    of any document, without the need to commit or open a new searcher. The current
    implementation relies on the updateLog feature being enabled. -->
  <requestHandler name="/get" class="solr.RealTimeGetHandler">
    <lst name="defaults">
      <str name="omitHeader">true</str>
    </lst>
  </requestHandler>

  <requestHandler name="/replication" class="solr.ReplicationHandler" startup="lazy" />

  <requestDispatcher handleSelect="true" >
    <requestParsers enableRemoteStreaming="false" multipartUploadLimitInKB="2048" formdataUploadLimitInKB="2048" />
  </requestDispatcher>

  <requestHandler name="standard" class="solr.StandardRequestHandler" default="true" />
  <requestHandler name="/analysis/field" startup="lazy" class="solr.FieldAnalysisRequestHandler" />
  <requestHandler name="/update" class="solr.UpdateRequestHandler"  />
  <requestHandler name="/admin/" class="org.apache.solr.handler.admin.AdminHandlers" />

  <requestHandler name="/admin/ping" class="solr.PingRequestHandler">
    <lst name="invariants">
      <str name="q">solrpingquery</str>
    </lst>
    <lst name="defaults">
      <str name="echoParams">all</str>
    </lst>
  </requestHandler>

  <!-- config for the admin interface -->
  <admin>
    <defaultQuery>solr</defaultQuery>
  </admin>

</config>


You will also need to update the core.properties file in the index folder :

The contents are very simple e.g. :

name=search


Then run sudo java -jar start.jar in the index directory.

This will start the solr server, which if configured correctly should be viewable at :

http://localhost:8983/solr/#/search



<h1>Getting the laravel-solarium package</h1>

add the following to your composer.json file if using composer :

"require": {
  "fbf/laravel-solarium": "dev-master"
},

and then do a composer update

or

clone the pakage at : https://github.com/FbF/Laravel-Solarium.git

Usage :

foreach of the models that you wish to observe, you will need to create a model observer in the correct folder as follows :

Folder = app/observers/Fbf/LaravelSolarium

example page observer :

Filename = LaravelSolariumPageObserver.php

Contents :

<?php namespace Fbf\LaravelSolarium;

class LaravelSolariumPageObserver extends LaravelSolariumModelObserver
{
    public function activeCores()
    {
        return array(
            'search', // The names of the indeces that you want to save the data into.
        );
    }

    public function getIndexUrl($model, $core)
    {
        return \URL::action('Fbf\LaravelPages\PagesController@view', array('slug' => $model->slug));
    }
    // returns the mapping between the solr search schema (array keys) and any model values that want to be saved (array values).

    public function schemaMap()
    {
        return array(
            'search' => array(
                'id' => 'id',
                'model_id' => 'model_id',
                'model_name' => 'model_name',
                'title' => 'heading',
                'content' => 'content',
                'search_content' => array(
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
        );
    }
}

?>

Configuring :

create app/config/packages/fbf/laravel-solarium/config.php

contents :

<?php

return array(
    'models' => array(
        'Job' => 'Fbf\LaravelJobs\Job',
        'Page' => 'Fbf\LaravelPages\Page',
        'Post' => 'Fbf\LaravelBlog\Post',
    ),
);

?>

where the models array keys relate to the observer model name eg.

Job is derived from :

LaravelSolariumJobObserver.php

and the array value relate to the fully namespaced model name that the observer observes.


then run :

sudo composer dump-autoload