<?php

$host = "ftp.example.com"; //ftp server
$login = "user"; //ftp login
$password = "password"; //ftp password
$path = 'important_files'; //directory to backup
$logs = 'logs'; //directory to save logs
$interval = 900; //interval in seconds

function ftp_copy($conn_id, $src_dir, $dst_dir)
{
  global $status;
  $d = dir($src_dir);
  while($file = $d->read())
  {
    if ($file != "." && $file != "..")
    {
      if (is_dir($src_dir."\\".$file))
      {
        if (!@ftp_chdir($conn_id, $dst_dir."/".$file))
        {
          ftp_mkdir($conn_id, $dst_dir."/".$file);
          $status[] = $src_dir."\\".$file.' <> '.$dst_dir.'/'.$file;
        }
        ftp_copy($conn_id, $src_dir."\\".$file, $dst_dir."/".$file);
      }
      else
      {
        $upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."\\".$file, FTP_BINARY);
        $status[] = $src_dir."\\".$file.' <> '.$dst_dir.'/'.$file;
      }
    }
  }
  $d->close();
}

$connection = @ftp_connect($host);

if($connection)
{
  $status[] = "$host | Connection established";
  {
    if(@ftp_login($connection, $login, $password))
    {
      $status[] = "$host | Logged in";
      $path = getcwd()."\\$path";
      $time = date("Y-m-d H-i-s");
      ftp_mkdir($connection, $time);
      $status[] = "$host | Backup started $time\n";
      ftp_copy($connection, $path, $time);
      $status[] = "\n$host | Backup ended";
    }
    else
    {
      $status[] = "$host | Login incorrect";
    }
  }
}
else
{
  $status[] = "$host | Connection refused by server";
}

foreach($status as $value)
{
  echo nl2br("$value\n");
}

if(is_dir($logs))
{
  file_put_contents(getcwd()."\\$logs\\$time.log", implode("\n", $status));
}
else
{
  mkdir($logs);
  file_put_contents(getcwd()."\\$logs\\$time.log", implode("\n", $status));
}

header("Refresh:$interval");

?>
