<?php
    header('Content-Type: text/html; charset=cp1251');
	define('INCLUDE_CHECK',true);
	include("connect.php");
	include("loger.php");
	@$login 		= mysql_real_escape_string($_POST['login']);
	@$postPass	= mysql_real_escape_string($_POST['password']);
	@$client 	= mysql_real_escape_string($_POST['client']);
	@$action		= mysql_real_escape_string($_POST['action']);
	if(!file_exists($uploaddirs)) die ("���� � ������ �� �������� ������! ������� � ���������� ���������� ����.");
	if(!file_exists($uploaddirp)) die ("���� � ������ �� �������� ������! ������� � ���������� ���������� ����.");
	
	if (!preg_match("/^[a-zA-Z0-9_-]+$/", $login) || !preg_match("/^[a-zA-Z0-9_-]+$/", $postPass) || !preg_match("/^[a-zA-Z0-9_-]+$/", $action)) {
	
		echo "errorLogin"; 	
		
	exit;
    }	
	
	if($crypt == 'hash_md5' || $crypt == 'hash_authme' || $crypt == 'hash_xauth' || $crypt == 'hash_cauth' || $crypt == 'hash_joomla' || $crypt == 'hash_wordpress' || $crypt == 'hash_dle' || $crypt == 'hash_launcher' || $crypt == 'hash_drupal' )
	{
	    if($useactivate)
        {
		 $query = mysql_query("SELECT $db_columnUser,$db_columnPass,$db_columnMoney,$db_table.$db_group FROM $db_table WHERE $db_columnUser='$login'") or die("errorsql".$logger->WriteLine($log_date.mysql_error()));  //����� ������ MySQL � m.log
		}
		else
		{
		 $query = mysql_query("SELECT $db_columnUser,$db_columnPass,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'") or die("errorsql".$logger->WriteLine($log_date.mysql_error()));  //����� ������ MySQL � m.log
		}
		$row = mysql_fetch_assoc($query);
		$realPass = $row[$db_columnPass];
		$realUser = $row[$db_columnUser];
	} else if ($crypt == 'hash_ipb' || $crypt == 'hash_vbulletin')
	{
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnPass,$db_columnSalt,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'")) or die("errorsql".$logger->WriteLine($log_date.mysql_error())); //����� ������ MySQL � m.log
		$realPass = $row[$db_columnPass];
		$salt = $row[$db_columnSalt];
		$realUser = $row[$db_columnUser];
	} else if($crypt == 'hash_xenforo')
	{
	    $query = "SELECT $db_table.$db_columnId,$db_table.$db_columnUser,$db_tableOther.$db_columnId,$db_tableOther.$db_columnPass FROM $db_table, $db_tableOther WHERE $db_table.$db_columnId = $db_tableOther.$db_columnId AND $db_table.$db_columnUser='{$login}'";
		$result = mysql_query($query)or die("errorsql".$logger->WriteLine($log_date.mysql_error())); //����� ������ MySQL � m.log
		$row = mysql_fetch_assoc($result);
		$realPass = substr($row[$db_columnPass],22,64);
		$realUser = $row[$db_columnUser];
		$salt = substr($row[$db_columnPass],105,64);
	} else die("badhash"); $checkPass = $crypt();

if($useantibrut)
{
	
    $ip  = getenv('REMOTE_ADDR');	
    $time = time();
    $bantime = $time+(10);

    $tbip = mysql_query("Select sip,time From sip Where sip='$ip' And time>'$time'") or die("errorsql".$logger->WriteLine($log_date.mysql_error())); //����� ������ MySQL � m.log
    $row = mysql_fetch_assoc($tbip);
    $real = $row['sip'];
    if($ip == $real)
    {
	 $query = mysql_query("SELECT * FROM `sip` WHERE `sip` > 0;") or die("errorsql".$logger->WriteLine($log_date.mysql_error()));
         while($result = mysql_fetch_assoc($query))
    {
     if($result['time'] < $time) {
        @mysql_query("DELETE FROM `sip` WHERE `sip`='{$result['sip']}';");
     }
    }
     echo 'temp'; 		
     exit;
    }
	
    if ($login !== $realUser)
    {
    exit ('errorLogin'.mysql_query("INSERT INTO sip (sip, time)VALUES ('$ip', '$bantime')"));
    }
	if(!strcmp($realPass,$checkPass) == 0 || !$realPass) die("errorLogin".mysql_query("INSERT INTO sip (sip, time)VALUES ('$ip', '$bantime')"));

    } else {
    if ($login !== $realUser)
    {
    exit ('errorLogin');
    }
	if(!strcmp($realPass,$checkPass) == 0 || !$realPass) die("errorLogin");
    }
	
