<?php
require_once('inc/config.php');
$servers = count($ip);
$time = microtime(true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $osguide; ?>">
    <meta name="author" content="djphil">
    <link rel="icon" href="./img/favicon.ico">
    <link rel="author" href="./inc/humans.txt" />

    <?php
    if ($refresh != "0") {echo "<meta http-equiv=\"refresh\" content=\"$refresh\">\n";}
    echo '<title>'.$title.'</title>';
    ?>

    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="./css/gh-fork-ribbon.min.css" rel="stylesheet">

    <?php if ($mini == true) echo '<style>audio {width: 46px;}</style>'; ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="./js/html5shiv.min.js"></script>
        <script src="./js/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="github-fork-ribbon-wrapper left">
    <div class="github-fork-ribbon">
        <a href="https://github.com/djphil/shoutstats" target="_blank">Fork me on GitHub</a>
    </div>
</div>

<div class="container">
    <h1>
        <a class="btn btn-primary pull-right" href="./" title="Refresh Stats">
        <i class="glyphicon glyphicon-refresh"></i> Refresh</a>
        <?php echo $title; ?>
    </h1>

    <?php
    $i = 1;
    while($i <= $servers)
    {
        $fp = @fsockopen($ip[$i], $port[$i], $errno, $errstr, $timeout);

        if (!$fp) 
        {
            $error[$i] = 1;
            $listeners[$i] = 0;
            $datas[$i] = '<strong>Error:</strong> Connection refused, server down ...';
            $stat[$i] = '<span class="label label-danger pull-right">OFFLINE</span>';
        }

        else
        {
            fputs($fp, "GET /7.html HTTP/1.0\r\nUser-Agent: Mozilla\r\n\r\n");

            while (!feof($fp)) 
            {
                $info = fgets($fp);
            }

            $stats = explode(',', strip_tags($info));

            if (empty($stats[1]))
            {
                $error[$i] = 1;
                $listeners[$i] = 0;
                $datas[$i] = '<strong>Error:</strong> There is no source connected ...';
                $stat[$i] = '<span class="label label-danger pull-right">OFFLINE</span>';
            }

            else
            {
                if ($stats[1] == 1)
                {
                    $listeners[$i]  = $stats[0]; 
                    $status[$i]     = $stats[1];
                    $peak[$i]       = $stats[2];
                    $max[$i]        = $stats[3];
                    $bitrate[$i]    = $stats[5];
                    $song[$i]       = $stats[6];

                    if ($song[$i] == "") $song[$i] = "Unknow ...";
                    if ($stats[0] == $max[$i]) $datas[$i] .= '<div class="alert alert-danger">';

                    $datas[$i] .= "<strong>Status:</strong> Server is up at $bitrate[$i] kbps ";
                    $datas[$i] .= "with $listeners[$i] of $max[$i] listeners (Peak: ".$peak[$i].")<br />";
                    $datas[$i] .= "<strong>Current song:</strong> ".$song[$i];

                    if ($status[$i] == 1) $stat[$i] = '<span class="label label-success pull-right">ONLINE</span>';
                    else $stat[$i] = '<span class="label label-danger pull-right">OFFLINE</span>';
                    if ($stats[0] == $max[$i]) $datas[$i] .= "</div>";
                }

                else
                {
                    $error[$i] = 1;
                    $listeners[$i] = 0;
                    $datas[$i] = 'Error: Cannot get info from server ...';
                    $stat[$i] = '<span class="label label-danger pull-right">OFFLINE</span>';
                }
            }
        }
        fclose($fp);
        $i++;
    }

    $total_listeners = array_sum($listeners);
    $time_difference = "0"; // BST: 1 GMT: 0
    $time_difference = ($time_difference * 60 * 60);
    $current_time = date("h:ia", time() + $time_difference);
    $current_date = date("jS F, Y", time() + 0);

    echo '<p class="pull-right">There are <span class="badge badge-default">'.$total_listeners.'</span> listeners locked</p>';
    echo "<p>Live <span class='label label-warning'>Shoutcast V1 - V2</span> statistics: $current_date, $current_time</p>\n";

    $i = 1;
    while($i <= $servers)
    {
        if ($max[$i] > 0) 
        {
            if ($max[$i] > 250 && $demo == true) $max[$i] = 250;
            $percentage = round(($listeners[$i] / $max[$i] * 100));
            if ($percentage >= -1 && $percentage <= 19) $class = "progress-bar-default";
            else if ($percentage >= 20 && $percentage <= 39) $class = "progress-bar-info";
            else if ($percentage >= 40 && $percentage <= 59) $class = "progress-bar-success";
            else if ($percentage >= 60 && $percentage <= 79) $class = "progress-bar-warning";
            else if ($percentage >= 80 && $percentage <= 100) $class = "progress-bar-danger";
            else $class = "progress-bar-danger";
        }

        if ($stats[0] == $max[$i] || $error[$i] == "1") $panel_class = 'panel-danger alert-danger';
        else $panel_class = 'panel-default';

        if ($error[$i] <> 1)
        {
            $url = "http://".$ip[$i].':'.$port[$i];

            echo '<div class="panel '.$panel_class.'">';
            echo '<div class="panel-heading">';
            echo '<strong>Server '.$i.':</strong> '.$ip[$i].':'.$port[$i].$stat[$i];
            echo '</div>';
            echo '<div class="panel-body">';
            echo '<div class="progress">';
            echo '<div class="progress-bar progress-bar-striped '.$class.'" role="progressbar" aria-valuenow="'.$percentage.'" aria-valuemin="0" aria-valuemax="100" style="min-width: 8em; width: '.$percentage.'%;">';
            echo '<p>'.$percentage.'% of capacity</p>';
            echo '</div>';
            echo '</div>';
            echo $datas[$i];
            echo '</div>';
            echo '<div class="panel-footer">';
            echo '<a class="btn btn-default btn-sm pull-right" href="'.$url.'" target="_blank">Read more ...</a>';
            echo '<audio controls preload="none" id="player'.$i.'">';
            echo '<source src="'.$url.'/;&type=mp3" type="audio/mpeg">';
            echo 'Your browser does not support the audio element.';
            echo '</audio>';
            echo '</div>';
            echo '</div>';
        }

        else
        {
            echo '<div class="panel '.$panel_class.'">';
            echo '<div class="panel-heading">';
            echo '<strong>Server '.$i.':</strong> '.$ip[$i].':'.$port[$i].$stat[$i];
            echo '</div>';
            echo '<div class="panel-body">'.$datas[$i].'</div>';
            echo '</div>';
        }
        ++$i;
    }
    echo "<strong>Page load time:</strong> ".round((microtime(true) - $time), 5)." seconds";
    ?>

    <footer class="footer">
        <p class="text-muted">
            <?php echo $title ?> by djphil (CC-BY-NC-SA 4.0)        
            <a href="http://validator.w3.org/check?uri=referer" target="_blank">Valid W3C</a>
        </p>
    </footer>
</div>

<script src="./js/jquery.min.js"></script>
<script src="./js/bootstrap.min.js"></script>
<script src="./js/players.js"></script>
</body>
</html>
