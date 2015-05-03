<?php

function nDias($date) {
    return dateDiff(date('Y-m-d'), $date);
}

function dateDiff($startDate, $endDate) {
    $date1 = date_create($startDate);
    $date2 = date_create($endDate);
    $diff = date_diff($date1, $date2);
    return $diff->format("%a");
}

function hourDiff($startDate, $endDate) {
    $date1 = date_create($startDate);
    $date2 = date_create($endDate);
    $diff = date_diff($date1, $date2);
    return $diff->format("%a") * 24 + $diff->format("%h");
}

function nRange($n) {
    return date('d/m/Y', strtotime('-' . $n . ' day'));
}

function percentual($parte, $todo) {
    return @round((($parte / $todo) * 100), 2) . "%";
}

function arredondar($valor) {
    if ($valor - floor($valor) > 0 && $valor - floor($valor) <= .249) {
        $r = floor($valor);
        return $r;
    } else if ($valor - floor($valor) >= .25 && $valor - floor($valor) < .59) {
        $r = floor($valor) + .5;
        return $r;
    } else if ($valor - floor($valor) > .5 && $valor - floor($valor) <= .749) {
        $r = floor($valor) + .5;
        return $r;
    } else if (($valor - floor($valor)) >= .749) {
        $r = ceil($valor);
        return $r;
    } else {
        return ($valor);
    }
}