if($useactivate)
{	
	if($row[$db_group] == $noactive)	
	{
    exit ("��� ������� �� �����������!");
    }
}
if($useban)
{
   $time = time();
   $tipe = '2';
   $result = mysql_query("Select name From $banlist Where name='$login' And type<'$tipe' And temptime>'$time'") or die ("errorsql");
   if(mysql_num_rows($result) == 1)
    {
      $result2 = mysql_query("Select name,temptime From $banlist Where name='$login' And type<'$tipe' And temptime>'$time'") or die ("errorsql");
      $row = mysql_fetch_assoc($result2);
      exit ('��������� ��� �� '.date('d.m.Y�. H:i', $row['temptime'])." �� ������� �������");
    }
      $result = mysql_query("Select name From $banlist Where name='$login' And type<'$tipe' And temptime='0'") or die ("errorsql");
      if(mysql_num_rows($result) == 1)
    {
      exit ("������ ���");
    }
}



	
	
	if($action == 'getpersonal' && !$usePersonal) die("������������� �� ���������");
	if($action == 'uploadskin' && !$canUploadSkin) die("������� ����������");
	if($action == 'uploadcloak' && !$canUploadCloak) die("������� ����������");
	if($action == 'buyvip' && !$canBuyVip) die("������� ����������");
	if($action == 'buypremium' && !$canBuyPremium) die("������� ����������");
	if($action == 'buyunban' && !$canBuyUnban) die("������� ����������");
	if($action == 'exchange' && !$canExchangeMoney) die("������� ����������");
	if($action == 'activatekey' && !$canActivateVaucher) die("������� ����������");

	if($action == 'exchange' || $action == 'getpersonal')
	{
		$rowicon = mysql_fetch_assoc(mysql_query("SELECT username,balance FROM iConomy WHERE username='$login'"));
		$iconregistered = true;
		if(!$rowicon['balance'])
		{
			mysql_query("INSERT INTO `iConomy` (`username`, `balance`, `status`) VALUES ('$login', '$initialIconMoney.00', '0');") or $iconregistered = false;
		}
	}
    
	if($action == 'auth')
	{
		if(!file_exists("clients/".$client."/bin/client.zip") || !file_exists("clients/".$client."/bin/minecraft.jar") ||
		   !file_exists("clients/".$client."/bin/libraries.jar")  || !file_exists("clients/".$client."/bin/Forge.jar")  ||
		   !file_exists("clients/".$client."/bin/extra.jar") || !file_exists("clients/".$client."/mods/")               || 
		   !file_exists("clients/".$client."/coremods/") || !file_exists("clients/".$client."/bin/assets.zip")) 
		   die("������ $client �� ������");
		   
	    
	    $chars="0123456789abcdef";
        $max=32;
        $size=StrLen($chars)-1;
        $password=null;
        while($max--)
        $password.=$chars[rand(0,$size)];
	    $chars2="0123456789abcdef";
        $max2=32;
        $size2=StrLen($chars)-1;
        $password2=null;
        while($max2--)
        $password2.=$chars2[rand(0,$size2)];
		
		$sessid 		= "token:".$password.":".$password2;
		//$sessid 		= generateSessionId();
		$md5zip			= md5_file("clients/".$client."/bin/client.zip");
		$md5czip        = strtoint(xorencode($md5zip, $protectionKey));
		$md52zip		= md5_file("clients/".$client."/bin/assets.zip");
		$md52czip       = strtoint(xorencode($md52zip, $protectionKey));
		$md5jar         = md5_file("clients/".$client."/bin/minecraft.jar");
		$md5cjar        = strtoint(xorencode($md5jar, $protectionKey));
		$md5lwjql		= md5_file("clients/".$client."/bin/libraries.jar");
		$md5clwjql      = strtoint(xorencode($md5lwjql, $protectionKey));
		$md5lwjql_util	= md5_file("clients/".$client."/bin/Forge.jar");
		$md5clwjql_util = strtoint(xorencode($md5lwjql_util, $protectionKey));
		$md5jinput		= md5_file("clients/".$client."/bin/extra.jar");
		$md5cjinput     = strtoint(xorencode($md5jinput, $protectionKey));
		mysql_query("UPDATE $db_table SET $db_columnSesId='$sessid' WHERE $db_columnUser = '$login'") or die ("errorsql.");
		echo "$md5czip<:>$md52czip<:>$md5cjar<:>$md5clwjql<:>$md5clwjql_util<:>$md5cjinput<:>$masterversion<br>".
		$realUser.'<:>'.strtoint(xorencode($sessid, $protectionKey)).'<br>';
		
		$colMods = 0; $files = scandir("clients/".$client."/mods");
		for($i=0; $i < sizeof($files); $i++) if(substr($files[$i], -4) == ".zip" || substr($files[$i], -4) == ".jar" || substr($files[$i], -8) == ".litemod")
		{
			$echo1 = $files[$i].":>".md5_file("clients/".$client."/mods/".$files[$i])."<:>"; $colMods++;
			echo str_replace(' ', '%20', $echo1);
		} if($colMods == 0);
		echo '::';
		$colCoreMods = 0; $coremods = scandir("clients/".$client."/coremods");
		for($i=0; $i < sizeof($coremods); $i++) if(substr($coremods[$i], -4) == ".zip" || substr($coremods[$i], -4) == ".jar")
		{
			$echo2 = $coremods[$i].":>".md5_file("clients/".$client."/coremods/".$coremods[$i])."<:>"; $colCoreMods++;
			echo str_replace(' ', '%20', $echo2);
		} if($colCoreMods == 0) echo "nomods";

	} else
  
	if($action == 'getpersonal')
	{
		$realmoney = $row[$db_columnMoney];

		if($iconregistered)
		{
			$row = mysql_fetch_assoc(mysql_query("SELECT username,balance FROM iConomy WHERE username='$login'"));
			$iconmoney = $row['balance'];
		} else $iconmoney = "0.0";
		
		if($canBuyVip || $canBuyPremium)
		{
			$sql = mysql_query("SELECT name,permission,value FROM permissions WHERE name='$login'");
			$datetoexpire = 0;
			if(!$sql) $ugroup = 'User'; else
			{
				$row = mysql_fetch_assoc($sql);
				$group = $row['permission'];
				if($group == 'group-premium-until')
				{
					$ugroup = 'Premium';
					$datetoexpire = $row['value'];
				} else if($group == 'group-vip-until')
				{
					$ugroup = 'VIP';
					$datetoexpire = $row['value'];
				} else $ugroup = 'User';
			}
		} else
		{
			$datetoexpire = 0;
			$ugroup = 'User';
		}
	
		if($canUseJobs)
		{
			$sql = mysql_fetch_assoc(mysql_query("SELECT job FROM jobs WHERE username='$login'"));
			$query = $sql['job'];
			if($query == '') { $jobname = "�����������"; $joblvl = 0; $jobexp = 0; } else
			{
				$result = mysql_query("SELECT * FROM jobs WHERE username='$login'");
				while($data = mysql_fetch_assoc($result))
				{
					if ($data["job"] === 'Miner') $data["job"] = '������';
					if ($data["job"] === 'Woodcooter') $data["job"] = '�������';
					if ($data["job"] === 'Builder') $data["job"] = '���������';
					if ($data["job"] === 'Digger') $data["job"] = '�����';
					if ($data["job"] === 'Farmer') $data["job"] = '������';
					if ($data["job"] === 'Hunter') $data["job"] = '�������';
					if ($data["job"] === 'Fisherman') $data["job"] = '�����';
					if ($data["job"] === 'Weaponsmith') $data["job"] = '���������';
					
					$jobname = $data['job'];
					$joblvl = $data["level"];
					$jobexp = $data["experience"];
				}
			}
		} else { $jobname = "nojob"; $joblvl = -1; $jobexp = -1; }
		
		$canUploadSkin 		= (int)$canUploadSkin;
		$canUploadCloak		= (int)$canUploadCloak;
		$canBuyVip	   		= (int)$canBuyVip;
		$canBuyPremium 		= (int)$canBuyPremium;
		$canBuyUnban   		= (int)$canBuyUnban;
		$canActivateVaucher = (int)$canActivateVaucher;
		$canExchangeMoney	= (int)$canExchangeMoney;
	
		if($canBuyUnban == 1)
		{
		    $ty = 2;
			$sql2 = mysql_fetch_assoc(mysql_query("SELECT name,type FROM $banlist WHERE name='$login' and type<'$ty'"));
			$query2 = $sql2['name'];
			if(strcasecmp($query2, $login) == 0) $ugroup = "Banned";
		}
		
		echo "$canUploadSkin$canUploadCloak$canBuyVip$canBuyPremium$canBuyUnban$canActivateVaucher$canExchangeMoney<:>$iconmoney<:>$realmoney<:>$cloakPrice<:>$vipPrice<:>$premiumPrice<:>$unbanPrice<:>$exchangeRate<:>$ugroup<:>$datetoexpire<:>$jobname<:>$joblvl<:>$jobexp";
	} else
