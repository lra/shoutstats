<?
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
require('sslib.php');

$server = $_GET['server'];
$servers = GetServerList();

if(!file_exists(SS_PATH_GFX))
	mkdir(SS_PATH_GFX,0777);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
 <? if (strlen($server)) : ?>
 <title>Shoutstats for <?=SS_NAME?> - <?=$server?></title>
 <? else: ?>
 <title>Shoutstats for <?=SS_NAME?></title>
 <? endif; ?>
 <meta http-equiv="refresh" content="300" />
 <link rel="stylesheet" href="shoutstats.css" type="text/css" media="screen" />
</head>
<body>

<h1>Shoutstats for <?=SS_NAME?></h1>

<form action="<?=basename($SCRIPT_FILENAME)?>" method="get">
<fieldset>
<legend>Shoutstats Server Selection</legend>
<label for="server">Show statistics for</label>
<select name="server" id="server">
<option value="">All servers together</option>
<?
// combobox items generation of a list of server
foreach ($servers as $c_name => $c_server)
{
	if ($c_name == $server) : ?>
                <option value="<?=$c_name?>" selected><?=$c_name?> (<?=$servers[$c_name]['type']?>)</option>
        <? else: ?>
                <option value="<?=$c_name?>"><?=$c_name?> (<?=$servers[$c_name]['type']?>)</option>
        <? endif;

}
?>
</select>
<input type="submit" value="Select" />
</fieldset>
</form>

<?
// a specific server has been selected
if(strlen($server)):
if($servers[$server]['type']=='icecast') $mp=".".$servers[$server]['mpoint'];

// generate and display the 24 hours server specific graph
$txt_freq = 'hourly';
$rrdgfx=SS_PATH_GFX."/hourly-{$servers[$server]['host']}.{$servers[$server]['port']}{$mp}.png";
$rrdfile=SS_PATH_RRD."/{$servers[$server]['host']}.{$servers[$server]['port']}{$mp}.rrd";
exec(SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG DEF:myaudience=$rrdfile:audience:AVERAGE LINE2:myaudience#FF0000");
DisplayGraph($txt_freq, $rrdgfx);

// generate and display the 7 days server specific graph
$txt_freq = 'daily';
$rrdgfx=SS_PATH_GFX."/daily-{$servers[$server]['host']}.{$servers[$server]['port']}{$mp}.png";
exec(SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG -s e-1w DEF:myaudience=$rrdfile:audience:AVERAGE LINE2:myaudience#FF0000");
DisplayGraph($txt_freq, $rrdgfx);

// generate and display the 5 weeks server specific graph
$txt_freq = 'weekly';
$rrdgfx=SS_PATH_GFX."/weekly-{$servers[$server]['host']}.{$servers[$server]['port']}{$mp}.png";
exec(SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG -s e-5w DEF:myaudience=$rrdfile:audience:AVERAGE LINE2:myaudience#FF0000");
DisplayGraph($txt_freq, $rrdgfx);

// generate and display the 12 months server specific graph
$txt_freq = 'monthly';
$rrdgfx=SS_PATH_GFX."/monthly-{$servers[$server]['host']}.{$servers[$server]['port']}{$mp}.png";
exec(SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG -s e-1y DEF:myaudience=$rrdfile:audience:AVERAGE LINE2:myaudience#FF0000");
DisplayGraph($txt_freq, $rrdgfx);

// no server has been selected
else:

$rrdexecend = ' ';
$i = 0;
foreach ($servers as $n => $s)
{
	$mp=""; if($s['type']=='icecast') $mp=".".$s['mpoint'];
        $rrdfile = SS_PATH_RRD."/{$s['host']}.{$s['port']}{$mp}.rrd";
	$rrdexecend .= "DEF:myaudience$i=$rrdfile:audience:AVERAGE ";
	$rrdexecend .= "CDEF:audience$i=myaudience$i,UN,0,myaudience$i,IF ";
	$i++;
}
if(count($servers)>1) {
	$rrdexecend .= 'CDEF:mytotal=';
	for($i=0; $i<count($servers); $i++)
		$rrdexecend .= "audience$i,";
	for($i=0; $i<count($servers)-2; $i++)
		$rrdexecend .= '+,';
	$rrdexecend .= '+ ';
} else {
	$rrdexecend .= 'CDEF:mytotal=audience0 ';
}
$rrdexecend .= 'LINE2:mytotal#FF0000';

// generate and display the 24 hours graph
$txt_freq = 'hourly';
$rrdgfx = SS_PATH_GFX.'/hourly-all.png';
$rrdexecstart = SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG";
exec($rrdexecstart.$rrdexecend);
DisplayGraph($txt_freq, $rrdgfx);

// generate and display the 7 days graph
$txt_freq = 'daily';
$rrdgfx = SS_PATH_GFX.'/daily-all.png';
$rrdexecstart = SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG -s e-1w";
exec($rrdexecstart.$rrdexecend);
DisplayGraph($txt_freq, $rrdgfx);

// generate and display the 5 weeks graph
$txt_freq = 'weekly';
$rrdgfx = SS_PATH_GFX.'/weekly-all.png';
$rrdexecstart = SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG -s e-5w";
exec($rrdexecstart.$rrdexecend);
DisplayGraph($txt_freq, $rrdgfx);

// generate and display the 12 months graph
$txt_freq = 'monthly';
$rrdgfx = SS_PATH_GFX.'/monthly-all.png';
$rrdexecstart = SS_RRDTOOL_COMMAND . " graph $rrdgfx --lazy -l 0 -a PNG -s e-1y";
exec($rrdexecstart.$rrdexecend);
DisplayGraph($txt_freq, $rrdgfx);

endif;
?>

<p>
<a href="http://www.gnu.org/copyleft/gpl.html">GPL</a>
<a href="http://www.glop.org/shoutstats/">Shoutstats</a> 
<a href="CHANGES"><?=SS_VERSION?></a> -
<a href="http://validator.w3.org/check?uri=referer">XHTML/1.1</a> and 
<a href="http://jigsaw.w3.org/css-validator/check/referer">CSS2</a> compliant
</p>

</body>
</html>
