<?php namespace Fbf\LaravelSolarium;

use View;
use Input;
use Controller;
use Paginator;

class LaravelSolariumController extends Controller {

    protected $_items_per_page = 10;

	public function site_search()
	{
	    $page = Input::get('page', 1);

	    $view_vars = array(
	        'search' => FALSE,
	        'page' => $page,
	        'result' => FALSE,
	        'pagination' => FALSE,
	    );

	    if ( Input::has('search') )
        {
            $solr = new LaravelSolariumQuery('books');

            $search = Input::get('search');

            $search_array = explode(' ', $search);

            $search_string = '*'.$search.'* OR ';

            foreach ( $search_array as $item )
            {
                $search_string .= '*'.$item.'* OR ';
            }

            $search_string = rtrim($search_string, ' OR ');

            $result = $solr->search('title:'.$search_string.'')
                ->fields(array('id', 'title', 'Author.name', 'author_id', 'url'))
                ->page($page, $this->_items_per_page)
                // ->order_by('title', 'desc')
                // ->filter('author_id_filter', 'author_id:3')
                ->get();

            $view_vars = array_merge($view_vars, array(
                'search' => $search,
                'result' => $result,
                'pagination' => Paginator::make($result->getDocuments(), $result->getNumFound(), $this->_items_per_page),
            ));
        }

        return View::make('laravel-solarium::site_search', $view_vars);
	}
}