//============================================������� ��====================================//

	if($action == 'activatekey')
	{
		@$key = mysql_real_escape_string($_POST['key']);
		$row = mysql_fetch_assoc(mysql_query("SELECT * FROM `$db_tableMoneyKeys` WHERE `$db_columnKey` = '$key'"));
		$amount = $row[$db_columnAmount];
		if($amount)
		{
			mysql_query("UPDATE `$db_table` SET $db_columnMoney = $db_columnMoney + $amount WHERE $db_columnUser='$login'");
			mysql_query("DELETE FROM `$db_tableMoneyKeys` WHERE `$db_columnKey` = '$key'");
			$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
			$money = $row[$db_columnMoney];
			echo "success:".$money;
		} else echo "���� ������ �������";
	} else

	if($action == 'uploadskin')
	{
		if(!is_uploaded_file($_FILES['ufile']['tmp_name'])) die("���� �� ������");
		$imageinfo = getimagesize($_FILES['ufile']['tmp_name']);
		if($imageinfo['mime'] != 'image/png' || $imageinfo["0"] != '64' || $imageinfo["1"] != '32') die("���� ���� �� �������� ������ �����");
		
		
		$uploadfile = "".$uploaddirs."/".$login.".png";

		if(move_uploaded_file($_FILES['ufile']['tmp_name'], $uploadfile)) echo "success";
		else echo "errorsql �������� �����";
	} else
	
	if($action == 'uploadcloak')
	{
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		$query = $row[$db_columnMoney]; if($query < $cloakPrice) die("� ��� ������������ �������!");
		
		if(!is_uploaded_file($_FILES['ufile']['tmp_name'])) die("���� �� ������");
		$imageinfo = getimagesize($_FILES['ufile']['tmp_name']);
		$go = false;
		if(($imageinfo['mime'] != 'image/png' || $imageinfo["0"] == '64' || $imageinfo["1"] == '32')){
		$go = true;
		} else echo '���� ���� �� �������� ������ �����';
		if($go) {
		$uploadfile = "".$uploaddirp."/".$login.".png";

		if(!move_uploaded_file($_FILES['ufile']['tmp_name'], $uploadfile)) die("errorsql �������� �����");
		mysql_query("UPDATE $db_table SET $db_columnMoney = $db_columnMoney - $cloakPrice WHERE $db_columnUser='$login'");
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		echo "success:".$row[$db_columnMoney];
	}} else
	
	if($action == 'buyvip')
	{
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		$query = $row[$db_columnMoney]; if($query < $vipPrice) die("� ��� ������������ �������!");
		
		$sql = mysql_query("SELECT name,permission FROM permissions WHERE name='$login'");
		$row = mysql_fetch_assoc($sql);
		$group = $row['permission'];
		
		$pexdate = time() + 2678400;
		if($group == 'group-vip-until')
		{
			mysql_query("UPDATE $db_table SET $db_columnMoney=$db_columnMoney-$vipPrice WHERE $db_columnUser='$login'")or die("errorsql.");
			mysql_query("UPDATE permissions SET value=value+2678400 WHERE name='$login'");
		} else
		{
			mysql_query("INSERT INTO permissions (id, name, type, permission, world, value) VALUES (NULL, '$login', '1', 'group-vip-until', ' ', '$pexdate')")or die("errorsql.");
			mysql_query("INSERT INTO permissions_inheritance (id, child, parent, type, world) VALUES (NULL, '$login', 'vip', '1', NULL)")or die("errorsql.");
			mysql_query("UPDATE $db_table SET $db_columnMoney=$db_columnMoney-$vipPrice WHERE $db_columnUser='$login'")or die("errorsql.");
		}
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		echo "success:".$row[$db_columnMoney].":";
		$row = mysql_fetch_assoc(mysql_query("SELECT name,permission,value FROM permissions WHERE name='$login'"));
		echo $row['value'];
	} else
	
	if($action == 'buypremium')
	{
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		$query = $row[$db_columnMoney]; if($query < $premiumPrice) die("� ��� ������������ �������!");
		
		$sql = mysql_query("SELECT name,permission FROM permissions WHERE name='$login'");
		$row = mysql_fetch_assoc($sql);
		$group = $row['permission'];
		
		$pexdate = time() + 2678400;
		if($group == 'group-premium-until')
		{
			mysql_query("UPDATE $db_table SET $db_columnMoney=$db_columnMoney-$premiumPrice WHERE $db_columnUser='$login'")or die("errorsql.");
			mysql_query("UPDATE permissions SET value=value+2678400 WHERE name='$login'");
		} else
		{
			mysql_query("INSERT INTO permissions (id, name, type, permission, world, value) VALUES (NULL, '$login', '1', 'group-premium-until', ' ', '$pexdate')")or die("errorsql.");
			mysql_query("INSERT INTO permissions_inheritance (id, child, parent, type, world) VALUES (NULL, '$login', 'premium', '1', NULL)")or die("errorsql.");
			mysql_query("UPDATE $db_table SET $db_columnMoney=$db_columnMoney-$premiumPrice WHERE $db_columnUser='$login'")or die("errorsql.");
		}
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		echo "success:".$row[$db_columnMoney].":";
		$row = mysql_fetch_assoc(mysql_query("SELECT name,permission,value FROM permissions WHERE name='$login'"));
		echo $row['value'];
	} else
	
	if($action == 'buyunban')
	{
		$sql1 = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		$query1 = $sql1[$db_columnMoney];
		$sql2 = mysql_fetch_assoc(mysql_query("SELECT name FROM $banlist WHERE name='$login'"));
		$query2 = $sql2['name'];
		
		if(strcasecmp($query2, $login) == 0)
		{
			if($query1 >= $unbanPrice)
			{
				if($canBuyVip || $canBuyPremium)
				{
					$sql = mysql_query("SELECT name,permission,value FROM permissions WHERE name='$login'");
					$row = mysql_fetch_assoc($sql);
					$group = $row['permission'];
					if(!$sql) $ugroup = 'User'; else
					{
						if($group == 'group-premium-until') $ugroup = 'Premium';
						else if($group == 'group-vip-until') $ugroup = 'VIP';
						else $ugroup = 'User';
					}
				} else $ugroup = 'User';
				
				mysql_query("DELETE FROM $banlist WHERE name='$login'");
				mysql_query("UPDATE $db_table SET $db_columnMoney=$db_columnMoney-$unbanPrice WHERE $db_columnUser='$login'")or die("errorsql.");
				$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
				
				echo "success:".$row[$db_columnMoney].":".$ugroup;
			} else die('������������ �������.');
		} else die("�� �� ��������");
	} else

	if($action == 'exchange')
	{
		@$wantbuy = mysql_real_escape_string((int)$_POST['buy']);
		$gamemoneyadd = ($wantbuy * $exchangeRate);
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		$query = $row[$db_columnMoney];
		
		if($wantbuy == '' || $wantbuy < 1) die("�� �� ����� �����!");
		if(!$iconregistered) die("��� ��� � ���� iConomy");
		if($query < $wantbuy) die("� ��� ������������ �������!");
		
		mysql_query("UPDATE iConomy SET balance = balance + $gamemoneyadd WHERE username='$login'");
		mysql_query("UPDATE $db_table SET $db_columnMoney = $db_columnMoney - $wantbuy WHERE $db_columnUser='$login'");
		
		$row = mysql_fetch_assoc(mysql_query("SELECT $db_columnUser,$db_columnMoney FROM $db_table WHERE $db_columnUser='$login'"));
		$money = $row[$db_columnMoney];
		
		$row = mysql_fetch_assoc(mysql_query("SELECT username,balance FROM iConomy WHERE username='$login'"));
		$iconmoney = $row['balance'];
		
		echo "success:".$money.":".$iconmoney;
	} else echo "������ ��������� �������";
	
	//===================================== ��������������� ������� ==================================//

	function xorencode($str, $key)
	{
		while(strlen($key) < strlen($str))
		{
			$key .= $key;
		}
		return $str ^ $key;
	}

	function strtoint($text)
	{
		$res = "";
		for ($i = 0; $i < strlen($text); $i++) $res .= ord($text{$i}) . "-";
		$res = substr($res, 0, -1);
		return $res;
	}

	function generateSessionId()
	{
		srand(time());
		$randNum = rand(1000000000, 2147483647).rand(1000000000, 2147483647).rand(0,9);
		return $randNum;
	}

	function hash_xauth()
	{
		global $realPass, $postPass;
		$cryptPass = false;
		$saltPos = (strlen($postPass) >= strlen($realPass) ? strlen($realPass) : strlen($postPass));
		$salt = substr($realPass, $saltPos, 12);
		$hash = hash('whirlpool', $salt . $postPass);
		$cryptPass = substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
		return $cryptPass;
	}

	function hash_md5()
	{
		global $postPass;
		$cryptPass = false;
		$cryptPass = md5($postPass);
		return $cryptPass;
	}

	function hash_launcher()
	{
		global $postPass;
		$cryptPass = false;
		$cryptPass = md5($postPass);
		return $cryptPass;
	}

	function hash_dle()
	{
		global $postPass;
		$cryptPass = false;
		$cryptPass = md5(md5($postPass));
		return $cryptPass;
	}

	function hash_cauth()
	{
		global $realPass, $postPass;
		$cryptPass = false;
		if (strlen($realPass) < 32)
		{
			$cryptPass = md5($postPass);
			$rp = str_replace('0', '', $realPass);
			$cp = str_replace('0', '', $cryptPass);
			(strcasecmp($rp,$cp) == 0 ? $cryptPass = $realPass : $cryptPass = false);
		}
		else $cryptPass = md5($postPass);
		return $cryptPass;
	}

	function hash_authme()
	{
		global $realPass, $postPass;
		$cryptPass = false;
		$ar = preg_split("/\\$/",$realPass);
		$salt = $ar[2];
		$cryptPass = '$SHA$'.$salt.'$'.hash('sha256',hash('sha256',$postPass).$salt);
		return $cryptPass;
	}

	function hash_joomla()
	{
		global $realPass, $postPass;
		$cryptPass = false;
		$parts = explode( ':', $realPass);
		$salt = $parts[1];
		$cryptPass = md5($postPass . $salt) . ":" . $salt;
		return $cryptPass;
	}

	function hash_ipb()
	{
		global $postPass, $salt;
		$cryptPass = false;
		$cryptPass = md5(md5($salt).md5($postPass));
		return $cryptPass;
	}

	function hash_xenforo()
	{
		global $postPass, $salt;
		$cryptPass = false;
		$cryptPass = hash('sha256', hash('sha256', $postPass) . $salt);
		return $cryptPass;
	}

	function hash_wordpress()
	{
		global $realPass, $postPass;
		$cryptPass = false;
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$count_log2 = strpos($itoa64, $realPass[3]);
		$count = 1 << $count_log2;
		$salt = substr($realPass, 4, 8);
		$input = md5($salt . $postPass, TRUE);
		do $input = md5($input . $postPass, TRUE);
		while (--$count);        
		$output = substr($realPass, 0, 12);
		$count = 16;
		$i = 0;
		do 
		{
			$value = ord($input[$i++]);
			$cryptPass .= $itoa64[$value & 0x3f];
			if ($i < $count) $value |= ord($input[$i]) << 8;
			$cryptPass .= $itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count) break;
			if ($i < $count) $value |= ord($input[$i]) << 16;
			$cryptPass .= $itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count) break;
			$cryptPass .= $itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);
		$cryptPass = $output . $cryptPass;
		return $cryptPass;
	}

	function hash_vbulletin()
	{
		global $postPass, $salt;
		$cryptPass = false;
		$cryptPass = md5(md5($postPass) . $salt);
		return $cryptPass;
	}

	function hash_drupal()
	{
		global $postPass, $realPass;
		$cryptPass = false;
		$setting = substr($realPass, 0, 12);
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$count_log2 = strpos($itoa64, $setting[3]);
		$salt = substr($setting, 4, 8);
		$count = 1 << $count_log2;
		$input = hash('sha512', $salt . $postPass, TRUE);
		do $input = hash('sha512', $input . $postPass, TRUE);
		while (--$count);

		$count = strlen($input);
		$i = 0;
	  
		do
		{
			$value = ord($input[$i++]);
			$cryptPass .= $itoa64[$value & 0x3f];
			if ($i < $count) $value |= ord($input[$i]) << 8;
			$cryptPass .= $itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count) break;
			if ($i < $count) $value |= ord($input[$i]) << 16;
			$cryptPass .= $itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count) break;
			$cryptPass .= $itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);
		$cryptPass =  $setting . $cryptPass;
		$cryptPass =  substr($cryptPass, 0, 55);
		return $cryptPass;
	}

?>
