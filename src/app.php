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


class queryData {
    public $start;
    public $recordsTotal;
    public $recordsFiltered;
    public $data;

    function queryData() {
    }
}
 
use Silex\Application;

require_once __DIR__.'/func_db_options.php';

$app = new Application();

if($_SERVER['HTTP_HOST'] == 'crudgenerator')
{
    $app['is_prod'] = false;
}
else
{
    $app['is_prod'] = true;
}

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../web/views',
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => getDbOptions( $app['is_prod'] )
));


if($app['is_prod'])
    $app['asset_path'] = 'http://admin.gillesmichel.fr/resources';
else
    $app['asset_path'] = 'http://crudgenerator/resources';

$app['debug'] = true;

return $app;
