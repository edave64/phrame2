<?php global $user; global $config; ?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <title>Phrame - <?php echo $this->title; ?></title>
        <?php
        echo $this->styles();
        echo $this->scripts();
        ?>
        
        <!--[if lt IE 7]><script src="public/script/kill-ie6.js"></script><![endif]-->
<?php if (sizeof(get_flash('fail')) || sizeof(get_flash('ok'))) { ?>
        <script type="text/javascript" src="public/script/info_toggle.js"></script>
<?php } ?>
    </head>
    <body>
<?php if ($user->may('see_ap')) $this->renderPartial('admin-panel'); ?>
        <div id="container">
            <h1>Kirchgemeinde</h1>
            <div id="topmenu">
                <?php echo $this->topMenuCache; ?>
            </div>
            <div id="leftmenu">
                <?php echo $this->leftMenuCache; ?>
            </div>
            <h2><?php echo $this->title; ?></h2>
            <?php
            if($config['state']=='dev')
              $_SESSION['ok'] = array_merge(($_SESSION['ok']?$_SESSION['ok']:array()), query::$log); ?>
            <div id="content">
<?php if (sizeof(get_flash('fail'))) { ?>
                <p id="errors">
<?php foreach (flush_flash('fail') as $fail) { ?>
                    <span class="error"><?php echo $fail ?></span><br />
<?php } ?>
                </p>
<?php } if (sizeof(get_flash('ok'))) { ?>
                <p id="oks">
<?php foreach (flush_flash('ok') as $ok) { ?>
                    <span class="ok"><?php echo $ok ?></span><br />
<?php } ?>
                </p>
<?php } echo $yield; ?>
            </div>
            <p id="footer">
                (c) 2012 by EDave. Created in <?php echo $this->response_time(); ?> Millisecounds;
                <?php echo count(query::$log) ?> Requests
            </p>
        </div>
    </body>
</html>