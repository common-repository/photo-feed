<?php
/**
 * The Template for displaying Grid Gallery
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
if (! empty($PFDS['pff-hover-color'])) {
    $ig_dstyle .= $iGSelector . ' .ig-hover .ig-item a:hover:after {background: ' . $PFDS['pff-hover-color'] . ';}';
}
if (isset($PFDS['pff-spacing'])) {
    $spacing = (int) $PFDS['pff-spacing'];
    $spacingSM = $spacingSM2 = 0;
    if ($spacing > 0) {
        $spacingSM = ceil($spacing / 2);
        $spacingSM2 = ceil($spacingSM / 2);
    }
    $ig_dstyle .= "{$iGSelector} .photo-feed-grid-container.ig-spacing .photo-feed-items {
		margin-left: -{$spacingSM}px;
		margin-right: -{$spacingSM}px;
	}";
    $ig_dstyle .= "{$iGSelector} .photo-feed-grid-container.ig-spacing .ig-item {
		padding: {$spacingSM}px;
	}";
    $ig_dstyle .= " @media screen and (max-width: 767px) { ";
    $ig_dstyle .= "{$iGSelector} .photo-feed-grid-container.ig-spacing .photo-feed-items {
		margin-left: -{$spacingSM2}px;
		margin-right: -{$spacingSM2}px;
	}";
    $ig_dstyle .= "{$iGSelector} .photo-feed-grid-container.ig-spacing .ig-item {
		padding: {$spacingSM2}px;
	}";
    $ig_dstyle .= "} ";
}

?>
<?php if (!empty($ig_dstyle)): ?>
<style><?php echo $ig_dstyle; ?></style>
<?php endif; ?>

<div class="photo-feed photo-feed-grid" id="photo-feed-<?php echo $iGID; ?>">
	<div class="photo-feed-grid-container <?php echo $hoveredClass . ' ' . $spacingClass; ?>">
		<div class="photo-feed-items">
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
            <div class="ig-item ig-col-<?php echo $PFDS['pff-cols']; ?>" style="width: <?php echo (floor((100 / $PFDS['pff-cols'])*1000)/1000); ?>%;">
				<a href="<?php echo $link; ?>" target="_blank" rel="noopener" class="nofancybox"><img src="<?php echo $img_src; ?>" alt="Instagram" loading="lazy" />
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
	</div>
    <?php
    if ($PFDS['pff-instalink']) {
        ?>
        <div class="photo-feed-actions">
		<a href="<?php echo $profileUrl; ?>" target="_blank" class="photo-feed-button button btn"><span class="icon-pf-instagram" aria-hidden="true"></span> <?php echo $PFDS['pff-instalink-text']; ?></a>
	</div>
    <?php } ?>
</div>
