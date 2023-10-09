<?php

function url(?string $uri = null): string 
{
    if($uri) {
        if(strpos($uri, 'http://') !== false || strpos($uri, 'https://') !== false) {
            return $uri;
        }

        return ENV['app_url'] . "/{$uri}";
    }

    return ENV['app_url'];
}

function generatePassword(
    int $length = 12, 
    bool $upperCase = true, 
    bool $lowerCase = true, 
    bool $numbers = true, 
    bool $specials = false
): string {
    $ma = 'ABCDEFGHIJKLMNOPQRSTUVYXWZ';
    $mi = 'abcdefghijklmnopqrstuvyxwz';
    $nu = '0123456789';
    $si = '!@#$%¨&*()_+=';
    
    $password = '';
    if($upperCase) { $password .= str_shuffle($ma); }
    if($lowerCase) { $password .= str_shuffle($mi); }
    if($numbers) { $password .= str_shuffle($nu); }
    if($specials) { $password .= str_shuffle($si); }

    return substr(str_shuffle($password), 0, $length);
}

function verifyCell(string $tel): bool 
{
    return preg_match("/^(?:(?:\+|00)?(55)\s?)?(?:\(?([1-9][0-9])\)?\s?)?(?:((?:9\d|[2-9])\d{3})\-?(\d{4}))$/", $tel) ? true : false;
}

function validateCNPJ(string $cnpj): bool 
{
	if(empty($cnpj)) {
		return false;
	}

	$cnpj = preg_replace("/[^0-9]/", "", $cnpj);
	$cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);

	if(strlen($cnpj) != 14) {
		return false;
	} elseif ($cnpj == '00000000000000' || 
		$cnpj == '11111111111111' || 
		$cnpj == '22222222222222' || 
		$cnpj == '33333333333333' || 
		$cnpj == '44444444444444' || 
		$cnpj == '55555555555555' || 
		$cnpj == '66666666666666' || 
		$cnpj == '77777777777777' || 
		$cnpj == '88888888888888' || 
		$cnpj == '99999999999999') {
		return false;
	 } else {
		$j = 5;
		$k = 6;
		$soma1 = 0;
		$soma2 = 0;
		for($i = 0; $i < 13; $i++) {
			$j = $j == 1 ? 9 : $j;
			$k = $k == 1 ? 9 : $k;
			$soma2 += ($cnpj[$i] * $k);
			if($i < 12) {
				$soma1 += ($cnpj[$i] * $j);
			}
			$k--;
			$j--;
		}
		$digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
		$digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;

		return (($cnpj[12] == $digito1) && ($cnpj[13] == $digito2));
	}
}

function validateCPF(string $cpf): bool
{
    $cpf = "$cpf";
    if(strpos($cpf, '-') !== false) {
        $cpf = str_replace('-', '', $cpf);
    }
    if(strpos($cpf, '.') !== false) {
        $cpf = str_replace('.', '', $cpf);
    }
    $sum = 0;
    $cpf = str_split( $cpf );
    $cpftrueverifier = array();
    $cpfnumbers = array_splice( $cpf , 0, 9 );
    $cpfdefault = array(10, 9, 8, 7, 6, 5, 4, 3, 2);
    for($i = 0; $i <= 8; $i++) {
        $sum += $cpfnumbers[$i]*$cpfdefault[$i];
    }
    $sumresult = $sum % 11;  
    if($sumresult < 2) {
        $cpftrueverifier[0] = 0;
    } else {
        $cpftrueverifier[0] = 11-$sumresult;
    }
    $sum = 0;
    $cpfdefault = array(11, 10, 9, 8, 7, 6, 5, 4, 3, 2);
    $cpfnumbers[9] = $cpftrueverifier[0];
    for($i = 0; $i <= 9; $i++) {
        $sum += $cpfnumbers[$i]*$cpfdefault[$i];
    }
    $sumresult = $sum % 11;
    if($sumresult < 2){
        $cpftrueverifier[1] = 0;
    } else {
        $cpftrueverifier[1] = 11 - $sumresult;
    }
    $returner = false;
    if($cpf == $cpftrueverifier) {
        $returner = true;
    }
    $cpfver = array_merge($cpfnumbers, $cpf);
    if(count(array_unique($cpfver)) == 1 || $cpfver == array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0)) {
        $returner = false;
    }
    return $returner;
}

function cleanDocument(string $str): mixed 
{
    $str = str_replace(' ', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('/', '', $str);
    return preg_replace('/[^A-Za-z0-9\-]/', '', $str);
}

function slugify(string $str, string $delimiter = '-'): string
{
    return strtolower(
        trim(
            preg_replace(
                '/[\s-]+/', 
                $delimiter, 
                preg_replace(
                    '/[^A-Za-z0-9-]+/', 
                    $delimiter, 
                    preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str)))
                )
            ), 
            $delimiter
        )
    );
}

function writeIniFile(array $array, string $path, bool $hasSections = false): bool 
{
    $content = ''; 
    if($hasSections) { 
        foreach($array as $key => $elem) { 
            $content .= '[' . $key . "]\n"; 
            foreach($elem as $key2 => $elem2) { 
                if(is_array($elem2)) { 
                    for($i = 0 ;$i < count($elem2); $i++) { 
                        $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n"; 
                    } 
                } elseif($elem2 == "") {
                    $content .= $key2 . " = \n"; 
                } else {
                    $content .= $key2 . " = \"" . $elem2 . "\"\n"; 
                }
            } 
        } 
    } else { 
        foreach($array as $key => $elem) { 
            if(is_array($elem)) { 
                for($i = 0; $i < count($elem); $i++) { 
                    $content .= $key . "[] = \"" . $elem[$i] . "\"\n"; 
                } 
            } elseif($elem == "") {
                $content .= $key . " = \n";
            } else {
                $content .= $key . " = \"" . $elem . "\"\n"; 
            }
        } 
    } 

    if(!$handle = fopen($path, 'w')) { 
        return false; 
    }

    $success = fwrite($handle, html_entity_decode($content));
    fclose($handle); 

    return $success ? true : false; 
}