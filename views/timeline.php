<h2><?php echo $timeline->name ?></h2>
<ul id="events_timeline">
<?php

	if(sizeof($contents) > 0)
	{
		foreach($contents as $content)
		{
			echo '<li class="timeline_content" rel="'.$content->id.'"><form action="" method="post" class="form_etl nice_timeline">';
			wp_nonce_field( 'update_etl_'.$content->id );
			echo '<input type="hidden" name="id" value="'.$content->id.'" />';
			echo '<input type="hidden" name="action" value="etl_update" />';
			echo '<label>Title:</label> <input type="text" name="title" value="'.$content->title.'" /><br />';
			echo '<label>Text:</label> '.wp_editor( $content->text, 'text_'.$content->id, array('textarea_name' => 'text') ).'<br />';
			echo '<div class="icon_line"><label>Icon:</label> <input type="text" name="icon" value="'.$content->icon.'" autocomplete="off" /><div class="icons_list_search"></div></div><br />';
			echo '<label>Link:</label> <input type="text" name="link" value="'.$content->link.'" /><input type="checkbox" name="blank" id="blank" value="1" '.($content->blank ? 'checked="checked"' : '').' /> <label for="blank">Blank ?</label><br />';;
			echo '<input type="image" src="'.plugins_url( 'img/save.png', dirname(__FILE__) ).'" title="Update timeline parameters" />';
			echo '<a href="#" class="remove_etl" rel="'.$content->id.'" title="Remove event"><img src="'.plugins_url( 'img/remove.png', dirname(__FILE__) ).'" /></a><br />';
			echo '</form></li>';


		}
	}

?>
</ul>
<form action="" method="post" class="form_etl" id="form_new_ent">
	<h3>Add a new event</h3>
	<?php wp_nonce_field( 'new_etl' ) ?>
	<input type="hidden" name="id_timeline" value="<?php echo $timeline->id ?>" />
	<input type="hidden" name="action" value="etl_new" />
	<label>Title:</label> <input type="text" name="title" /><br />
	<label>Text:</label> <?php wp_editor( '', 'text') ?><br />
	<div class="icon_line"><label>Icon:</label> <input type="text" name="icon" autocomplete="off" /><img src="<?= plugins_url( 'img/loading.gif', dirname(__FILE__)) ?>" class="loading" /><a href="https://fortawesome.github.io/Font-Awesome/icons/" target="_blank">List of all icons</a>
	<div class="icons_list_search"></div></div><br />
	<label>Link:</label> <input type="text" name="link" /> <input type="checkbox" name="blank" id="blank" value="1" /> <label for="blank">Blank ?</label><br />
	<input type="submit" value="Add event" />
</form>
<script>

	jQuery(document).ready(function($){

		//changement d'ordre des images
		jQuery('#events_timeline').sortable({
			update: function( event, ui ) {
				//effectuer le changement de position en BDD par Ajax
				jQuery.post(ajaxurl, {action: 'ntl_order_content', id: jQuery(ui.item).attr('rel'), order: (ui.item.index()+1), _ajax_nonce: '<?= wp_create_nonce( "ntl_order_content" ); ?>' });
			}
		});

		//ajout de la carte
	    jQuery('.form_etl').submit(function(){
	    	if(jQuery(this).find('input[name="title"]').val() == '')
	    		alert('Please fill the title!');
	    	else
	    	{
	    		//save TinyMCE
	    		tinyMCE.triggerSave();
	    		//on ajoute l'image en ajax
	    		jQuery.post(ajaxurl, jQuery(this).serialize(), function(data){
	    			window.location.reload();
		    	});
			}
	    	return false;
	    });

	    //autocomplète icon font-awesome
	    jQuery('.form_etl input[name="icon"]').keyup(function(){

	    	var _this = this;

	    	var icons_list = jQuery(_this).parent().find('.icons_list_search');

			//on fait un autocomplète si la valeur n'est pas une URL
			if(jQuery(this).val().indexOf('http') == -1 && jQuery(this).val().length > 0)
			{
				jQuery('.form_etl .loading').show();				

				//autocomplète ajax pour la choix de l'icone
				jQuery.post(ajaxurl, {action: 'ntl_fa_icons_list', q: jQuery(this).val(), _ajax_nonce: '<?= wp_create_nonce( "ntl_fa_icons_list" ); ?>' }, function(icons){

					jQuery(icons_list).html(icons);

					jQuery(icons_list).find('li').click(function(){

						var icon = jQuery(this).attr('rel');

						/*jQuery('.form_etl #new_icon').attr('class', 'fa fa-'+icon);*/

						jQuery(_this).val(icon);

						jQuery(icons_list).html('');

					});

					jQuery('.form_etl .loading').hide();
				});
			}
			else
				jQuery(icons_list).html('');

		});

		jQuery('.remove_etl').click(function(){

			var et = jQuery(this).parent().parent();
			jQuery.post(ajaxurl, {action: 'remove_etl', id: jQuery(this).attr('rel'), _ajax_nonce: '<?= wp_create_nonce( "ntl_remove_content" ); ?>' }, function(){
				jQuery(et).remove();
			});

		});

	});

</script>