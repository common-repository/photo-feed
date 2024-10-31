<?php
// Feed settings page
if (! defined('ABSPATH')) {
    die();
}
global $PhotoFeedGalleries;

$ig_profiles = ig_getProfiles();
// Form Fields
$PFFFs = [];
if (isset($_GET['pf-gid']) && isset($PhotoFeedGalleries[$_GET['pf-gid']])) {
    $PFFFs = $PhotoFeedGalleries[$_GET['pf-gid']];
} else {
    $defaultGSettings = ig_defaultGSettings();
    foreach ($ig_profiles as $ig_profile) {
        $defaultGSettings['pff-aid'] = $ig_profile['id'];
        break;
    }
    $PFFFs = $defaultGSettings;
}

$pics_limit = 12;
$pics_spacing = 0;
$gallery_cols = 3;
$active_grid = true;
$active_carousel = false;
$carousel_ipv = 4;

if (! empty($PFFFs['pff-limit'])) {
    $pics_limit = (int) $PFFFs['pff-limit'];
}
if (! empty($PFFFs['pff-spacing'])) {
    $pics_spacing = (int) $PFFFs['pff-spacing'];
}
if (! empty($PFFFs['pff-cols'])) {
    $gallery_cols = (int) $PFFFs['pff-cols'];
}
if (! empty($PFFFs['pff-layout'])) {
    if ($PFFFs['pff-layout'] == 'grid') {
        $active_grid = true;
        $active_carousel = false;
    }
    if ($PFFFs['pff-layout'] == 'carousel') {
        $active_carousel = true;
        $active_grid = false;
    }
}
if (! empty($PFFFs['pff-car-ipv'])) {
    $carousel_ipv = (int) $PFFFs['pff-car-ipv'];
}

?>
<p>
	<a href="?page=photo-feed" class="pfa-back-link"><span class="dashicons dashicons-undo"></span> Back</a>
