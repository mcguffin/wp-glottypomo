<?php


//*


//*/

if ( ! class_exists( 'GlottypomoTemplates' ) ):
class GlottypomoTemplates {
	
	
	/**
	 *	Output Translation editor JS templates
	 *	
	 *	@action admin_footer
	 */
	static function js_templates(){
?>
<!-- translations table entry -->
<script type="text/template" id="tpl-glottypomo-translation-entry">
	<!-- context -->
	<% 
		cols = glottybot.label ? 3 : 2;
	%>
	<% if ( glottybot.heading ) { %>
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<th class="translation-heading" colspan="<%= cols %>"><h3><%= glottybot.heading %></h3></th>
		</tr>
	<% } %>
	<% if ( entry.context ) { %>
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<th class="translation-context" colspan="<%= cols %>"><strong><?php _e('Context:','wp-glottypomo') ?> <%= entry.context %></strong></th>
		</tr>
	<% } %>

	<% if ( entry.is_plural ) { %>
		<!-- plural -->
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<% if ( glottybot.label ) { %>
				<th class="translation-label"><strong><%= glottybot.label %></strong></th>
			<% } %>
			<td class="source"><?php _e( 'Singular:' , 'wp-glottypomo' ) ?> <%= entry.singular %></td>
			<td rowspan="2" class="translation">
				<div role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">	
					<% for (var i=0;i<entry.translations.length;i++ ) { %>
						<li role="presentation" <% if (i==0) { %>class="active"<% } %>>
							<a class="tab-nav" href="#<%= id %>-<%= i %>" aria-controls="<%= id %>-<%= i %>" role="tab" data-toggle="tab"><%= i %></a>
						</li>
					<% } %>
					</ul>
					<% for (var i=0;i<entry.translations.length;i++ ) { %>
						<div role="tabpanel" class="tab-pane<% if (i==0) { %> active<% } %> translation-tab" id="<%= id %>-<%= i %>">
							<% console.log(plural_definitions); %>
							<p><code><%= plural_definitions.plural_definitions[i].condition %></code></p>
							<textarea data-key="<%= entry.b64_key %>" data-idx="<%= i %>" name="entries[<%= entry.b64_key %>][translations][<%= i %>]"><%= entry.translations[i] %></textarea>
							<p class="description"><?php _e('Condition:','wp-glottypomo') ?> <%= plural_definitions.plural_definitions[i].condition_human %></p>
						</div>
					<% } %>
				</div>
			</td>
		</tr>
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<td class="source"><?php _e( 'Plural:' , 'wp-glottypomo' ) ?><br /> <%= entry.plural %></td>
		</tr>
	<% } else { %>
		<!-- singular -->
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<% if ( glottybot.label ) { 
				source_colspan = '';
			%>
				<th class="translation-label"><strong><%= glottybot.label %></strong></th>
				
			<% } else { 
				source_colspan = ' colspan="2"';
			%>
			<% } %>
			<td class="source"<%= source_colspan %>><%= entry.singular %></td>
			<td class="translation">
				<textarea data-key="<%= entry.b64_key %>" data-idx="0" name="entries[<%= entry.b64_key %>][translations][0]"><%= entry.translations[0] %></textarea>
			</td>
		</tr>
	<% } %>
	<!-- comments -->
	<% if ( entry.translator_comments || ( ! glottybot && entry.extracted_comments ) ) { %>
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<td colspan="2" class="comments"><%
				if (entry.translator_comments) {
					%><code class="extracted-comment"># <%= entry.translator_comments %></code><%
				}
				if (entry.extracted_comments) {
					%><code class="extracted-comment"># <%= entry.extracted_comments %></code><%
				}
			%></td>
		</tr>
	<% } %>
	<!-- flags -->
	<% if ( entry.flags && entry.flags.length ) { %>
		<tr<% if ( alternate ) { %> class="alternate"<% } %>>
			<td colspan="2" class="flags"><%
				for (var i=0;i<entry.flags.length;i++) {
					%><strong class="dashicons-before dashicons-flag"><%= entry.flags[i] %></strong><%
				}
			%></td>
		</tr>
	<% } %>
			entry.translator_comments
			entry.extracted_comments
</script>

<!-- translations table -->
<script type="text/template" id="tpl-glottypomo-translations-table">
	<% if ( has_po ) { %>
		<nav id="glottypomo-toolbar">
			<button id="glottypomo-cancel" class="button button-secondary"><span class="dashicons dashicons-no"></span><?php _e('Cancel') ?></button>
			<% if ( can_sync ) { %>
			<button id="glottypomo-sync" class="button button-secondary"><span class="dashicons dashicons-update"></span><?php _e('Sync') ?></button>
			<% } %>
			<% if ( can_save ) { %>
			<button id="glottypomo-save" class="button button-primary"><span class="dashicons dashicons-yes"></span><?php _e('Save') ?></button>
			<% } %>
		
		</nav>
		<ul class="nav page-nav above">
		<% for (var i=1;i<count_pages;i++ ) { %>
			<li class="page-entry<% if (i==page) { %> active<% } %>" role="presentation" >
				<a href="#<%= i %>" data-page="<%= i %>"><%= i %></a>
			</li>
		<% } %>
		</ul>
		<table class="wp-list-table widefat glottypomo-translations-table">
		<thead>
			<tr>
				<th class="translate-cell" colspan="2"><?php _e( 'Original' , 'wp-glottypomo' ) ?></th>
				<th class="translate-cell"><?php _e( 'Translation' , 'wp-glottypomo' ) ?></th>
			</tr>
		<thead>
		<tbody></tbody>
		</table>
		<ul class="nav page-nav below">
		<% for (var i=1;i<count_pages;i++ ) { %>
			<li class="page-entry<% if (i==page) { %> active<% } %>" role="presentation" >
				<a href="#<%= i %>" data-page="<%= i %>"><%= i %></a>
			</li>
		<% } %>
		</ul>
	<% } else if ( can_make_po ) { %>
		<p><?php _e( 'There is no translation file for this locale. Anyway we can create one.' , 'wp-glottypomo' ); ?></p>
		<nav id="glottypomo-toolbar">
			<button id="glottypomo-make-po" class="button-primary"><?php _e('Create Translation') ?></button>
		</nav>
	<% } else { %>
		<p>No pot or po.</p>
	<% } %>
</script>
<?php
	}
	
}
endif;