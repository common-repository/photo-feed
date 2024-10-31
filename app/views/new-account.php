<p style="font-size: 18px;">
	Thanks for choosing the <a href="https://wordpress.org/plugins/photo-feed/" target="_blank" title="Plugin Page">Photo Feed</a> plugin. You must have
	to connect your <a href="https://www.instagram.com/" target="_blank" title="Instagram Social Media">Instagram</a> account to use plugin and to
	display content.
</p>
<div class="pfa-account-card">
	<div class="add-account-title">
		<button onclick="photofeedAuthorize()">
			<img src="<?php echo PHOTOFEED_URL; ?>/assets/media/instagram-icon.png" width="32" height="32" /> <?php _e('Connect Your Instagram Account', 'photo-feed'); ?></button>
	</div>
</div>

<script>
	function photofeedAuthorize(){
		window.location.href = '<?php echo ig_getOAuthURL(); ?>';
	}
</script>