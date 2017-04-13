<?php

// TODO
// * API callback to check authenticity of incoming call.

$items = array(
  '1' => array('price' => '5.00', 'file' => 'TGCC-94543.pdf'),
  '2' => array('price' => '0.05', 'file' => 'HTV-free-guide.pdf'),
  '3' => array('price' => '4.00', 'file' => 'HTV-EBook-64732.pdf'),
  '4' => array('price' => '2.00', 'file' => 'bank-privacy-report-45143.pdf'),
  '5' => array('price' => '1.25', 'file' => 'secrets-of-independent-contractors-58394.pdf'),
  '6' => array('price' => '25.00', 'file' => 'state-income-tax-optimization-57853.pdf'),
  '7' => array('price' => '1.00', 'file' => 'mini-guide-personal-13549.pdf'),
  '8' => array('price' => '1.00', 'file' => 'mini-guide-political-66473.pdf'),
  '9' => array('price' => '1.00', 'file' => 'mini-guide-financial-75574.pdf'),
  '10' => array('price' => '0.05', 'file' => 'TGCC-sample.pdf'),
  '11' => array('price' => '1.25', 'file' => 'anonymous-website-report-57875.pdf'),
  '12' => array('price' => '1.50', 'file' => 'bitcoin-and-taxes-78434.pdf'),
);

$forward_addr = '1ACU3YJdzKr8GakT1TTEyzPrmtb16gNd9c';

// This is the API key you have created in StrongCoin.
$api_key = '123456';


//
// Do not edit anything below this line. (Unless you know what you're doing.)
//

$api = 'http://micropayments.herokuapp.com/';

if( ! ISSET($_GET['cmd']))
{
  // Here we process the callback from StrongCoin Payments
  $addr = $_GET['addr'];
  $amount = $_GET['amount'];
  $item = $_GET['orderId'];

  // We create a file using the address, this is so we can
  // track the status of the payment without using a database
  $file_name = sys_get_temp_dir() . '/' . $addr;
  $fh = fopen($file_name, 'w') 
    or die("can't open file " . $file_name . '/' . $addr); 
  // Store the item number
  fwrite($fh, $item);
  fclose($fh);

  echo 'OK';
}
else
{
  $cmd = $_GET['cmd'];

  if($cmd == 'getaddress')
  {
    // The popup gets a payment address form here by calling
    // the strongcoin payment server.

    $return_url = (!empty($_SERVER['HTTPS'])) ? 
      "https://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'] : 
      "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
    $return_url = urlencode($return_url);

    $item = $_GET['item'];
    $amount = $items[$item]['price'];
    $req = "{$api}?order={$item}&api={$api_key}" .
      "&amount={$amount}&addr={$forward_addr}&ret={$return_url}";

    $html = file_get_contents($req);

    echo $html;
  }
  else if($cmd == 'poll')
  {
    // The popup polls here to see if the payment has been made
    $addr = $_GET['addr'];
    $file_name = sys_get_temp_dir() . '/' . $addr;
    if(file_exists($file_name))
      echo '1';
    else
      echo '0';
  }
  else if($cmd == 'file')
  {
    // When the payment has been made the popup links
    // to here to allow the customer to download the file.
    $addr = $_GET['addr'];
    $file_name = sys_get_temp_dir() . '/' . $addr;
    if(file_exists($file_name))
    {
      $fh = fopen($file_name, 'r');
      $item = fread($fh, filesize($file_name));
      fclose($fh);
      $dl_item = $items[$item]['file'];
      $dlfile = dirname(__FILE__) . '/files/' . $dl_item;
      header("Content-Type: application/octet-stream");   
      header("Content-Length: " . filesize($dlfile));   
      header("Content-Disposition: attachment; filename=\"$dl_item\"");   
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");   
      $fp = fopen($dlfile,"rb");   
      fpassthru($fp);   
      fclose($fp);
    }
  }
}
?>
