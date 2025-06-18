<?php
$res = 0;
if((isset($a) && isset($b)) && $a >= $b){
  $res = rand($a, $b);
}
return $res;