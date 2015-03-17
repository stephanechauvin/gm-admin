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

$app->match('/tourdate/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'tourdate_id', 
		'tourdate_where', 
		'tourdate_who', 
		'tourdate_when', 
		'tourdate_link', 
		'tourdate_published', 
    );
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `tourdate`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `tourdate`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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

$app->match('/tourdate', function () use ($app) {
    
	$table_columns = array(
		'tourdate_id', 
		'tourdate_where', 
		'tourdate_who', 
		'tourdate_when', 
		'tourdate_link', 
		'tourdate_published', 
    );

    $primary_key = "tourdate_id";	

    return $app['twig']->render('tourdate/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('tourdate_list');



$app->match('/tourdate/create', function () use ($app) {
    
    $initial_data = array(
		'tourdate_where' => '', 
		'tourdate_who' => '', 
		'tourdate_when' => '', 
		'tourdate_link' => '', 
		'tourdate_published' => true, 
    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('tourdate_where', 'text', array('required' => true));
	$form = $form->add('tourdate_who', 'text', array('required' => true));
	$form = $form->add('tourdate_when', 'text', array('required' => true));
	$form = $form->add('tourdate_link', 'text', array('required' => false));
	$form = $form->add('tourdate_published', 'checkbox', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `tourdate` (`tourdate_where`, `tourdate_who`, `tourdate_when`, `tourdate_link`, `tourdate_published`) VALUES (?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['tourdate_where'], $data['tourdate_who'], $data['tourdate_when'], $data['tourdate_link'], $data['tourdate_published']?1:0));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'tourdate created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('tourdate_list'));

        }
    }

    return $app['twig']->render('tourdate/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('tourdate_create');



$app->match('/tourdate/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `tourdate` WHERE `tourdate_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('tourdate_list'));
    }

    
    $initial_data = array(
		'tourdate_where' => $row_sql['tourdate_where'], 
		'tourdate_who' => $row_sql['tourdate_who'], 
		'tourdate_when' => $row_sql['tourdate_when'], 
		'tourdate_link' => $row_sql['tourdate_link'], 
		'tourdate_published' => $row_sql['tourdate_published']==1?true:false, 
    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('tourdate_where', 'text', array('required' => true));
	$form = $form->add('tourdate_who', 'text', array('required' => true));
	$form = $form->add('tourdate_when', 'text', array('required' => true));
	$form = $form->add('tourdate_link', 'text', array('required' => false));
	$form = $form->add('tourdate_published', 'checkbox', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `tourdate` SET `tourdate_where` = ?, `tourdate_who` = ?, `tourdate_when` = ?, `tourdate_link` = ?, `tourdate_published` = ? WHERE `tourdate_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['tourdate_where'], $data['tourdate_who'], $data['tourdate_when'], $data['tourdate_link'], $data['tourdate_published']?1:0, $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'tourdate edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('tourdate_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('tourdate/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('tourdate_edit');



$app->match('/tourdate/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `tourdate` WHERE `tourdate_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `tourdate` WHERE `tourdate_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'tourdate deleted!',
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

    return $app->redirect($app['url_generator']->generate('tourdate_list'));

})
->bind('tourdate_delete');






