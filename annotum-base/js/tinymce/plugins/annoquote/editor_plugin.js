(function() {
	tinymce.create('tinymce.plugins.annoQuote', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var disabled = true;

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('Anno_Quote', function() {
				ed.windowManager.open({
					id : 'anno-popup-quote',
					width : 480,
					height : "auto",
					wpDialog : true,
					title : "Insert Quote"
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('annoquote', {
				//removing for temp fix-- title : ed.getLang('advanced.link_desc'),
				title : 'Insert Quote',
				cmd : 'Anno_Quote'
			});
			
		/*	ed.onNodeChange.add(function(ed, cm, n, co) {
				disabled = co && n.nodeName != 'A';
			});*/
		},
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Annotum Quote Dialog',
				author : 'Crowd Favorite',
				authorurl : 'http://crowdfavorite.org',
				infourl : '',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('annoQuote', tinymce.plugins.annoQuote);
})();

jQuery(document).ready( function($) {
/*	$('.img-list-actions .show-img').live('click', function() {
		var img_id = $(this).attr('id').replace('toggle-', '');
		$(this).removeClass('show-img');
		$(this).addClass('hide-img');

		$('#img-edit-' + img_id).slideDown();
		//TODO translate
		$(this).html('Hide');
		return false;
	});
	
	$('.img-list-actions .hide-img').live('click', function() {
		var img_id = $(this).attr('id').replace('toggle-', '');
		$(this).removeClass('hide-img');
		$(this).addClass('show-img');
		
		$('#img-edit-' + img_id).slideUp();
		//TODO translate
		$(this).html('Show');
		return false;
	});
		
	
	$('#anno-popup-quote form.anno-img-edit').submit(function() {
		$.post(ajaxurl, $(this).serialize(), function(data) {
			//TODO Quote saved!!
		});
		return false;
	}) */
	
	
});
