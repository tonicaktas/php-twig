<?php

require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Stockholm');


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// $log = new Logger('name');
// $log->pushHandler(new StreamHandler('app.log', Logger::WARNING));
// $log->addWarning('Oh no');




$app = new \Slim\Slim( array(
  'view' => new \Slim\Views\Twig()
));
// // dynamiv hello world with url
// $app->get('/hello/{name}', function ($request, $response, $args) {
//     return $response->write("Hello " . $args['name']);
// });

$view = $app->view();
$view->parserOptions = array(
  'debug'=> true
);




$view->parserExtensions = array(
  new \Slim\Views\TwigExtension(),

);

//define a HTTP GET route
$app->get('/', function () use($app){
    $app->render('about.twig');// läser in content från about.twig(som kopierar struktur från main.twig)
  })->name('home');//object operator for url to call


$app->get('/contact', function () use($app){
    $app->render('contact.twig');
  })->name('contact');


  $app->post('/contact', function () use($app){//tar hand om responsen från formulären och sparar i var sin variabel
      $name = $app->request->post('name');
      $email = $app->request->post('email');
      $msg = $app->request->post('msg');

      if(!empty($name) && !empty($email) && !empty($msg)){
          $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
          $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
          $cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);
      }
      else{
        //message the user that there was a problem
        $app->redirect('contact');
      }

      $transport = Swift_Sendmailtransport::newInstance('/usr/sbin/sendmail -bs');
      $mailer = \Swift_Mailer::newInstance($transport);

      $message = \Swift_Message::newInstance();
      $message->setSubject('Email From Our Website');
      $message->setFrom(array(
        $cleanEmail => $cleanName
      ));
      $message->setTo(array('thehouse@localhost'));
      $message->setBody($cleanMsg);

      $result = $mailer->send($message);

      if($result > 0){
        $app->redirect('/webPhp-1');

      } else {

        $app->redirect('/contact');
      }

    });





//Run the slim application
$app->run();
//echo "<br>Hello World";