</p>
<div id="pfa-gallery-setting-wrapper">
	<form method="post" id="pfa-form-update" action="?page=photo-feed">
		<h3><?php _e('Display Settings', 'photo-feed'); ?></h3>
		<hr class="pfa-hr-title" />
		<table class="form-table pfa-table-edit">
			<tr>
				<th scope="row"><?php _e('Gallery Name', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('A friendly name to identify the gallery.', 'photo-feed'); ?> </p></th>
				<td><input name="pff-gname" type="text" placeholder="<?php _e('My New Gallery','photo-feed'); ?>"
					value="<?php echo empty($PFFFs['pff-gname']) ? __('My New Gallery','photo-feed') : $PFFFs['pff-gname']; ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Account', 'photo-feed'); ?>:</th>
				<td><select name="pff-aid">
						<?php
    foreach ($ig_profiles as $ig_profile) :
        $selected = (! empty($PFFFs['pff-aid']) && ($PFFFs['pff-aid'] == $ig_profile['id'])) ? 'selected' : '';
        ?>
						    <option value="<?php echo $ig_profile['id']; ?>" <?php echo $selected; ?>>@<?php echo $ig_profile['username']; ?></option>
						<?php endforeach; ?>
					</select></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Layout', 'photo-feed'); ?>:</th>
				<td>
					<ul class="pfa-layouts-list">
						<li><label><input type="radio" data-toggle-radios="#pfa-layout-grid" name="pff-layout" value="grid" <?php if($active_grid) echo 'checked';?> /><?php _e('Grid','photo-feed'); ?>
							</label></li>
						<li><label><input type="radio" data-toggle-radios="#pfa-layout-carousel" name="pff-layout" value="carousel"
								<?php if($active_carousel) echo 'checked';?> /><?php _e('Carousel','photo-feed'); ?>
							</label></li>
					</ul>
				</td>
			</tr>
			<tr id="pfa-layout-grid" class="pfa-tab-content-row <?php if ($active_grid) echo 'active'; ?>">
				<th scope="row">
					<?php _e('Grid Columns', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Number of pictures in a row.', 'photo-feed'); ?> </p>
				</th>
				<td><input name="pff-cols" type="number" min="1" max="12" value="<?php echo $gallery_cols; ?>" /></td>
			</tr>
			<tr id="pfa-layout-carousel" class="pfa-tab-content-row <?php if ($active_carousel) echo 'active'; ?>">
				<td colspan="2" class="pfa-py-0">
					<table>
						<tr>
							<th scope="row">
								<?php _e('Items per view', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
								<p class="description"><?php _e('Number of pictures displayed per view.', 'photo-feed'); ?> </p>
							</th>
							<td><input name="pff-car-ipv" type="number" min="1" max="12" value="<?php echo $carousel_ipv; ?>" /></td>
						</tr>
						<tr>
							<th scope="row">
								<?php _e('Autoplay Interval', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
								<p class="description"><?php _e('Moves to the next picture after the specified time interval.', 'photo-feed'); ?> <br /> <em><?php _e('0 or empty to disable autoplay.', 'photo-feed'); ?> </em>
								</p>
							</th>
							<td><input name="pff-car-autoplay" type="number" min="0" max="60"
								value="<?php
        if (! empty($PFFFs['pff-car-autoplay'])) {
            echo $PFFFs['pff-car-autoplay'];
        }
        if (! isset($PFFFs['pff-car-autoplay'])) {
            echo 3000;
        }
        ?>"
								placeholder="3" /> <span class="description"><?php _e('second(s)', 'photo-feed'); ?></span></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Navigation Arrows', 'photo-feed'); ?>:</th>
							<td><input name="pff-car-nav" data-toggle-checks="#pfa-car-nav-color-collapse" type="checkbox" value="1"
								<?php echo (isset($PFFFs['pff-car-nav']) && empty($PFFFs['pff-car-nav'])) ? '' : 'checked'; ?> /></td>
						</tr>
						<tr id="pfa-car-nav-color-collapse" class="pfa-tab-content-row <?php if (!empty($PFFFs['pff-car-nav'])) echo 'active'; ?>">
							<th scope="row"><?php _e('Navigation Arrows Color', 'photo-feed'); ?>:</th>
							<td><input name="pff-car-nav-color" type="text" data-default-color="#007aff" class="pff-color-picker" placeholder="#007aff"
								value="<?php echo (!empty($PFFFs['pff-car-nav-color']) ? $PFFFs['pff-car-nav-color'] : '#007aff'); ?>" /></td>
						</tr>
					</table>
				</td>
			
			
			<tr>
				<th scope="row">
						<?php _e('Pictures Limit', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Number of pictures to display.', 'photo-feed'); ?></p>
				</th>
				<td><input name="pff-limit" type="number" min="1" max="50" value="<?php echo $pics_limit; ?>" placeholder="12" /></td>
			</tr>
			<tr>
				<th scope="row">
						<?php _e('Exclude Videos', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Hide video posts from display.', 'photo-feed'); ?> </p>
				</th>
				<td><input name="pff-exclude-video" type="checkbox" value="1" <?php echo (empty($PFFFs['pff-exclude-video'])) ? '' : 'checked'; ?> /></td>
			</tr>
			<tr>
				<th scope="row">
						<?php _e('Space Between Pictures', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Add blank space between pictures.', 'photo-feed'); ?> </p>
				</th>
				<td><input name="pff-spacing" type="number" min="0" max="100" step="2" value="<?php echo $pics_spacing; ?>" placeholder="20" /></td>
			</tr>
			<tr>
				<th scope="row">
						<?php _e('Image Hover Overlay', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Display overlay color on pictures.', 'photo-feed'); ?> </p>
				</th>
				<td><input name="pff-hover" data-toggle-checks="#pfal-hcolor-collapse" type="checkbox" value="1"
					<?php echo (empty($PFFFs['pff-hover'])) ? '' : 'checked'; ?> /></td>
			</tr>
			<tr id="pfal-hcolor-collapse" class="pfa-tab-content-row <?php if (!empty($PFFFs['pff-hover'])) echo 'active'; ?>">
				<th scope="row">
						<?php _e('Overlay Color', 'photo-feed'); ?>:
					</th>
				<td><input name="pff-hover-color" type="text" data-default-color="#007aff" class="pff-color-picker" placeholder="#007aff"
					value="<?php echo (!empty($PFFFs['pff-hover-color']) ? $PFFFs['pff-hover-color'] : '#007aff'); ?>" /></td>
			</tr>
			<tr>
				<th scope="row">
						<?php _e('Display Icons', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Display indicator icons for galleries and videos.', 'photo-feed'); ?> </p>
				</th>
				<td><input name="pff-type-icon" type="checkbox" value="1" <?php echo (empty($PFFFs['pff-type-icon'])) ? '' : 'checked'; ?> /></td>
			</tr>
			<tr>
				<th scope="row">
						<?php _e('Display Profile Link', 'photo-feed'); ?>: <span class="dashicons dashicons-editor-help"></span>
					<p class="description"><?php _e('Display the button to open the Instagram profile page.', 'photo-feed'); ?> </p>
				</th>
				<td><input name="pff-instalink" data-toggle-checks="#pfa-section-igbtn" type="checkbox" value="1"
					<?php echo (empty($PFFFs['pff-instalink'])) ? '' : 'checked'; ?> /></td>
			</tr>
			<tr id="pfa-section-igbtn" class="pfa-tab-content-row <?php if (!empty($PFFFs['pff-instalink'])) echo 'active'; ?>">
				<td colspan="2" class="pfa-py-0">
					<table>
						<tr>
							<th scope="row"><?php _e('Link Text', 'photo-feed'); ?>:</th>
							<td><input name="pff-instalink-text" type="text" placeholder="@username"
								value="<?php
        if (! empty($PFFFs['pff-instalink-text'])) {
            echo $PFFFs['pff-instalink-text'];
        }
        ?>" /></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
		<div>
			<button class="button button-primary" type="submit"><?php _e('Update', 'photo-feed'); ?></button>
		</div>
		<input type="hidden" name="pfds-form-update" value="true" /> <input type="hidden" name="pff-gid"
			value="<?php echo isset($_GET['pf-gid']) ? $_GET['pf-gid'] : 0;?>" />
			<?php wp_nonce_field('pfds-form-nonce'); ?>
		</form>
	<div id="ig-preview-panel">
		<h3><?php _e('Preview', 'photo-feed'); ?></h3>
		<hr class="pfa-hr-title" />
		<div id="ig-preview"><?php
$gid = isset($_GET['pf-gid']) ? $_GET['pf-gid'] : 1;
PhotoFeed($gid);
?></div>
	</div>
</div>
<script>
		var pfa = {
			prActive : false,
			prPending: false
		};
		jQuery(document).ready(function ($) {
			$('.pff-color-picker').wpColorPicker({
				change: function (event, ui) {
					setTimeout(loadPFPreview,500);
				}
			});
			
			// Toggle section [checkbox]
			$('input[data-toggle-checks]').on('change', function () {
				var $ttarget = $($(this).data('toggle-checks'));
				if (!$ttarget.length) {
					return;
				}
				if (this.checked) {
					$ttarget.show('fast').addClass('active');
					} else {
					$ttarget.hide('fast').removeClass('active');
				}
			});
			
			// toggle section [radio]
			$('input[data-toggle-radios]').on('change', function () {
				var $e = $(this);
				var $ttarget = $($e.data('toggle-radios'));
				if (!$ttarget.length) {
					return;
				}
				
				$('[name="'+ $e.attr('name') +'"]').each(function(){
					var $ttarget2 = $($(this).data('toggle-radios'));
					if ($ttarget2.length) {
						if($e.is($(this))){
							$ttarget2.show().addClass('active');
							} else {
							$ttarget2.hide().removeClass('active');
						}
					}
				});
				
			});
			
			jQuery('#pfa-form-update input[type="text"],#pfa-form-update input[type="checkbox"],#pfa-form-update input[type="radio"],#pfa-form-update input[type="number"],#pfa-form-update select').on('change', function () {
				loadPFPreview();
			});
			
			// toggle help
			$('.pfa-table-edit').on('click','.dashicons-editor-help',function (){
				var $target = $(this).parent().find('.description');
				if($target.length){
					$target.fadeToggle();
				}
			});
		});
		
		// Preview Gallery
		function loadPFPreview() {
			if(pfa.prActive){
				pfa.prPending = true;
				return;
			}
			if(pfa.prPending){
				pfa.prPending = false;
			}
			pfa.prActive = true;
			
			$f = jQuery('#pfa-form-update');
			var $fresponse = jQuery('#ig-preview');
			var data = $f.serialize() + "&action=pfa_preview_gallery";
			data = data.replace('pfds-form-update', 'pfds-form-update-preview')
			jQuery.ajax({
				url: ajaxurl,
				type: 'post',
				dataType: 'JSON',
				data: data,
				beforeSend: function ()
				{
					$fresponse.addClass('loading');
				},
				success: function (response) {
					if ((typeof response === 'object') && response.hasOwnProperty('success')) {
						$fresponse.html(response.data);
						} else {
						console.log('unable to load preview.');
					}
				}
				}).fail(function (jqXHR, textStatus) {
				console.log(textStatus);
			}).always(function ()
			{
				$fresponse.removeClass('loading');
				pfa.prActive = false;
				if(pfa.prPending){
					loadPFPreview();
				}
			});
		}
	</script>
