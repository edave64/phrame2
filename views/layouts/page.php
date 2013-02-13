<?php global $user; global $config; ?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <title>Kirchgemeinde - <?php echo $this->active_page['title']; ?></title>
        <?php
        echo $this->styles();
        ?>
        <link rel="stylesheet" href="index.php?controller=functions&a=phpcss&f=main.php" />
        <!--[if lt IE 7]><script src="public/script/kill-ie6.js"></script><![endif]-->
        <!--[if lt IE 8]>
        <style type="text/css">
        #catmenu ul li { display: inline; }
        #submenu ul li { display: inline; }
        </style>
        <![endif]-->
    </head>
    <body>
<?php if ($user->may('see_ap')) $this->renderPartial('admin-panel'); ?>
        <div id="container">
			<div id="header">
        <img id="evlks" src="public/images/evlks.png" alt="Evangelisch-lutherische Kirchgemeinde Sachsen" />
				<h1>Kirchgemeinde Machern, Leulitz mit Zeitiz &amp; Altenbach</h1>
				<div id="catmenu">
					<?php echo $this->topMenuCache; ?>
				</div>
				<div id="submenu">
					<?php echo $this->leftMenuCache; ?>
				</div>
            </div>
            <div id="content">
            <h2><?php echo $this->active_page['title']; ?></h2>
            <?php
            if($config['state']=='dev')
              $_SESSION['ok'] = array_merge(($_SESSION['ok']?$_SESSION['ok']:array()), query::$log); ?>
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
                <div id="sidemap">
            	    Inhaltverzeichniss:
                	<?php echo $this->build_sidemap() ?>
                </div><br />
                (c) 2012 by EDave | <a data-modal="login" href="?a=login&controller=functions">Login</a>
            </p>
        </div>
        <?php 
          echo $this->scripts();
         ?>
    </body>
</html>
