<?php
require('sslib.php');

$servers = GetServerList();

if(!file_exists(SS_PATH_RRD))
{
	mkdir(SS_PATH_RRD,0777);
}

foreach ($servers as $n => $s)
{
	if($s["type"]=="shoutcast")
        {
                $audience[$i]=GetShoutcastStats($s["host"],$s["port"]);
                $rddfile = SS_PATH_RRD."/{$s["host"]}.".$s["port"].".rrd";
        }
        else
        {
                $audience[$i]=GetIcecastStats($s["host"],$s["port"],$s["mpoint"]);
                $rddfile = SS_PATH_RRD."/{$s["host"]}.".$s["port"].".{$s["mpoint"]}.rrd";
        }

	if(!file_exists($rddfile))
	{
		system(SS_RRDTOOL_COMMAND.
			   ' create '.$rddfile.
			   'DS:audience:GAUGE:600:U:U '.
			   'RRA:AVERAGE:0.5:1:288 '.
			   'RRA:AVERAGE:0.5:6:336 '.
			   'RRA:AVERAGE:0.5:24:420 '.
			   'RRA:AVERAGE:0.5:288:365');
	}
	system(SS_RRDTOOL_COMMAND.
		   ' update '.$rddfile.' '.
		   '-t audience '.
		   'N:'.$audience[$i]['current']);
}
