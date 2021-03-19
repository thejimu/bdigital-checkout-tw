<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
$localeArray = json_decode(file_get_contents(plugin_dir_path(__FILE__).'/locales.json'));
$city = $_GET['city'];

if($city==''){
    echo "<option value=''>".__('Select City First')."</option>";
    exit;
}

foreach($localeArray->city as $item){
    if($item->zhtw==$city){
        $code = substr($item->id, 0, 2);
        break;
    }
}

if(isset($code)){
    $districtString = '<option value="">'.__('Select District').'</option>';
    foreach($localeArray->district as $district){
        if(substr($district->id, 0, 2)==$code){
           $districtString .= '<option value="'.$district->zhtw.'">'.__($district->en).'</option>';
        }
    }
}

echo $districtString;