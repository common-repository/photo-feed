<?php
if (! defined('ABSPATH')) {
    die();
}
global $PhotoFeedGalleries;
$ig_profiles = ig_getProfiles();
?>

<h3><?php _e('Galleries', 'photo-feed'); ?> <a href="?page=photo-feed&tab=edit" class="button button-primary button-small"><?php _e('Add New', 'photo-feed'); ?></a>
</h3>
<div id="pfa-galleries-list-wrapper">
	<table class="fixed striped widefat">
		<thead>
			<tr>
				<th><?php _e('gallery', 'photo-feed'); ?></th>
				<th>@<?php _e('username', 'photo-feed'); ?></th>
				<th><?php _e('shortcode', 'photo-feed'); ?></th>
				<th><?php _e('action', 'photo-feed'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($PhotoFeedGalleries as $gid => $gallery): ?>
			<tr>
				<td><a href="?page=photo-feed&tab=edit&pf-gid=<?php echo $gid; ?>"><?php echo $gallery['pff-gname']; ?></a></td>
				<td>@<?php echo isset($ig_profiles[$gallery['pff-aid']]) ? $ig_profiles[$gallery['pff-aid']]['username'] : $gallery['pff-aid']; ?></td>
				<td><input type="text" onclick="select()" value='[photo-feed id="<?php echo $gid; ?>"]' readonly />
					<button class="button dashicons-align-middle" title="copy the shortcode" onclick="pfs_copyToClipboard(this)">
						<span class="dashicons dashicons-admin-page"></span>
					</button></td>
				<td><a href="?page=photo-feed&tab=edit&pf-gid=<?php echo $gid; ?>" class="button button-small" title="<?php _e('Edit','photo-feed'); ?>"><span
						class="dashicons dashicons-edit"></span></a> <a href="<?php echo wp_nonce_url( '?page=photo-feed&pf-delete-gallery='.$gid, 'pfa-request' );?>"
					class="button button-small" title="<?php _e('Delete','photo-feed'); ?>"
					onclick="return confirm('<?php _e('Are you sure want to delete this Gallery?','photo-feed'); ?>');"><span class="dashicons dashicons-trash"></span></a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<?php if(!empty($PhotoFeedGalleries)): ?>
		<tfoot>
			<tr>
				<td colspan="4" style="text-align: center;">
					<?php _e('Paste the shortcode within pages, posts, widgets anywhere, where you want to display the gallery.', 'photo-feed'); ?>
				</td>
			</tr>
		</tfoot>
		<?php endif; ?>
	</table>
</div>
<script>
// copy shortcode to clipboard
function pfs_copyToClipboard(ele){
	var inputBox = ele.parentElement.querySelector('input[type="text"]');
	inputBox.select();
    document.execCommand("copy");
    alert('<?php _e('Copied', 'photo-feed'); ?>: ' + inputBox.value);
    inputBox.selectionEnd = inputBox.selectionStart;
}
</script>