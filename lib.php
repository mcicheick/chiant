<?php

function concat_sep($str1, $str2, $sep=', ') {
  if($str1)
    return $str1.$sep.$str2;
  else
    return $str2;
}

function set_concat_sep(&$str1, $str2, $sep=', ') {
  if($str1)
    $str1 = $str1.$sep.$str2;
  else
    $str1 = $str2;
}

function int_of_bool ($b, $n=0) {
    return ($b) ? 1 << $n: 0;
}


function int_of_bools($bools, $ids) {
    $ints = array_map('int_of_bool', $bools, $ids);
    return array_sum($ints);
}

function bools_of_int($n, $ids) {
    return array_map(function($idx) use ($n) { return $n & (1 << $idx); }, $ids);
}


