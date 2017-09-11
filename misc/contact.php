<?php

ini_set('display_errors', 1);
require_once 'class.phpmailer.php';

$json = file_get_contents('php://input');
$data = json_decode($json);

$body = '<p><b>Name:</b>'.$data->name.'</p><br>';
$body .= '<p><b>E-Mail:</b>'.$data->email.'</p><br>';
if (isset($data->message)) {
  $body .= '<p><b>Message:</b>'.$data->message.'</p><br>';
}
  

$captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$captcha_val = isset($data->response) ? $data->response : '';
$captcha_sec = '6Ld89C8UAAAAAHEpWZ5BU8AHzK0Hx7lgPoU5NPiu';
var_dump($captcha_val);
$myCurl = curl_init();
curl_setopt_array($myCurl, array(
  CURLOPT_URL => $captcha_url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(array('secret' => $captcha_sec, 'response' => $captcha_val))
));

$response = curl_exec($myCurl);
curl_close($myCurl);
$response = (array)json_decode($response);
var_dump($response);

if ($response['success']) {

  $email = new PHPMailer();
  $email->From      = $data->email;
  $email->FromName  = $data->name;
  $email->Subject   = 'BENTA.AT - INTERESSE?';
  $email->Body      = $body;
  $email->isHTML(true);
  $email->AddAddress('smotzart@yandex.ru'); //office@benta.at
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

