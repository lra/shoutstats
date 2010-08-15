<?php
// Shoutstats version
define('SS_VERSION',	'0.8.5');

// The file providing the the config variables
define('SS_CONF',	'config.ini');

// The file providing the server list
define('SS_SERVERS',	'servers.ini');

// The folder containing the generated PNG
define('SS_PATH_GFX',	'gfx');

// The folder containing the RRDtool databases
define('SS_PATH_RRD',	'rrd');

if (!file_exists(SS_CONF))
{
	die(SS_CONF.' file is missing');
}
$configuration = parse_ini_file(SS_CONF, TRUE);
define('SS_RRDTOOL_COMMAND', $configuration['Configuration']['rrdtool_command']);
define('SS_NAME', $configuration['Configuration']['stream_name']);

//if(!is_executable(SS_RRDTOOL_COMMAND))
//	die(SS_RRDTOOL_COMMAND . ' not found. Please update the ' . SS_CONF . ' file');
exec(SS_RRDTOOL_COMMAND, $output);
if(!count($output))
{
	die(SS_RRDTOOL_COMMAND . ' not found. Please update the ' . SS_CONF . ' file');
}

//
// Get the server list from the configuration file
//

function GetServerList()
{
	if (!file_exists(SS_SERVERS))
	{
		die(SS_SERVERS.' file is missing');
	}

	$servers = parse_ini_file(SS_SERVERS, TRUE);
	ksort($servers);

	if (!count($servers))
	{
		die('no server configured, edit the '.SS_CONF.' file');
	}

	return $servers;
}

//
// Get the number of current and max listeners from the specified server
//

function GetShoutcastStats($host, $port)
{
  $fp = fsockopen($host, $port, $errno, $errstr, 30);

  // can't connect =(
  if (!$fp)
  {
	print("$errstr ($errno)<br>\n");
	$server['current'] = 0;
	$server['max'] = 0;
  // oh yes, it can connect
  }
  else
  {
      fputs($fp, "GET /7.html HTTP/1.0\r\nUser-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows NT 5.0)\r\n\r\n");
      while (!feof($fp))
	  {
          $content .= fgets($fp,128);
      }
      fclose($fp);

      $debut = strpos($content, '<body>') + strlen('<body>');
      $fin = strpos($content, '</body>', $debut);
      $string = substr($content, $debut, $fin - $debut);

	  $stats = explode(',', $string);

	// server is up but no source is connected
	if ($stats[1] == 0)
	{
		$server['current'] = 0;
	// everything is ok
	}
	else
	{
		$server['current'] = $stats[0];
	}

	$server['max'] = $stats[3];
  }

	// debug
	// print("$host:$port = {$server['current']}/{$server['max']}\n");
	return $server;
}

//
// Template used to display each generated graph
//

function DisplayGraph($txt_freq, $rrdgfx)
{
	$size = GetImageSize($rrdgfx);
?>
<p>
<?=$txt_freq?> graph:<br />
<img src="<?=$rrdgfx?>" <?=$size[3]?> alt="<?=$txt_freq?> graph" /><br />
</p>
<?
}
