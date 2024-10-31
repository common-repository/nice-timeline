jQuery(document).ready(function(){

	//place les événements suivant la place disponible
	jQuery('.nice_timeline').each(function(){

		var orientation;
		if(jQuery(this).hasClass('nice_timeline_h'))
			orientation = 'h';
		else
			orientation = 'v';

		var events = jQuery(this).find('.timeline_content');

		var offset = 100 / jQuery(events).length;
		current_offest = 0;

		jQuery(events).each(function(){

			if(orientation == 'h')
				jQuery(this).css('left', current_offest+'%');
			else
				jQuery(this).css('top', current_offest+'%');

			current_offest += offset;

		});

	});

	jQuery('.nice_timeline h3').click(function(){

		jQuery(this).parent().parent().find('.active').removeClass('active');
		jQuery(this).parent().addClass('active');

		var content = jQuery(this).parent().find('.content').html();
		var id = jQuery(this).parent().parent().attr('id');
		console.log(id);
		console.log(content);
		jQuery('#'+id+'_content').html(content);

	});

	//calcul de la taille de la div affichant le contenu en vertical
	jQuery('.nice_timeline_v').each(function(){

		var width = jQuery(this).outerWidth();
		var total_width = jQuery(this).parent().width();

		var width_content = ((total_width - width) / total_width)*100 - 1;

		console.log(width_content);

		var id = jQuery(this).attr('id');
		jQuery('#'+id+'_content').css('width', width_content.toFixed(2)+'%');
	});

});