function formata($strDate, $PDF = null) {
    // Array com os meses do ano em portuguÃƒÂªs;
    $arrMonthsOfYear = array(1 => 'Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
    if ($PDF)
        $arrMonthsOfYear = array(1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
    // Descobre o dia da semana
    $intDayOfWeek = date('w', strtotime($strDate));
    // Descobre o dia do mÃƒÂªs
    $intDayOfMonth = date('d', strtotime($strDate));
    // Descobre o mÃƒÂªs
    $intMonthOfYear = date('n', strtotime($strDate));
    // Descobre o ano
    $intYear = date('Y', strtotime($strDate));
    // Formato a ser retornado
    return $intDayOfMonth . ' de ' . $arrMonthsOfYear[$intMonthOfYear] . ' de ' . $intYear;
}

function maiusculo($texto) {
    return mb_convert_case($texto, MB_CASE_UPPER, 'UTF-8');
}

function mostraTexto($string) {
    return $string;
}

function formatarTexto($texto) {
    $string = mb_convert_case(html_entity_decode($texto, ENT_QUOTES, "UTF-8"), MB_CASE_LOWER);

    $string = preg_replace('/\s+/', ' ', $string);

    $lower_exceptions = array(
        "da" => "1", "de" => "1", "di" => "1", "do" => "1",
        "das" => "1", "des" => "1", "dis" => "1", "dos" => "1",
        "a" => "1", "e" => "1", "i" => "1", "o" => "1", "u" => "1",
        "as" => "1", "es" => "1", "is" => "1", "os" => "1", "us" => "1",
        "em" => "1", "no" => "1", "nas" => "1", "nos" => "1", "nas" => "1",
        "à" => "1", "às" => "1", "para" => "1", "por" => "1"
    );

    $higher_exceptions = array(
        "i" => "1", "ii" => "1", "iii" => "1", "iv" => "1",
        "v" => "1", "vi" => "1", "vii" => "1", "viii" => "1",
        "ix" => "1", "x" => "1"
    );

    $words = @explode(" ", $string);
    $newwords = array();
    foreach ($words as $word) {
        if (@$higher_exceptions[$word])
            $word = @mb_convert_case($word, MB_CASE_UPPER);
        if (!@$lower_exceptions[$word])
            $word[0] = @mb_convert_case($word[0], MB_CASE_UPPER);
        array_push($newwords, $word);
    }
    return @join(" ", $newwords);
}

function minusculo($texto) {
    return mb_convert_case($texto, MB_CASE_LOWER);
}

function mysql_matched_rows() {
    $_kaBoom = explode(' ', mysql_info());
    return $_kaBoom[2];
}

function abreviar($texto, $tamanho) {
    if (strlen($texto) > $tamanho) {
        $regExp = "[áàâãäªÁÀÂÃÄéèêëÉÈÊËíìîïÍÌÎÏóòôõöºÓÒÔÕÖúùûüÚÙÛÜçÇÑñ.]";
        $texto2 = substr_replace($texto, '...', $tamanho);
        if (ereg($regExp, $texto2[strlen($texto2) - 4]))
            return substr_replace($texto, '...', $tamanho - 5);
        return $texto2;
    } else
        return $texto;
}

function crip($texto) {
    return base64_encode($_SESSION["cripto"] . $texto) . '___';
}

function dcrip($texto) {
    $texto = str_replace('___', '', $texto);
    $texto = base64_decode($texto);
    $r = explode($_SESSION["cripto"], $texto);
    if (sizeof($r) > 1)
        return $r[1];
    else
        return $r[0]; // nÃƒÂ£o criptografado
}

function genRandomString() {
    $length = 3;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = null;

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}

//formata a data EN
function dataMysql($data) {
    $data = explode('/', $data);
    $data = $data[2] . '-' . $data[1] . '-' . $data[0];
    return $data;
}

//formata a data PT
function dataPTBR($data) {
    $parts = explode(' ', $data);
    $data = explode('-', $parts[0]);
    $data = $data[2] . '/' . $data[1] . '/' . $data[0];

    if ($parts[1])
        $data = $parts[1] . ' de ' . $data;
    return $data;
}

function diasDaSemana() {
    $dias[1] = 'Domingo';
    $dias[2] = 'Segunda';
    $dias[3] = 'Ter&ccedil;a';
    $dias[4] = 'Quarta';
    $dias[5] = 'Quinta';
    $dias[6] = 'Sexta';
    $dias[7] = 'S&aacute;bado';

    return $dias;
}

function meses($a) {
    switch ($a) {
        case 1: $mes = "janeiro";
            break;
        case 2: $mes = "fevereiro";
            break;
        case 3: $mes = "mar&ccedil;o";
            break;
        case 4: $mes = "abril";
            break;
        case 5: $mes = "maio";
            break;
        case 6: $mes = "junho";
            break;
        case 7: $mes = "julho";
            break;
        case 8: $mes = "agosto";
            break;
        case 9: $mes = "setembro";
            break;
        case 10: $mes = "outubro";
            break;
        case 11: $mes = "novembro";
            break;
        case 12: $mes = "dezembro";
            break;
    }
    return $mes;
}

// VERIFICANDO O DIRETORIO TEMPORARIO
if (!function_exists('sys_get_temp_dir')) {

    function sys_get_temp_dir() {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(__FILE__, '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }
        return null;
    }

}

// Função para listagem recursiva de diretórios
// Entrada: diretório (string)
// Saída: Lista de Diretórios e Arquivos (Array)
// Arquivos que utilizam essa função: view/admin/permissao.php
// Autor: Naylor - 17/07
function dirToArray($dir, $regex = null) {
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        $arquivo = str_replace(PATH . LOCATION . '/', '', $name);

        if (!$regex)
            $regex = '\/..$|\/.$';

        if (!preg_match("/$regex/", $name)) {
            if (is_dir($name)) {
                $files[$arquivo] = '';
            } else {
                $base = dirname($arquivo);
                $files[$base][] = $arquivo;
            }
        }
    }
    return $files;
}

// Função decriptação de variaveis em um Array.
// Entrada: Array
// Saída: Array
// Autor: Naylor - 29/07
function dcripArray($array) {
    foreach ($array as $key => $value) {
        if (strpos($value, '___') !== false) {
            if ($value)
                $new_array[$key] = dcrip($value);
        } else {
            if ($value)
                $new_array[$key] = $value;
            else
                $new_array[$key] = '';
        }
    }
    return $new_array;
}

// Função que converte um Array em Retorno de URL
// Entrada: Array
// Saída: String concatenada
// Autor: Naylor - 29/07
function mapURL($array) {
    foreach ($array as $key => $value) {
        $ret[] = "$key=" . urlencode($value);
    }
    return implode('&', $ret);
}

// Função que atualiza o BD-RUCKUS
function updateDataBase() {
    try {
        $argv[2] = 1;
        require PATH . LIB . '/migration/ruckusWeb.php';
        $argv[0] = 'db:migrate';

        $main = new Ruckusing_FrameworkRunner($db_config, $argv);
        $ret = $main->execute();
        $argv = null;

        if (strpos($ret, 'relevant') !== false)
            return false;
        else
            return true;
    } catch (Exception $e) {
        print $e;
        return false;
    }
}

// RETORNA O IP DO CLIENTE, UTILIZADO PARA O LOGIN
function getClientIP() {

    if (isset($_SERVER)) {

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            return $_SERVER["HTTP_X_FORWARDED_FOR"];

        if (isset($_SERVER["HTTP_CLIENT_IP"]))
            return $_SERVER["HTTP_CLIENT_IP"];

        return $_SERVER["REMOTE_ADDR"];
    }

    if (getenv('HTTP_X_FORWARDED_FOR'))
        return getenv('HTTP_X_FORWARDED_FOR');

    if (getenv('HTTP_CLIENT_IP'))
        return getenv('HTTP_CLIENT_IP');

    return getenv('REMOTE_ADDR');
}

function xss_clean($data) {
    // Fix &entity\n;
    $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

    do {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    } while ($old_data !== $data);

    // we are done...
    return $data;
}

?>