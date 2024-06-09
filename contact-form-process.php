<link rel="stylesheet" href="./style.css">
<?php
$name = $_REQUEST['name'] ; // Haetaan lomakkeesta nimi kenttä
$email = $_REQUEST['email'] ; // Haetaan lomakkeesta sähköpostiosoite kenttä
$message = $_REQUEST['message']; // Haetaan lomakkeesta viesti kenttä
$mail = new EMail;
$mail->Server = "localhost";
$mail->Username = 'noresponse@rondorakennus.fi'; // Webhotelliin luotu sähköpostilaatikko
$mail->Password = 'Testisivusto94'; // Sähköpostilaatikon salasana
$mail->SetFrom($email,$name); //Viestin lähettäjän sähköpostiosoite ja nimi.
$mail->AddTo("roni@rondorakennus.fi","Roni Kanerva"); //Viestin vastaanottajan sähköpostiosoite ja nimi
$mail->Subject = "Rondo-Rakennus"; // Viestin otsikko
$mail->Message = "Sahkopostiosoite: $email<br> Nimi: $name <br> Viesti: $message\n.\n"; // Viestin sisältö. Tässä tapauksessa lomakkeessa ilmoitettu sähköpostiosoite ja viesti.
//Optional extras
$mail->ContentType = "text/html"; // Defaults to "text/plain; charset=iso-8859-1"
//$mail->Headers['X-SomeHeader'] = 'abcde'; // Set some extra headers if required
echo $success = $mail->Send();
/*
This is the EMail class.
Anything below this should not be edited unless you really know what you're doing.
*/
class EMail
{
const newline = "\r\n";
private
$Port, $Localhost, $skt;
public
$Server, $Username, $Password, $ConnectTimeout, $ResponseTimeout,
$Headers, $ContentType, $From, $To, $Cc, $Subject, $Message,
$Log;
function __construct()
{
$this->Server = "localhost";
$this->Port = 587;
$this->Localhost = "localhost";
$this->ConnectTimeout = 30;
$this->ResponseTimeout = 8;
$this->From = array();
$this->To = array();
$this->Cc = array();
$this->Log = array();
$this->Headers['MIME-Version'] = "1.0";
$this->Headers['Content-type'] = "text/plain; charset=iso-8859-1";
}
private function GetResponse()
{
stream_set_timeout($this->skt, $this->ResponseTimeout);
$response = '';
while (($line = fgets($this->skt, 515)) != false)
{
$response .= trim($line) . "\n";
if (substr($line,3,1)==' ') break;
}
return trim($response);
}
private function SendCMD($CMD)
{
fputs($this->skt, $CMD . self::newline);
return $this->GetResponse();
}
private function FmtAddr(&$addr)
{
if ($addr[1] == "") return $addr[0]; else return "\"{$addr[1]}\" <{$addr[0]}>";
}
private function FmtAddrList(&$addrs)
{
$list = "";
foreach ($addrs as $addr)
{
if ($list) $list .= ", ".self::newline."\t";
$list .= $this->FmtAddr($addr);
}
return $list;
}
function AddTo($addr,$name = "")
{
$this->To[] = array($addr,$name);
}
function AddCc($addr,$name = "")
{
$this->Cc[] = array($addr,$name);
}
function SetFrom($addr,$name = "")
{
$this->From = array($addr,$name);
}
function Send()
{
$newLine = self::newline;
//Connect to the host on the specified port
$this->skt = fsockopen($this->Server, $this->Port, $errno, $errstr, $this->ConnectTimeout);
if (empty($this->skt))
return false;
$this->Log['connection'] = $this->GetResponse();
//Say Hello to SMTP
$this->Log['helo'] = $this->SendCMD("EHLO {$this->Localhost}");
//Request Auth Login
$this->Log['auth'] = $this->SendCMD("AUTH LOGIN");
$this->Log['username'] = $this->SendCMD(base64_encode($this->Username));
$this->Log['password'] = $this->SendCMD(base64_encode($this->Password));
//Email From
$this->Log['mailfrom'] = $this->SendCMD("MAIL FROM:<{$this->From[0]}>");
//Email To
$i = 1;
foreach (array_merge($this->To,$this->Cc) as $addr)
$this->Log['rcptto'.$i++] = $this->SendCMD("RCPT TO:<{$addr[0]}>");
//The Email
$this->Log['data1'] = $this->SendCMD("DATA");
//Construct Headers
if (!empty($this->ContentType))
$this->Headers['Content-type'] = $this->ContentType;
$this->Headers['From'] = $this->FmtAddr($this->From);
$this->Headers['To'] = $this->FmtAddrList($this->To);
if (!empty($this->Cc))
$this->Headers['Cc'] = $this->FmtAddrList($this->Cc);
$this->Headers['Subject'] = $this->Subject;
$this->Headers['Date'] = date('r');
$headers = '';
foreach ($this->Headers as $key => $val)
$headers .= $key . ': ' . $val . self::newline;
$this->Log['data2'] = $this->SendCMD("{$headers}{$newLine}{$this->Message}{$newLine}.");
// Say Bye to SMTP
$this->Log['quit'] = $this->SendCMD("QUIT");
fclose($this->skt);
return substr($this->Log['data2'],0,3) == "250";
}
}
?>