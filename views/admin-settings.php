<div class="wrap">
	<?php screen_icon(); ?>
	<h2>WP-Portability Settings</h2>


	<form method="post" action="options.php">
		<?php
		// This prints out all hidden setting fields
		settings_fields( 'portability_group' );
		do_settings_sections( 'portability_group' );
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Use Dynamic Site URL</th>
				<td>
					<input type="checkbox" name="plk_wp_portability[dynamic_siteurl]" value="1" <?php echo $this->settings['dynamic_siteurl']?'checked':''?> <?php echo is_multisite()?'disabled':'' ?> />
					<?php if(is_multisite()): ?>
						<small style="color:red">This option cannot be used on a multisite installation.</small>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Use Dynamic Home URL</th>
				<td>
					<input type="checkbox" name="plk_wp_portability[dynamic_homeurl]" value="1" <?php echo $this->settings['dynamic_homeurl']?'checked':''  ?> <?php echo is_multisite()?'disabled':'' ?> />
					<?php if(is_multisite()): ?>
						<small style="color:red">This option cannot be used on a multisite installation.</small>
					<?php endif; ?>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Use Relative Apache Rewrites</th>
				<td>
					<input type="checkbox" name="plk_wp_portability[relative_htaccess]" value="1" <?php echo $this->settings['relative_htaccess']?'checked':''  ?> <?php echo is_multisite()?'disabled':'' ?> />
					<?php if(is_multisite()): ?>
						<small style="color:red">This option cannot be used on a multisite installation.</small>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Automatically inject URL Shortcode</th>
				<td>
					<input type="checkbox" name="plk_wp_portability[url_shortcode_inject]" value="1" <?php echo $this->settings['url_shortcode_inject']?'checked':''  ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Update the Database</th>
				<td>
					<p>When you first install this plugin, it is recommended that you run the insert shortcode script to convert all existing local links across your site to the [url] shortcode.</p>
					<strong>This process may take a few minutes to complete</strong>
					<br><br>
					<input type="button" value="Insert Shortcodes" id="plk-wp-insert-shortcodes" style="float:left" />
					<span class="spinner" style="float:left"></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Purge the Database</th>
				<td>
					<p>Deactivating this plugin will not change the contents of the database. If you need to permanently uninstall this plugin, it is recommended that you perform a database-wide purge of the [url] shortcode. Otherwise, your links may break once the shortcode is no longer able to automatically parse them.</p>
					<strong>This process may take a few minutes to complete</strong>
					<br><br>
					<input type="button" value="Purge Shortcodes" id="plk-wp-purge-shortcodes" style="float:left" />
					<span class="spinner" style="float:left"></span>
				</td>
			</tr>

		</table>
		<?php submit_button(); ?>
	</form>
	<p>WP-Portability is developed by <a href="http://plankdesign.com" target="_blank">Plank Design Inc.</a></p>
</div>
