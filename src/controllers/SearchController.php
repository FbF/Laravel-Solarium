<?php namespace Fbf\LaravelSolarium;

class SearchController extends \BaseController {

	public function results()
	{
		$results = $paginator = false;

	    if ( \Input::has('term') )
        {
            $solr = new LaravelSolariumQuery(\Config::get('laravel-solarium::default_core'));

            $term = \Input::get('term');

            $words = explode(' ', $term);

            $searchString = '*'.$term.'* OR ';

            foreach ( $words as $word )
            {
	            $searchString .= '*'.$word.'* OR ';
            }

	        $searchString = rtrim($searchString, ' OR ');

	        $resultsPerPage = \Config::get('laravel-solarium::results.items_per_page');

            $results = $solr->search('search_content:'.$searchString.'')
                ->fields(array('id', 'title', 'content', 'url'))
                ->page(\Input::get('page', 1), $resultsPerPage)
                ->get();

	        $paginator = \Paginator::make(
		        $results->getDocuments(),
		        $results->getNumFound(),
		        $resultsPerPage
	        );
        }

		$viewFile = \Config::get('laravel-solarium::results.view');

        return \View::make($viewFile)->with(compact('results', 'paginator'));
	}
}