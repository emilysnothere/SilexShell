<?php
/**
 * The main application entry point.
 *
 * @since       1.0
 * @author      Emily Fox <emily@pmg.com>
 * @copyright   2015 PMG
 */

 use Silex\Application;
 use Silex\Provider\SessionServiceProvider;
 use Silex\Provider\FormServiceProvider;
 use Silex\Provider\ValidatorServiceProvider;
 use Silex\Provider\UrlGeneratorServiceProvider;
 use Silex\Provider\TranslationServiceProvider;
 use Silex\Provider\DoctrineServiceProvider;
 use PMG\Shell\Web\Provider\MainControllerProvider;

 final class AppKernel extends Application
 {
     /**
      * {@inheritdoc}
      */
     public function __construct()
     {
         parent::__construct();

         $this['debug'] = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

         $this->register(new SessionServiceProvider());
         $this->register(new FormServiceProvider());
         $this->register(new ValidatorServiceProvider());
         $this->register(new UrlGeneratorServiceProvider());
         $this->register(new TranslationServiceProvider());

         $this->register(new TwigProvider(), [
             'twig.path'             => __DIR__.'/views',
             'twig.options'          => function ($app) {
                 return [
                     'debug'             => $app['debug'],
                     'strict_variables'  => $app['debug'],
                     'cache'             => $app['debug'] ? false : __DIR__.'/../var/cache',
                 ];
             },
         ]);

         $this->before(function($request) {
             $this['twig']->addGlobal('active', $request->get('_route'));
         });

         $this->register(new DoctrineServiceProvider(), [
             'db.options' => [
                 'url'       => getenv('DATABASE_URL') ?: 'mysql://root@localhost/tct_db',
             ],
         ]);

         $this->register(new LoggingProvider(), [
             'monolog.logfile'   => __DIR__.'/../var/log/application.log',
         ]);

         $this->mount('/', new MainControllerProvider());

         $appProvider = new ApplicationServiceProvider();
         $this->register($appProvider);
     }

     public static function web(Request $r=null)
     {
         (new self())->run($r);
     }
 }
