<?php
if (! defined('ABSPATH')) {
    die();
}

/**
 * Class PHOTOFEED_Widget
 */
class PHOTOFEED_Widget extends WP_Widget
{

    /**
     * Constructs the new widget.
     */
    public function __construct()
    {
        parent::__construct('PHOTOFEED_Widget', __('Photo Feed Gallery', 'photo-feed'), [
            'classname' => 'photo-feed-widget',
            'description' => esc_html__('Displays your Instagram Gallery created in the Photo Feed plugin.', 'photo-feed')
        ]);
    }

    /**
     * The widget's HTML output.
     *
     * @param array $args
     *            before_title, after_title, before_widget, after_widget.
     * @param array $instance
     *            widget instance settings.
     */
    public function widget($args, $instance)
    {
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
        $photofeed_id = empty($instance['photofeed_id']) ? '' : $instance['photofeed_id'];

        echo $args['before_widget'];

        if (! empty($title)) {
            echo $args['before_title'] . wp_kses_post($title) . $args['after_title'];
        }

        if (! empty($photofeed_id)) {
            echo do_shortcode('[photo-feed id="' . $photofeed_id . '"]');
        }

        echo $args['after_widget'];
    }

    /**
     * Output the admin widget options form HTML.
     *
     * @param array $instance
     *            The current widget settings.
     * @return string The HTML markup for the form.
     */
    public function form($instance)
    {
        global $PhotoFeedAccounts, $PhotoFeedGalleries;
        $PhotoFeedAccounts = get_option('photofeed_accounts');
        $PhotoFeedGalleries = get_option('photofeed_galleries');
        $ig_profiles = ig_getProfiles();
        
        $instance = wp_parse_args((array) $instance, array(
            'title' => '',
            'photofeed_id' => 0
        ));

        $title = $instance['title'];
        $photofeed_id = $instance['photofeed_id'];
        ?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'photo-feed' ); ?>: <input
		class="widefat"
		id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
		type="text" value="<?php echo esc_attr( $title ); ?>" /></label>
</p>

<?php if( !empty($PhotoFeedGalleries) && count($PhotoFeedGalleries) ):  ?>
<p>
	<label
		for="<?php echo esc_attr( $this->get_field_id( 'photofeed_id' ) ); ?>"><?php esc_html_e( 'Select Gallery', 'photo-feed' ); ?>: </label>
	<select
		id="<?php echo esc_attr( $this->get_field_id( 'photofeed_id' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'photofeed_id' ) ); ?>"
		class="widefat">
    <?php
            foreach ($PhotoFeedGalleries as $gid => $gallery) {
                $label = '@';
                if (isset($ig_profiles[$gallery['pff-aid']])) {
                    $label .= $ig_profiles[$gallery['pff-aid']]['username'];
                } else {
                    $label .= $gallery['pff-aid'];
                }
                ?>		
		<option value="<?php echo $gid; ?>"
			<?php selected( $gid, $photofeed_id ) ?>><?php echo $label; ?></option>
<?php } ?>
	</select>
</p>
<?php else: ?>
<p style="color: #e23565;">
	<?php _e('Please add a Gallery item in the plugin panel, Then you can select the added Gallery here.','photo-feed'); ?>
</p>
<?php endif; ?>
<p style="text-align: center;">
	<a href="options-general.php?page=photo-feed" target="_blank"><?php _e('Add New Gallery','photo-feed'); ?></a>
</p>

<?php
    }

    /**
     * The widget update handler.
     *
     * @param array $new_instance
     *            The new instance of the widget.
     * @param array $old_instance
     *            The old instance of the widget.
     * @return array The updated instance of the widget.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['photofeed_id'] = trim(strip_tags($new_instance['photofeed_id']));
        return $instance;
    }
}
