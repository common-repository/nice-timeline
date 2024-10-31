<?php

	//horizontal
	if($timeline->direction == 1)
		$orientation_class = '_h';
	//vertical
	else
		$orientation_class = '_v';

?>

<div class="nice_timeline nice_timeline<?php echo $orientation_class ?>" id="nice_timeline_<?php echo $timeline->id ?>">
	<?php

		foreach($contents as $i => $content)
		{
			echo '<div class="timeline_content'.($i == 0 ? ' active' : '').'" style="background-color: '.$timeline->info_bg_color.';">';
			echo '<h3 style="font-size: '.$timeline->title_size.'px; color: '.$timeline->info_color.'"><i class="fa fa-'.$content->icon.'" style="font-size: '.$timeline->icon_size.'px"></i> '.$content->title.'</h3>';
			echo '<div class="content" style="color: '.$timeline->info_color.'">';
			echo wpautop($content->text);
			if($content->link)
				echo '<a href="'.$content->link.'" '.(!empty($content->blank) ? 'target="_blank"' : '').'>'.$timeline->more_text.'</a>';
			echo '</div>';			
			echo '</div>';
		}

	?>
	<span class="timeline" style="background-color: <?php echo $timeline->lines_color ?>"></span>
</div>
<div id="nice_timeline_<?php echo $timeline->id ?>_content" class="nice_timeline_content nice_timeline_content<?php echo $orientation_class ?>">
	<?php echo wpautop($contents[0]->text); ?>
</div>

<style>
#nice_timeline_<?php echo $timeline->id ?> .timeline_content:nth-child(even)::before {
	background-color: <?php echo $timeline->lines_color ?>;
}

#nice_timeline_<?php echo $timeline->id ?> .timeline_content:nth-child(odd)::before {
	background-color: <?php echo $timeline->lines_color ?>;
}
</style>