var annoQuote;

(function($){
	var inputs = {}, ed;

	annoQuote = {	
		keySensitivity: 100,
		textarea: function() { return edCanvas; },

		init : function() {
			inputs.dialog = $('#anno-popup-quote');
			inputs.submit = $('#anno-quote-submit');
		
			// Bind event handlers
			inputs.dialog.keydown( annoQuote.keydown );
			inputs.dialog.keyup( annoQuote.keyup );
			inputs.submit.click( function(e){
				annoQuote.update();
				e.preventDefault();
			});
			
			$('#anno-quote-cancel').click(annoQuote.close);

		//	inputs.dialog.bind('wpdialogrefresh', annoLink.refresh);
		//	inputs.dialog.bind('wpdialogbeforeopen', annoLink.beforeOpen);
		//	inputs.dialog.bind('wpdialogclose', annoLink.onClose);
		},


		update : function(attachment_id) {
			var display_type, caption, label, copyright_statement, copyright_holder, license, url, xml;
			var ed = tinyMCEPopup.editor;
			ed.selection.collapse(0);
			//TODO Validation

/*

<disp-quote>
	<p>&inlines; <xref ref-type="bibr" rid="B1">xref text</xref></p>  
	<attrib>ATTRIBUTION</attrib>
	<permissions>
		<copyright-statement>&formats;</copyright-statement>
		<copyright-holder>holder</copyright-holder>
		<license license-type="creative-commons">
			<license-p>&inlines; <xref ref-type="bibr" rid="B1">xref text</xref></license-p>
		</license>
	</permissions>
</disp-quote>

*/						
			form = $('#anno-popup-quote-form');
			quote = $('input[name$="text"]', form).val();
			attribution = $('input[name$="attribution"]', form).val();
			statement = $('input[name$="statement"]', form).val();
			holder = $('input[name$="holder"]', form).val();
			license = $('input[name$="license"]', form).val();
						
			xml = '<disp-quote>' + quote +  '<attrib>' + attribution +  '</attrib><permissions><copyright-statement>' + statement +  '</copyright-statement><copyright-holder>' + holder +  '</copyright-holder><license license-type="creative-commons"><license-p>' + license +  '</license-p></license></permissions></disp-quote>'
				
			tinyMCEPopup.execCommand('mceInsertContent', false, xml);
			
			tinyMCEPopup.close();
		},


		keyup : function( event ) {
			var key = $.ui.keyCode;

			switch( event.which ) {
				case key.ESCAPE:
					event.stopImmediatePropagation();
					if ( ! $(document).triggerHandler( 'wp_CloseOnEscape', [{ event: event, what: 'annoquote', cb: annoQuote.close }] ) )
						annoQuote.close();

					return false;
					break;
				default:
					return;
			}
			event.preventDefault();
		},
	}
	$(document).ready( annoQuote.init );
})(jQuery);

