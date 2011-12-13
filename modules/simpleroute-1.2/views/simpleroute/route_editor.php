<div id="route_editor_header" class="update route_editor module_header">

    <h2><?php echo $title; ?></h2>

</div>

<div id="route_editor_form" class="txt-left form add route_editor">

    <?php echo form::open(); ?>

      <?php echo form::open_section('Route Outbound Calls'); ?>
	<div class="field">
        <?php
            echo form::label('route[destination]', 'Destination:');
            echo form::dropdown('route[destination]',$destinations,$route['destination']);
        ?>
	</div>

	<div class="field">
        <?php
            echo form::label('route[trunk]', 'Trunk:');
            echo form::dropdown('route[trunk]',$trunks,$route['trunk']);
        ?>
	</div>

	<div class="field">
        <?php
            echo form::label('route[dialstring]', 'Dial String:');
            echo form::input('route[dialstring]',$route['dialstring']);
        ?>
	</div>

	<div class="field">
        <?php
            echo form::label('route[clid_name]', 'Default Caller-ID Name:');
            echo form::input('route[clid_name]',$route['clid_name']);
        ?>
	</div>

	<div class="field">
        <?php
            echo form::label('route[clid_number]', 'Default Caller-ID Number:');
            echo form::input('route[clid_number]', $route['clid_number']);
        ?>
	</div>
	<?php echo form::hidden("magicrownumber",$magicrownumber); ?>

	<?php echo form::close_section(); ?>
	<?php echo form::close(TRUE); ?>
</div>
