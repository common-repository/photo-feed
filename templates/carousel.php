<?php
/**
 * The Template for displaying Carousel Gallery
 *
 * @package 	photo-feed/templates
 * @version     1.1.2
 */
if (! defined('ABSPATH')) {
    exit();
}

// $PFDS : array of display settings
// $ifeed : array of feed items
// $iprofile : array of profile information

// shortcode counter, used to identify multiple shortcodes
global $iGID;
$iGID = (isset($iGID)) ? ($iGID + 1) : 1;
$iGSelector = '#photo-feed-' . $iGID; // gallery selector

$profileUrl = 'https://www.instagram.com/';
if (isset($iprofile['username'])) {
    $profileUrl .= $iprofile['username'];
}

$spacingClass = $PFDS['pff-spacing'] ? 'ig-spacing' : '';
$hoveredClass = $PFDS['pff-hover'] ? 'ig-hover' : '';

// dynamic CSS
$ig_dstyle = '';
$ig_dstyle .= $iGSelector . ' .swiper-container:not(.swiper-container-initialized) .swiper-slide{flex-basis: ' . (100 / $PFDS['pff-car-ipv']) . '%;}';
if (! empty($PFDS['pff-car-nav-color'])) {
    $ig_dstyle .= $iGSelector . ' .swiper-button-next,' . $iGSelector . ' .swiper-button-prev {color: ' . $PFDS['pff-car-nav-color'] . ';}';
}
if (! empty($PFDS['pff-hover-color'])) {
    $ig_dstyle .= $iGSelector . ' .ig-hover .swiper-slide a:hover:after {background: ' . $PFDS['pff-hover-color'] . ';}';
}

?>
<?php if (!empty($ig_dstyle)): ?>
<style><?php echo $ig_dstyle; ?></style>
<?php endif; ?>

<div class="photo-feed photo-feed-carousel" id="photo-feed-<?php echo $iGID; ?>">
	<div class="photo-feed-carousel-container <?php echo $hoveredClass . ' ' . $spacingClass; ?>">
		<div class="swiper-container">
			<div class="swiper-wrapper">
				<?php
    $i = 1;
    foreach ($ifeed as $item) {

        $img_src = $item['media_url'];
        if (strtoupper($item['media_type']) == 'VIDEO') {

            // exclude
            if (! empty($PFDS['pff-exclude-video'])) {
                continue;
            }

            // get video thumbnail
            if (isset($item['thumbnail_url']) && ! empty($item['thumbnail_url'])) {
                $img_src = $item['thumbnail_url'];
            } else {
                continue;
            }
        }
        $link = $item['permalink'];
        $typeIconClass = 'icon-pf-picture'; // no icon for pictures
        if (strtoupper($item['media_type']) == 'VIDEO') {
            $typeIconClass = 'icon-pf-videocam';
        }
        if (strtoupper($item['media_type']) == 'CAROUSEL_ALBUM') {
            $typeIconClass = 'icon-pf-pictures';
        }
        ?>
					<div class="swiper-slide">
					<a href="<?php echo $link; ?>" target="_blank" rel="noopener" class="nofancybox"><img src="<?php echo $img_src; ?>" alt="Instagram"
						loading="lazy" />
							<?php
        if ($PFDS['pff-type-icon'] && ($typeIconClass != 'icon-pf-picture')) {
            echo '<span class="ig-type-icon">';
            echo '<i class="' . $typeIconClass . '" aria-hidden="true"></i>';
            echo '</span>';
        }
        ?>
							<span class="insta-icon-wrapper"><i class="icon-pf-instagram" aria-hidden="true"></i></span> </a>
				</div>
					<?php
        $i ++;
        if (($PFDS['pff-limit'] != 0) && ($i > $PFDS['pff-limit'])) {
            break;
        }
    }
    ?>
			</div>
			<!-- Add Navigation -->
			<?php if (!empty($PFDS['pff-car-nav'])) { ?>
				<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
			<?php } ?>
		</div>
	</div>
    <?php
    if ($PFDS['pff-instalink']) {
        ?>
        <div class="photo-feed-actions">
		<a href="<?php echo $profileUrl; ?>" target="_blank" class="photo-feed-button button btn"><span class="icon-pf-instagram"></span> <?php echo $PFDS['pff-instalink-text']; ?></a>
	</div>
	<?php } ?>
</div>
<!-- Initialize Carousel -->
<script>
	(function(){
		var PFDS = <?php echo json_encode($PFDS); ?>;
		PFDS['pff-car-ipv'] = parseInt(PFDS['pff-car-ipv']);
		var soptions = {
			loop : true,
			autoHeight : true,
			observer : true,
			observeParents : true,
		};
		var interval = parseInt(PFDS['pff-car-autoplay']);
		if (interval > 0) {
			interval *= 1000; // milliseconds
			soptions.autoplay = {
				delay : interval,
				disableOnInteraction: true
			};
		}
		if (PFDS['pff-car-nav']) {
			soptions.navigation = {
				nextEl : '.swiper-button-next',
				prevEl : '.swiper-button-prev',
			};
		}
		var spacing = spacingSM = parseInt(PFDS['pff-spacing']);
		if (spacing > 0) {
			soptions.spaceBetween = spacing;
			spacingSM = Math.ceil(spacing / 2);
		}
		soptions.slidesPerView = 1;
		soptions.breakpoints = {};
		if (PFDS['pff-car-ipv'] > 4) {
			soptions.breakpoints[1024] = {
				slidesPerView : PFDS['pff-car-ipv'],
				spaceBetween : spacing
			};
		}
		if (PFDS['pff-car-ipv'] > 3) {
			soptions.breakpoints[768] = {
				slidesPerView : 4,
				spaceBetween : spacing
			};
		}
		if (PFDS['pff-car-ipv'] > 2) {
			soptions.breakpoints[576] = {
				slidesPerView : 3,
				spaceBetween : spacingSM
			};
		}
		if (PFDS['pff-car-ipv'] > 1) {
			soptions.breakpoints[300] = {
				slidesPerView : 2,
				spaceBetween : spacingSM
			};
		}
		
		function initSwiper(){
			var swiper = new Swiper('<?php echo $iGSelector; ?> .swiper-container', soptions);
		}
		if(typeof Swiper == 'function'){
			initSwiper();
		} else {
			window.addEventListener('DOMContentLoaded', function () {
				initSwiper();
			});
		}
	})();
</script>