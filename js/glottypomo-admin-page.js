var glotty={};

(function( $ , Backbone , glotty ){
	glotty.TranslationView = Backbone.View.extend({
		initialize: function() {
			var initvars = {
				page:1,
				ipp:50,
				count_entries:0,
				count_pages:0
			};
			_.extend( this , initvars );
			_.extend( this , arguments[0] );
			var private_vars = {$updatedEntries:false};
			_.extend( this , private_vars );
			
			for ( var s in this.po.entries ) 
				this.count_entries++;
			
			// ops: save changes, sync with pot, sync with codebase (plugin/theme only)
			this.render();
		},
		render:function() {
			var self = this, html, template_data,
				counter=0,s,
				entry,entry_el;
			this.count_pages = Math.ceil( this.count_entries / this.ipp );
			template_data = {
				can_save:true,
				can_sync:true
			};
			_.extend( template_data , this );
			html = _.template( $("#tpl-glottypomo-translations-table").html(), template_data );
			this.$el.html(html);
			
			entry_el = $(this.$el.find('tbody').get(0));
			for ( s in this.po.entries ) {
				if ( counter >= this.ipp*(this.page-1) && counter < this.ipp*this.page ) {
					entry = new glotty.TranslationEntryView( { container:entry_el , entry:this.po.entries[s] , key:s , plural_definitions:this.plural_definitions } );
					entry.$el.on('change:translation',function(e){ 
						
							var val = $(e.target).val(),
	 							key = $(e.target).data('key'),
								idx = $(e.target).data('idx');
							
							self.po.entries[key].translations[idx] = val;
							if ( ! self.$updatedEntries )
								self.$updatedEntries = $(e.target);
							else
								self.$updatedEntries.push(e.target);
						} );
				}
				if ( counter >= this.ipp*this.page )
					break;
				counter++;
			}
		},
		gotoPage : function(e){
			this.page = $(e.target).data('page');
			this.render();
		},
		events: {
            "click a[data-page]": "gotoPage",
            'click button#glottypomo-save' : 'save',
            'click button#glottypomo-sync' : 'sync',
            'click button#glottypomo-cancel' : 'cancel',
            'click button#glottypomo-make-po' : 'makePo',
        },
        
        save : function(e) {
        	// trigger save
        	var evt = $.Event( 'save:translation' );
        	evt.updatedEntries = !!this.$updatedEntries ? this.$updatedEntries.serializeObject() : {};
        	this.$el.trigger( evt );
        	// disenable save button
        	this.afterSave();
        },
        sync : function(e) {
        },
        cancel : function(e) {
        },
        makePo : function(e) {
        	var evt = $.Event( 'make:translation' );
        	this.$el.trigger( evt );
        },
        afterSave : function() {
        	// remove entries included in response from updated entries.
        	// re-enable save button
        	this.$updatedEntries = false;
        }
	});

	glotty.TranslationEntryView = Backbone.View.extend({
		alternate:true,
		initialize: function(){
			var initvars = {
				html:'',
				ipp:50,
				count_entries:0
			},key_b64;
			_.extend( this , initvars );
			_.extend( this , arguments[0] );
			//key_b64 = Base64.encode(this.key);
			var entry_data = { 
				entry:this.entry,
				key : this.entry.b64_key,
				id : this.entry.b64_key.replace(/[^a-zA-Z0-9]/g,'-'),
				alternate:glotty.TranslationEntryView.alternate,
				plural_definitions:this.plural_definitions
			}
			/*
			entry.flags
			entry.references
			entry.translator_comments
			entry.extracted_comments
			*/
			var html = _.template( $("#tpl-glottypomo-translation-entry").html(), entry_data );
			this.$el = $(html);
			this.render();
		},
		render:function() {
			this.container.append(this.$el);
			glotty.TranslationEntryView.alternate = !glotty.TranslationEntryView.alternate;
		},
		toggleTab : function(e) {
			var id = $(e.target).attr('href'),
				$container=$(e.target).closest('[role="tabpanel"]');
			
			$container.find('li.active').removeClass('active');
			$(e.target).closest('li').addClass('active');
			
			$container.find('.tab-pane.active').removeClass('active');
			$(id).addClass('active');
			e.preventDefault();
			return false;
		},
		translationChanged : function(e) {
			// change entry in po!
			$(e.target).trigger('change:translation',e);
		},
		events : {
			'click a[role="tab"]' : 'toggleTab',
			'change textarea' : 'translationChanged'
		}
	});
	
})(jQuery,Backbone,glotty);


(function($,glotty){

if ( glottypomo.ajax_data.locale && glottypomo.ajax_data.po ) {
	
	// load po data
	function build_editor(response){
		var page, init_args = { 
			ipp:100 , 
			el:$('#glotty-translations-table') 
		};
		$.extend( true , init_args , response );
		if ( page = parseInt(document.location.hash.substring(1)) )
			init_args.page = page;
		new glotty.TranslationView(init_args);
	}
	
	var action='get-po-data',
		url = glottypomo.ajax_urls[action],
		data = glottypomo.ajax_data,
		$changed;
	// load po
	$.post( url , data , build_editor );
	
	$(document).on('save:translation',function( e ) {
		var action='save-entries',
			url = glottypomo.ajax_urls[action]
			data = glottypomo.ajax_data;
		$.extend( true , data , e.updatedEntries );
		$.post(url,data,function(response){
			
			console.log(response);
		});
	});
	$(document).on('make:translation',function( e ) {
		var action='init-po',
			url = glottypomo.ajax_urls[action]
			data = glottypomo.ajax_data;
       	console.log(url , data);
		$.post( url , data , build_editor );
	});
}

})(jQuery,glotty);




