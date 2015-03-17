<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/news/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'news_id', 
		'news_texte', 
		'news_date', 
        'news_published', 

    );
    
    $whereClause = "WHERE news_valide = 1 AND (";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = $whereClause . " ";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }

    $whereClause = $whereClause . " )";
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `news`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `news`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];


        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});

$app->match('/news', function () use ($app) {
    
	$table_columns = array(
		'news_id', 
		'news_texte', 
		'news_date', 
		'news_published', 
    );

    $primary_key = "news_id";	

    return $app['twig']->render('news/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('news_list');



$app->match('/news/create', function () use ($app) {
    
    $initial_data = array(
		'news_texte' => '', 
		'news_date' => date("Y-m-d"), 
		'news_published' => true, 
    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('news_texte', 'textarea', array('required' => true));
	$form = $form->add('news_date', 'text', array('required' => true));
	$form = $form->add('news_published', 'checkbox', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `news` (`news_texte`, `news_date`, `news_published`) VALUES (?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['news_texte'], $data['news_date'], $data['news_published']?1:0));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'news created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('news_list'));

        }
    }

    return $app['twig']->render('news/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('news_create');



$app->match('/news/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `news` WHERE `news_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('news_list'));
    }

    
    $initial_data = array(
		'news_texte' => $row_sql['news_texte'], 
		'news_date' => $row_sql['news_date'], 
		'news_published' => $row_sql['news_published']==1?true:false, 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('news_texte', 'textarea', array('required' => true));
	$form = $form->add('news_date', 'text', array('required' => true));
	$form = $form->add('news_published', 'checkbox', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `news` SET `news_texte` = ?, `news_date` = ?, `news_published` = ? WHERE `news_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['news_texte'], $data['news_date'], $data['news_published']?1:0, $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'news edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('news_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('news/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('news_edit');



$app->match('/news/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `news` WHERE `news_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "UPDATE `news` SET `news_valide` = 0 WHERE `news_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'news deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('news_list'));

})
->bind('news_delete');






