<?php

/**
 * Widgets
 *
 */

class Cap_Widget_Base extends WP_Widget {

    protected $title, $text, $link, $image, $mask, $class;

    const HAS_TITLE = 1; // use for $mask parameter
    const HAS_TEXT  = 2;
    const HAS_IMAGE = 4;

    public function __construct ($id, $name, $mask, $widget_ops) {
        $this->mask = $mask;
        $this->class = $widget_ops['classname'];
        $control_ops = array ('width' => 400, 'height' => 350);
        parent::__construct ($id, $name, $widget_ops, $control_ops);
    }

    protected function make_link ($text, $link, $classes = 'ssdone') {
        return empty ($link) ? $text : "<a$link class=\"$classes\">$text</a>";
    }

    protected function sanitize ($text) {
        return empty ($text) ? '' : strip_tags ($text);
    }

    protected function widget_setup ($args, $instance) {
        $this->title = apply_filters ('widget_title',
                                      empty ($instance['title']) ? '' : $instance['title'],
                                      $instance, $this->id_base);

        $this->text = apply_filters ('widget_text',
                               empty ($instance['content']) ? '' : $instance['content'],
                               $instance);

        $this->image = empty ($instance['image']) ? '' : $instance['image'];

        $this->link = empty ($instance['link']) ? '' : ' href="' . $instance['link'] . '"';
    }

    protected function the_widget_title ($args, $instance) {
        echo ($args['before_title']);
        echo $this->make_link ($this->title, $this->link);
        echo ($args['after_title']);
    }

    protected function the_widget_body ($args, $instance) {
        echo ("<div class=\"{$this->class}-body\">{$this->text}</div>\n");
        echo $this->make_link (__('mehr lesen', 'text_domain'), $this->link, 'mehr-lesen ssdone');
    }

    protected function the_widget_image ($args, $instance) {
        echo $this->make_link ("<img src=\"{$this->image}\" alt =\"\">", $this->link);
    }

    protected function the_option ($instance, $name, $caption, $placeholder) {
        $value = !empty ($instance[$name]) ? $instance[$name] : '';
        $caption = __($caption);
        $placeholder = __($placeholder);
        echo ("<p><label for=\"{$this->get_field_id ($name)}\">$caption</label>");
        echo ("<input class=\"widefat\" id=\"{$this->get_field_id ($name)}\" name=\"{$this->get_field_name ($name)}\" type=\"text\" value=\"$value\" placeholder=\"$placeholder\"></p>");
    }

    public function widget ($args, $instance) {
        $this->widget_setup ($args, $instance);

        echo $args['before_widget'];
        if ($this->mask & self::HAS_IMAGE) { $this->the_widget_image ($args, $instance); }
        if ($this->mask & self::HAS_TITLE) { $this->the_widget_title ($args, $instance); }
        if ($this->mask & self::HAS_TEXT)  { $this->the_widget_body  ($args, $instance); }
        echo $args['after_widget'];
    }

    /*
      function update( $new_instance, $old_instance ) {
        // Save widget options
      }
    */
    public function update ($new_instance, $old_instance) {
        $instance = $old_instance;
        if ($this->mask & self::HAS_TITLE) { $instance['title']   = $this->sanitize ($new_instance['title']);   }
        if ($this->mask & self::HAS_TEXT)  { $instance['content'] = $this->sanitize ($new_instance['content']); }
        if ($this->mask & self::HAS_IMAGE) { $instance['image']   = $this->sanitize ($new_instance['image']);   }
        $instance['link']    = $this->sanitize ($new_instance['link']);
        return $instance;
    }

    /*
      function form( $instance ) {
      // Output admin widget options form
      }
    */

    public function form( $instance ) {
        if ($this->mask & self::HAS_TITLE) {
            $this->the_option ($instance, 'title',   'Title',     'New title');
        }
        if ($this->mask & self::HAS_TEXT)  {
            $this->the_option ($instance, 'content', 'Text',      'New text');
        }
        if ($this->mask & self::HAS_IMAGE) {
            $this->the_option ($instance, 'image',   'Image-URL', 'New image');
        }
        $this->the_option ($instance, 'link', 'Link-URL', 'New link');
    }
}

class Cap_Widget_Text extends Cap_Widget_Base {

    public function __construct () {
        $widget_ops = array (
            'classname' => 'cap_widget_text',
            'description' => __('Arbitrary text.')
        );
        $control_ops = array ('width' => 400, 'height' => 350);
        parent::__construct ('cap_widget_text', __('Capitularia Text Widget'),
                             self::HAS_TITLE | self::HAS_TEXT, $widget_ops);
    }
}

class Cap_Widget_Image extends Cap_Widget_Base {

    public function __construct () {
        $widget_ops = array (
            'classname' => 'cap_widget_image',
            'description' => __('Arbitrary text and image.')
        );
        $control_ops = array ('width' => 400, 'height' => 350);
        parent::__construct ('cap_widget_image', __('Capitularia Image Widget'),
                             self::HAS_TITLE | self::HAS_TEXT | self::HAS_IMAGE, $widget_ops);
    }
}

class Cap_Widget_Logo extends Cap_Widget_Base {

    public function __construct () {
        $widget_ops = array (
            'classname' => 'cap_widget_logo',
            'description' => __('Image and link for the logo bar.')
        );
        $control_ops = array ('width' => 400, 'height' => 350);
        parent::__construct ('cap_widget_logo', __('Capitularia Logo Widget'),
                             self::HAS_TITLE | self::HAS_IMAGE, $widget_ops);
    }

    protected function the_widget_title ($args, $instance) {
        /* inhibit title */
    }

    protected function the_widget_image ($args, $instance) {
        echo $this->make_link ("<img src=\"{$this->image}\" alt =\"\" title=\"{$this->title}\">", $this->link);
    }
}


function cap_register_widgets () {
    register_widget ('Cap_Widget_Text');
    register_widget ('Cap_Widget_Image');
    register_widget ('Cap_Widget_Logo');
}

add_action ('widgets_init', 'cap_register_widgets');

// FIXME: WTF?
// add_filter('widget_text', 'do_shortcode');

?>