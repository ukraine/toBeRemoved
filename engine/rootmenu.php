<?php

global $root_menu;

////////////////////////////////////////////////////////
/////////////////////INPUT//////////////////////////////
$showinfo = ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) ? intval( $_GET['id'] ):false;
////////////////////////////////////////////////////////

function root_menu(){

	$qres = mysql_query( "SELECT id,name FROM `objects` WHERE `parent_id`='0' ORDER BY `priority` DESC" );
	if( !$qres ) die( mysql_error() );
	$menu_items = array();
	while( $row = mysql_fetch_assoc( $qres ) ){
		$menu_items[] = $row;
	}
	$res = array();
	foreach( $menu_items as $item ){
		$res[$item['id']]['info']['name'] = $item['name'];
	}
	return $res;
}

function get_info( $id ){

	$res = mysql_fetch_assoc(mysql_query("SELECT * FROM `objects` WHERE `id`=$id ORDER BY `priority` DESC"));
	return $res;
}

function get_items( $id ){

	$qres = mysql_query( "SELECT * FROM `objects` WHERE `parent_id`=$id ORDER BY `priority` DESC, id ASC" );
	$items = array();
	while( $row = mysql_fetch_assoc( $qres ) ){
		$items[$row['id']]['info'] = $row;
	}
	return $items;
}

function get_submenu( $id ){
	$res = array();
	while( $id ){

		$data = mysql_fetch_assoc( mysql_query( "SELECT `parent_id` FROM `objects` WHERE `id`=$id ORDER BY `priority` DESC" ) );
		$res[$id] = array();
		$res[$id]['items'] = get_items( $id );
		$res[$id]['info'] = get_info( $id );
		$id = $data['parent_id'];
	}
	while( count( $res )>1 ){
		foreach( $res as $k => $v ){
			if( !isset( $max_k ) )
				$max_k = $k;
			if( $k>$max_k )
				$max_k = $k;
		}
		$item2search = $res[$max_k];
		foreach( $res as $k => $v ){
			if( isset( $v['items'][$max_k] ) ){
				$res[$k]['items'][$max_k] = $res[$max_k];
				unset( $res[$max_k] );
			}
		}
		unset( $max_k );
	}
	return $res;
}

$root_menu = root_menu();

if( $showinfo ) {
	$info = get_info( $showinfo );
	$submenu = get_submenu( $showinfo );
	foreach($submenu as $k => $v){
		$root_menu[$k] = $v;
	}
}



function displayTreeV4() {

	global $root_menu;

	$clasacc4=$clasacc3=$clasacc2="";

				foreach( $root_menu as $k => $v )	{

					if (isset($v['items'])){

//					if (isset($v['items']) && count( $v['items'] )>0 ){


						echo "<li class='opened'><span>&nbsp;</span><div><a href='/catalogue/$k/' class='large_5'>" . $v['info']['name'] . "</a></div><ul class='submenu'>\n";

// Мультивложенность
/*
						foreach( $v['items'] as $k1 => $v1 )	{

							if ( isset( $v1['items'] ) && count( $v1['items'] )>0 ){

								if ($k1==$_GET['id']) $clasacc2="opened"; 

								echo "<li class='$clasacc2'><span>&nbsp;</span><div><a href='/catalogue/$k1/' class='large_5'>" . $v1['info']['name'] . "</a></div><ul>\n";

								$counter = count($v1['items']); $i = 0;
								// echo $counter;
								foreach( $v1['items'] as $k2 => $v2 ){

										$i++;
										// echo $i;

										if ($k2==$_GET['id']) $clasacc3="opened"; 
										echo "<li class='$clasacc3'><span>&nbsp;</span><div>&mdash; <a href='/catalogue/$k2/'  class='large_5'>" . $v2['info']['name'] . "</a></div></li>\n";
										if ($i > "10") { echo "<li class='$clasacc3'><a href='#'>...</a></li>\n"; break; }
										$clasacc3="";

								}

								echo '</ul>' . "\n";
								echo '</li>' . "\n";
							} else {

								if ($k1==$_GET['id']) $clasacc2="opened"; 
								echo "<li class='$clasacc2'> &mdash; <a href='/catalogue/$k1/'>" . $v1['info']['name'] . "</a></li>\n";
							}

							$clasacc2="";
						} */
// Мультивложенность

						echo "</ul>\n";
						echo "</li>\n";
					}else{

						// if ($k==$_GET['id']) $clasacc4="opened"; 
						echo "<li class='$clasacc4'><span>&nbsp;</span><div><a href='/catalogue/$k/'  class='large_5'>" . $v['info']['name'] . "</a></div></li>\n";
						$clasacc4 = "";
					}
				}


}

?>