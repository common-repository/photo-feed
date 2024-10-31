<?php
if (! defined('ABSPATH')) {
    die();
}
$ig_profiles = ig_getProfiles();
?>
<section class="pfa-accounts">
	<h3><?php _e('Instagram Accounts', 'photo-feed'); ?></h3>
	<div class="pfa-accounts-list">
		<?php foreach ($ig_profiles as $ig_profile): ?>
		<div class="pfa-account">
			<a class="pfa-account-card" href="https://www.instagram.com/<?php echo $ig_profile['username']; ?>" target="_blank"> <span
				class="dashicons dashicons-instagram"></span>@<?php echo $ig_profile['username']; ?>
			</a>
			<div class="pfa-account-action">
				<form method="post">
					<button type="submit" name="pfa-profile-action" value="cache" title="<?php _e('Refresh', 'photo-feed'); ?>">
						<span class="dashicons dashicons-update"></span>
					</button>
					<button type="submit" name="pfa-profile-action" value="remove" title="<?php _e('Remove', 'photo-feed'); ?>" class="pfa-confirm">
						<span class="dashicons dashicons-dismiss"></span>
					</button>
				
				<?php wp_nonce_field('pfa-request'); ?>
				<input type="hidden" name="pfa-profile-id" value="<?php echo $ig_profile['id']; ?>" />
				</form>
			</div>
		</div>
		<?php endforeach; ?>
		
		<div class="pfa-account pfa-account-new">
			<button onclick="photofeedAuthorize()" class="pfa-account-card">
				<span class="dashicons dashicons-plus-alt"></span> Add New Account
			</button>
		</div>
	</div>
</section>
<hr />

<script>
	// confirm before removing account
    jQuery(document).on('click','.pfa-confirm',function(ev){
        var c = confirm("<?php _e('Are you sure?', 'photo-feed'); ?>");
        if(!c){
            ev.preventDefault();
		}
	});
	
	// new account link
	function photofeedAuthorize(){
    	window.location.href = '<?php echo ig_getOAuthURL(); ?>';
    }
</script>