<?php
function array_values_from_keys($arr, $keys) {
    return array_map(function($x) use ($arr) { return $arr[$x]; }, $keys);
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


