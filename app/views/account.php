<?php
if (! defined('ABSPATH')) {
    die();
}
global $PhotoFeedSettings;

$profileInfo = ig_getProfile();
?>
<?php if (! $profileInfo): ?>
<p><?php _e('Unable to get Instagram account information, may be network issue or session expired.', 'photo-feed'); ?><br /><?php _e('Please re-connect the account or try again later.', 'photo-feed'); ?></p>
<?php if (! empty($PhotoFeedSettings['access_token'])) { ?>
<form method="post">
	<button type="submit" name="pfa-profile-action" value="remove" title="Re-Connect Account" class="button button-primary pfa-confirm"><?php _e('Re-Connect Account', 'photo-feed'); ?></button>
        <?php wp_nonce_field('pfa-request'); ?>
	</form>
<?php }	?>
<?php else: ?>
<header class="pfa-account-header">
	<form method="post">
		<h3><?php _e('Account', 'photo-feed'); ?></h3>
		<div class="pfa-account-action">
			<h3>
				<a href="https://www.instagram.com/<?php echo $profileInfo['username']; ?>" target="_blank">@<?php echo $profileInfo['username']; ?></a>
			</h3>
			<button type="submit" name="pfa-profile-action" value="remove" title="Disconnect Account" class="button button-primary pfa-confirm">
				<span class="dashicons dashicons-dismiss"></span> <?php _e('Disconnect Account', 'photo-feed'); ?></button>
			<button type="submit" name="pfa-profile-action" value="cache" title="Check for newly added media" class="button button-primary">
				<span class="dashicons dashicons-update"></span> <?php _e('Refresh Cache', 'photo-feed'); ?></button>
		</div>
		<?php wp_nonce_field('pfa-request'); ?>
	</form>
	<div class="pfa-how-to-use">
		<table>
			<tr>
				<td><h3><?php _e('How to use?', 'photo-feed'); ?></h3></td>
			</tr>
			<tr>
				<td><input class="pfa-code" type="text" value="[photo-feed]" readonly />
					<p><?php _e('Paste this shortcode within posts, pages, widgets etc.', 'photo-feed'); ?></p></td>
			</tr>
			<tr>
				<td class="pfa-or"><span><?php _e('OR', 'photo-feed'); ?></span></td>
			</tr>
			<tr>
				<td><input class="pfa-code" type="text" value="&lt;?php PhotoFeed(); ?&gt;" readonly />
					<p><?php _e('Paste this code within template files.', 'photo-feed'); ?></p></td>
			</tr>
		</table>
	</div>
</header>
<?php endif; ?>

<script>
    jQuery(document).on('click','.pfa-confirm',function(ev){
        var c = confirm("<?php _e('Are you sure?', 'photo-feed'); ?>");
        if(!c){
            ev.preventDefault();
		}
	});	
</script>