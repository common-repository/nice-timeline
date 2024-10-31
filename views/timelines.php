<h2>Nice timeslines</h2>

<div id="nice_timelines">
<?php

	if(sizeof($timelines) > 0)
	{
		foreach($timelines as $timeline)
		{
			echo '<form action="" method="post" class="form_ntl nice_timeline">';
			echo '<h3>'.$timeline->name.'</h3>';
			wp_nonce_field( 'update_ntl_'.$timeline->id );
			echo '<input type="hidden" name="id" value="'.$timeline->id.'" />';
			echo '<label>Name:</label> <input type="text" name="name" value="'.$timeline->name.'" /><br />';
			echo '<label>Orientation:</label> <select name="direction"><option value="1" '.($timeline->direction == 1 ? 'selected' : '').'>Horizontal</option><option value="2" '.($timeline->direction == 2 ? 'selected' : '').'>Vetical</option></select><br />';
			echo '<label>Lines color:</label> <input type="text" name="lines_color" class="colorpicker" value="'.$timeline->lines_color.'" /><br />';
			echo '<label>Infos color:</label> <input type="text" name="info_color" class="colorpicker" value="'.$timeline->info_color.'" /><br />';
			echo '<label>Infos background color:</label> <input type="text" name="info_bg_color" class="colorpicker" value="'.$timeline->info_bg_color.'" /><br />';
			echo '<label>Titles size:</label> <input type="text" name="title_size" value="'.$timeline->title_size.'" />px<br />';
			echo '<label>Icons size:</label> <input type="text" name="icon_size" value="'.$timeline->icon_size.'" />px<br />';
			echo '<label>More info text:</label> <input type="text" name="more_text" value="'.$timeline->more_text.'"><br />';
			echo '<a href="'.admin_url( 'admin.php?page=nice_timeline&id='.$timeline->id).'" title="Set timelines events"><img src="'.plugins_url( 'img/timeline.png', dirname(__FILE__) ).'" /></a>';
			echo '<input type="image" src="'.plugins_url( 'img/save.png', dirname(__FILE__) ).'" title="Update timeline parameters" />';
			echo '<a href="#" title="Remove timeline" rel="'.$timeline->id.'" class="remove_ntl"><img src="'.plugins_url( 'img/remove.png', dirname(__FILE__) ).'" /></a><br />';
			echo '<strong>Shortcode: [nice-timeline id="'.$timeline->id.'"]</strong>';
			echo '</form>';


		}
	}
	else
		echo '<p>No timelines found</p>';

?>
</div>
<form action="" method="post" class="form_ntl">
	<h3>Add a new timeline</h3>
	<?php wp_nonce_field( 'new_ntl' ) ?>
	<label>Name:</label> <input type="text" name="name" /><br />
	<label>Orientation:</label> <select name="direction"><option value="1">Horizontal</option><option value="2">Vetical</option></select><br />
	<label>Lines color:</label> <input type="text" name="lines_color" class="colorpicker" /><br />
	<label>Infos color:</label> <input type="text" name="info_color" class="colorpicker" /><br />
	<label>Infos background color:</label> <input type="text" name="info_bg_color" class="colorpicker" /><br />
	<label>Titles size:</label> <input type="text" name="title_size" />px<br />
	<label>Icons size:</label> <input type="text" name="icon_size" />px<br />
	<label>More info text:</label> <input type="text" name="more_text" value="More info..."><br />
	<input type="submit" value="Add timeline" />
</form>
<h3><br />Need help? <a href="http://www.info-d-74.com" target="_blank">Click for support</a> <br/>
and like InfoD74 to discover my new plugins: <a href="https://www.facebook.com/infod74/" target="_blank"><img src="<?php echo plugins_url( 'img/fb.png', dirname(__FILE__)) ?>" alt="" /></a></h3>
<script>

	jQuery(document).ready(function(){

		jQuery('.colorpicker').wpColorPicker();

		jQuery('.remove_ntl').click(function(){

			var nt = jQuery(this).parent();
			jQuery.post(ajaxurl, {action: 'remove_ntl', id: jQuery(this).attr('rel'), _ajax_nonce: '<?= wp_create_nonce( "remove_ntl" ); ?>' }, function(){
				jQuery(nt).remove();
			});

		});

	});

</script>