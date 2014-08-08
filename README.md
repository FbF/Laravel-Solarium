Laravel-Solarium
================
Laravel Framework package for using Solarium

## Features

* Observes your app's models for inserts, updates and deletes and stores data in a Solr index
* Supports multiple Solr cores, so you can have different models saving to different cores, or the same model to multiple cores. Useful if you have multiple search facilities in your app, e.g. product search function and general site search.
* Includes route (configurable URI) and controller for querying a site search core.
* Includes sample site search results view, which you can use, if you want, but you'll most likely want to just use the partial views...
* Partial view for site search form that you could include in your layout header
* Partial view for the site search results that you can include from your custom search results view

## Installation

### Setting up Solr

Download the latest version of solr : http://lucene.apache.org/solr/

Unpackage : `tar xvzf solr-4.*.*.tgz`

Copy the example directory and rename it in this case : site-search

Then rename the collection1 directory to what you would like to call your index.

In this case the index will be called search.

If you require more than one index, then copy and rename the site-search folder.

Then update the solr.xml file to tell the solr server how many indexes you have and their names.

for example :

```xml
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
```

Now for each created index folder you need to update the schema.xml and solrconfig.xml files in the index config folder.

example schema.xml file :

```xml
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
```

example solrconfig.xml :

This is best created by copying the matching folder in the provided example directory.

```xml
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
```

You will also need to update the core.properties file in the index folder :

The contents are very simple e.g. :

`name=search`

Then run `sudo java -jar start.jar` in the index directory.

This will start the solr server, which if configured correctly should be viewable at :

http://localhost:8983/solr/#/search

### Getting the laravel-solarium package

Add the following to your composer.json file if using composer :

```json
"require": {
  "fbf/laravel-solarium": "dev-master"
},
```

and then do a `composer update`

Add the ServiceProvider in `app/config/app.php`

`'Fbf\LaravelSolarium\LaravelSolariumServiceProvider'`

Publish the config file:

`php artisan config:publish fbf/laravel-solarium`

Edit the config file.
