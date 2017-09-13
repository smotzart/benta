<?php

require_once 'class.phpmailer.php';/*
if (mail('smotzart@yandex.ru', 'test subject', 'test message')) {} else {echo "???";}
  $email = new PHPMailer();
  $email->From      = 'office@benta.at';
  $email->FromName  = 'Kotsar Serhii';
  $email->Subject   = 'BENTA.AT - INTERESSE?';
  $email->Body      = "test";
  $email->isHTML(true);
  $email->AddAddress('smotzart@yandex.ru'); //office@benta.at
  $email->CharSet = 'UTF-8';
  if ($email->send()) {
    echo "-send-";
  } else {
    echo "-not send-";
  }

exit();*/
function errorHandler ($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
    case E_USER_ERROR:
      echo "<b>FATAL</b> [$errno] $errstr<br>\n";
      echo "Fatal error in line ".$errline." of file ".$errfile;
      echo ", PHP ".PHP_VERSION." (".PHP_OS.")<br>\n";
      echo "Aborting...<br>\n";     
      break;
    case E_USER_WARNING:
      echo "<b>ERROR</b> $errstr\n";
      break;
    case E_USER_NOTICE:
      echo "<b>WARNING</b> $errstr\n";
      break;
    default:
      echo "Unkown error type: $errstr\n";
      break;
  }
}

set_error_handler('errorHandler');

try {
  $json = file_get_contents('php://input');
  $data = (array)json_decode($json);

  if (empty($data))
    throw new Exception('empty form data');

  $body  = '<p><b>Name:</b>' . $data['name'] . '</p>';
  $body .= '<p><b>E-Mail:</b>' . $data['email'] . '</p>';

  if (isset($data['message']) && !empty($data['message']))
    $body .= '<p><b>Message:</b>' . $data['message'] . '</p>';

  $ch = curl_init();

  if (FALSE === $ch)
    throw new Exception('failed to initialize curl');

  $captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
  $captcha_val =  (isset($data['response']) && !empty($data['response'])) ? $data['response'] : '';
  $captcha_sec = '6Ld89C8UAAAAAHEpWZ5BU8AHzK0Hx7lgPoU5NPiu';

  curl_setopt($ch, CURLOPT_URL, $captcha_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  $url = 'secret='.$captcha_sec.'&response='.$captcha_val;
  curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
  //http_build_query(array('secret' => $captcha_sec, 'response' => $captcha_val))

  $content = curl_exec($ch);

  if (FALSE === $content)
    throw new Exception(curl_error($ch));

  curl_close($ch);
  $content = (array)json_decode($content);

  if (FALSE === $content['success']) {
    $captcha_err = array(
      'missing-input-secret'    => 'The secret parameter is missing.',
      'invalid-input-secret'    => 'The secret parameter is invalid or malformed.',
      'missing-input-response'  => 'The response parameter is missing.',
      'invalid-input-response'  => 'The response parameter is invalid or malformed.',
      'bad-request'             => 'The request is invalid or malformed.'
    );
    throw new Exception($captcha_err[$content['error-codes'][0]]);
  }

  $email = new PHPMailer();
  $email->From      = $data['email'];
  $email->FromName  = $data['name'];
  $email->Subject   = 'BENTA.AT - INTERESSE?';
  $email->Body      = $body;
  $email->isHTML(true);
  $email->AddAddress('office@benta.at'); //smotzart@yandex.ru
  $email->CharSet = 'UTF-8';

  if ($email->send()) {
    http_response_code(200);
    echo "mail send";  
  } else {
    throw new Exception($email->ErrorInfo);
  }


  

  // ...process $content now
} catch(Exception $e) {
  http_response_code(400);
  trigger_error(sprintf('Email sending failed with error: <i>%s</i>', $e->getMessage()), E_USER_WARNING);
}
/*
$body  = '<p><b>Name:</b>'.$data->name.'</p><br>';
$body .= '<p><b>E-Mail:</b>'.$data->email.'</p><br>';

if (isset($data->message)) {
  $body .= '<p><b>Message:</b>'.$data->message.'</p><br>';
}
  
$captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$captcha_val = isset($data->response) ? $data->response : '';
$captcha_sec = '6Ld89C8UAAAAAHEpWZ5BU8AHzK0Hx7lgPoU5NPiu';
$myCurl = curl_init();
curl_setopt_array($myCurl, array(
  CURLOPT_URL => $captcha_url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => 'secret='.$captcha_sec.'&response='.$captcha_val
));

$response = curl_exec($myCurl);
curl_close($myCurl);
$response = (array)json_decode($response);

if ($response['success']) {

  $email = new PHPMailer();
  $email->From      = $data->email;
  $email->FromName  = $data->name;
  $email->Subject   = 'BENTA.AT - INTERESSE?';
  $email->Body      = $body;
  $email->isHTML(true);
  $email->AddAddress('smotzart@yandex.ru');
  //office@benta.at
  //$email->AddCC($_POST['reg']['Einreicher Email'], 'Einreicher Email');
  //$email->AddBCC('othmar.fetz@gmail.com', 'Othmar Fetz');
  $email->CharSet = 'UTF-8';


  if($email->send()) {
    http_response_code(200);
  } else {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $email->ErrorInfo;
    http_response_code(400);
  }
} else {
  $captcha_err = array(
    'missing-input-secret' => 'The secret parameter is missing.',
    'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
    'missing-input-response' => 'The response parameter is missing.',
    'invalid-input-response' => 'The response parameter is invalid or malformed.',
    'bad-request' => 'The request is invalid or malformed.'
  );
  
  echo $captcha_err[$response['error-codes'][0]];
  http_response_code(400);
}
*/