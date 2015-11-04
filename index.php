<?php
$site_root = $_SERVER['DOCUMENT_ROOT'];
$api=true;
include "$site_root/include/db.php";
include "$site_root/include/general.php";
include "$site_root/include/search_functions.php";
include "$site_root/include/collections_functions.php";
include "$site_root/include/authenticate.php";

// required: check that this plugin is available to the user
if (!in_array("api_collection",$plugins)){die("no access");}

$collection=getval("collection","");


if ($api_resource['signed']){

  // test signature? get query string minus leading ? and skey parameter
  $test_query="";
  parse_str($_SERVER["QUERY_STRING"],$parsed);
  foreach ($parsed as $parsed_parameter=>$value){
    if ($parsed_parameter!="skey"){
      $test_query.=$parsed_parameter.'='.$value."&";
    }
  }
  $test_query=rtrim($test_query,"&");

  // get hashkey that should have been used to create a signature.
  $hashkey=md5($api_scramble_key.getval("key",""));

  // generate the signature required to match against given skey to continue
  $keytotest = md5($hashkey.$test_query);

  if ($keytotest <> getval('skey','')){
    header("HTTP/1.0 403 Forbidden.");
    echo "HTTP/1.0 403 Forbidden. Invalid Signature";
    exit;
  }
}

if($collection == 'all'){
  // get all collections
  $all_collections = sql_query("select c.*, c.theme2, c.theme3, c.keywords, u.fullname, u.username, c.home_page_publish, c.home_page_text, c.home_page_image,c.session_id from collection c left outer join user u on u.ref = c.user where 1");
  print json_encode($all_collections);
}elseif($collection != ''){
  // get specific collection
  print json_encode(get_collection($collection));
}else{
  // 
  die('invalid request');
}