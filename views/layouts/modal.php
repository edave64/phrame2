<div class="modal_wrapper" id="<?php echo $this->action ?>">
<div class="modal">
<a style="float: right" href="javascript:modal('<?php echo $this->action ?>')">[X]</a>
<h2><?php echo $this->title ?></h2>
<?php
global $user; global $config;
echo $yield;
?>
</div>
</div>