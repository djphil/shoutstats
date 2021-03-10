<?php include_once('inc/config.php'); ?>
<?php include_once('inc/functions.php'); ?>
<?php include_once('inc/servers.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title.' v'.$version ?> by djphil (<?php echo $lisense ; ?>)</title>
    <meta name="description" content="<?php echo $title.' v'.$version ?> by djphil (<?php echo $lisense ; ?>)">
    <meta name="author" content="Philippe Lemaire (djphil)">
    <link rel="icon" href="img/favicon.ico">
    <link rel="author" href="inc/humans.txt" />
    <?php if ($refresh > 0): ?>
    <meta http-equiv="refresh" content="<?php echo $refresh; ?>">
    <?php endif; ?>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <?php if ($theme): ?>
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <?php endif; ?>
    <link href="css/player.css" rel="stylesheet">
    <?php if ($display_ribbon): ?>
    <link href="css/gh-fork-ribbon.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
<?php if ($display_ribbon): ?>
<div class="github-fork-ribbon-wrapper left">
    <div class="github-fork-ribbon">
        <a href="<?php echo $github_url ?> " target="_blank">Fork me on GitHub</a>
    </div>
</div>
<?php endif; ?>
<div class="container">
    <h1>
        <i class="glyphicon glyphicon-flash"></i> <?php echo $title.' v'.$version ?>
        <span class="pull-right">
            <a class="btn btn-primary btn-xsxs infos" href="#" title="Infos Stats">
            <i class="glyphicon glyphicon-info-sign"></i> Infos</a>
            <a class="btn btn-primary btn-xsxs" href="./" title="Refresh Stats">
            <i class="glyphicon glyphicon-refresh"></i> Refresh</a>
        </span>
    </h1>

    <p class="datetime">
        Live <span class="label label-warning">Shoutcast V1 & V2</span> 
        Servers Statistics <span class="pull-right" id="date"></span>
    </p>

    <div class="clearfix"></div>

    <div class="panel panel-default" id="statistics">
        <div class="panel-heading"></div>
        <div class="panel-body text-center">
            Total Servers: <span class="badge badge-default" id="total_servers">0</span> 
            Server Failed: <span class="badge badge-default" id="servers_failed">0</span> 
            Server Online: <span class="badge badge-default" id="servers_online">0</span> 
            Total Listeners: <span class="badge badge-default" id="total_listeners">0</span> 
            Total Uniques: <span class="badge badge-default" id="total_uniques">0</span> 
            Total Peak's: <span class="badge badge-default" id="total_peaks">0</span>
        </div>
        <div class="panel-footer"></div>
    </div>

    <?php
    $total = count($servers);
    $total_listeners = 0;
    $total_uniques = 0;
    $total_peaks = 0;
    $failed = 0;

    foreach($servers as $key => $val)
    {
        $audio  = "audio/mpeg";
        $name   = isset($key) ? $key : "n/a";
        $ip     = isset($val['ip']) ? $val['ip'] : "localhost";
        $port   = isset($val['port']) ? $val['port'] : 8000;
        $sid    = isset($val['sid']) ? "?sid=".$val['sid'] : null;
        $type   = isset($val['type']) ? $val['type'] : "mp3";

        if (strpos($type, 'ogg') == true) $audio = "audio/ogg";
        $buffer = socket_get_7html($ip, $port, $sid);

        $listeners  = isset($buffer[0]) ? $buffer[0] : 0; // CURRENTLISTENERS
        $status     = isset($buffer[1]) ? $buffer[1] : 0; // STREAMSTATUS
        $peak       = isset($buffer[2]) ? $buffer[2] : 0; // PEAKLISTENERS
        $max        = isset($buffer[3]) ? $buffer[3] : 0; // MAXLISTENERS
        $unique     = isset($buffer[4]) ? $buffer[4] : 0; // UNIQUELISTENERS
        $bitrate    = isset($buffer[5]) ? $buffer[5] : 0; // BITRATE
        $song       = isset($buffer[6]) ? $buffer[6] : 0; // SONGTITLE

        if ($max > 0) 
        {
            $pc = round(($listeners / $max * 100));
            if ($pc >= -1 && $pc <= 19) $class = "progress-bar-default";
            else if ($pc >= 20 && $pc <= 39) $class = "progress-bar-info";
            else if ($pc >= 40 && $pc <= 59) $class = "progress-bar-success";
            else if ($pc >= 60 && $pc <= 79) $class = "progress-bar-warning";
            else if ($pc >= 80 && $pc <= 100) $class = "progress-bar-danger";
            else $class = "progress-bar-danger";
        }

        $panel_class = 'default';
        if ($listeners >= $max || !$status) $panel_class = 'danger';
        $status_class = $status ? "success" : "danger";
        $updown = $status ? "up" : "down";
        $status = $status ? "ONLINE" : "OFFLINE";
        $url = "http://".$val['ip'].":".$val['port'];

        echo '<div class="panel panel-'.$panel_class.'">';
        echo '<div class="panel-heading">';
        echo '<b>'.$name.'</b> @ '.$val['ip'].':'.$val['port'];
        echo ' <a class="" href="'.$url.'" target="_blank"><i class="glyphicon glyphicon-link"></i></a>';
        echo '<div class="pull-right"><span class="label label-'.$status_class.'">'.$status.'</span></div>';
        echo '<div class="clearfix"></div>';
        echo '</div>';

        echo '<div class="panel-body stretch">';
        echo '<div class="progress">';
        echo '<div class="progress-bar progress-bar-striped '.$class.'" role="progressbar" aria-valuenow="'.$pc.'" aria-valuemin="0" aria-valuemax="100" style="min-width: 8em; width: '.$pc.'%;">';
        echo '<b>'.$pc.'% of capacity</b>';
        echo '</div>';
        echo '</div>';

        $url = "http://".$val['ip'].":".$val['port']."/;&type=".$type;
        echo '<audio controls preload="none" id="player'.md5($key).'" class="pull-right">';
        echo '<source src="'.$url.'" type="'.$audio.'">';
        echo 'Your browser does not support the audio element.';
        echo '</audio>';

        echo '<b>Status:</b> Server is <b class="text-'.$status_class.'">'.$updown.' </b>at '.$bitrate.' kbps ';
        echo 'with '.$listeners.' ('.$unique.' unique) / '.$max.' listeners (Peak: '.$peak.')<br />';
        echo '<b>Current song:</b> '.$song;
        echo '</div>';
        if ($display_footer) echo '<div class="panel-footer"></div>';
        echo '</div>';

        $total_listeners += intval($listeners);
        $total_uniques += intval($unique); 
        $total_peaks += intval($peak);
    }
    ?>
    <footer class="footer text-center">
        <p class="text-muted">
            <?php echo $title.' v'.$version ?> by djphil <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode.fr" target="_blank">
            <span class="label label-default"><?php echo $lisense; ?></span></a>
            <br>Page load time: <?php echo round((microtime(true) - $microtime), 5); ?> seconds<br>
            <a href="http://validator.w3.org/check?uri=referer" target="_blank">Valid W3C</a><br>
        </p>
    </footer>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/players.js"></script>

<script>
$(document).ready(function() {
    $("#statistics").hide();
    $(".infos").click(function() {$("#statistics").toggle();});
    $('#date').html(Date());
    setInterval(function() {$('#date').html(Date());}, 1000);
    var total_servers = <?php echo $total; ?>;
    var servers_failed = <?php echo $failed; ?>;
    var servers_online = <?php echo $total - $failed; ?>;
    var total_listeners = <?php echo $total_listeners; ?>;
    var total_uniques = <?php echo $total_uniques; ?>;
    var total_peaks = <?php echo $total_peaks; ?>;
    $('#total_servers').html(total_servers);
    $('#servers_failed').html(servers_failed);
    $('#total_listeners').html(total_listeners);
    $('#servers_online').html(servers_online);
    $('#total_uniques').html(total_uniques);
    $('#total_peaks').html(total_peaks);
});
</script>
</body>
</html>
