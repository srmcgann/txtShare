<?php
  function alphaToDec($val){
    $pow=0;
    $res=0;
    while($val!=""){
      $cur=$val[strlen($val)-1];
      $val=substr($val,0,strlen($val)-1);
      $mul=ord($cur)<58?$cur:ord($cur)-(ord($cur)>96?87:29);
      $res+=$mul*pow(62,$pow);
      $pow++;
    }
    return $res;
  }

  function decToAlpha($val){
    $alphabet="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $ret="";
    while($val){
      $r=floor($val/62);
      $frac=$val/62-$r;
      $ind=(int)round($frac*62);
      $ret=$alphabet[$ind].$ret;
      $val=$r;
    }
    return $ret==""?"0":$ret;
  }

  @mkdir('./t');
  @mkdir('./t/d');
  @file_put_contents('./t/index.php', 'nothing to see here!');
  @file_put_contents('./t/index.html', 'nothing to see here!');
  
  $file = <<<'FILE'
<?php
$file = $_GET['file'];
header("Content-disposition: attachment; filename=$file.txt");
header("Content-type: text/plain");
readfile("../$file");
?>
FILE;
  @file_put_contents('./t/d/index.php', $file);
  
  $file = <<<'FILE'
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.+)$ index.php?file=$1 [QSA,B]
FILE;
  @file_put_contents('./t/d/.htaccess', $file);
  
  
  $data = json_decode(file_get_contents('php://input'));
  $text= $data->{'text'};
  $download = $data->{'download'};

  if($text){
    $existing = [];
    forEach(glob('./t/*') as $file){
      $fileAge = (time() - filectime($file));
      if($fileAge > 259200){ // 72 hours
        if(strpos(basename($file), '.') === false){
          unlink($file);
        }
      }else{
        if(strpos(basename($file), '.') === false){
          array_push($existing, basename($file));
        }
      }
    }
    
    function GenFileName(){
      global $existing, $text, $download;
      $ct = 0;
      do{
        $gidx = 1e9 + (rand()%1e8);
        $slug = decToAlpha($gidx);
        $ct++;
        $exists = false;
        forEach($existing as $el){
          if($el === $slug) $exists = true;
        }
      }while($exists && $ct<10);
      if(!$exists){
        if(file_put_contents("./t/$slug", $text)){
          echo json_encode( $download ? "d/$slug" : $slug);
        }else{
          echo "-1";
        }
      }else{
        echo "-2";
      }
    }
    GenFileName();
  }
?>