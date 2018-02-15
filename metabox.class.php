<?php
/**
 * Class addMetaboxes for easy metabox creation
 *
 * Just create object by thhis class and call init method
 * As metaboxes params is array(array('id' => '', ....))
 *
 * Example:
 * $careers_metabox_args = array(
 *                          array('id' => 'email', 'title' => 'E-mail', 'type' => 'email', 'label' => 'E-mail')
 *                          array('id' => 'test', 'title' => 'Test field', 'type' => 'text', 'label' => 'Put test field')
 *                         );
 * $careers_metabox = new addMetaboxes();
 * $careers_metabox->init($careers_metabox_args, 'careers');
 */
class addMetaboxes{

    public $metaboxes;
    public $screen;

    /**
     * On init add actions for add_metaboxes and save post
     * Here also set metabox data and screen (post type)
     *
     * @param array $metaboxes
     * @param string $screen
     */
    public function init($metaboxes = array(), $screen = 'post'){

        $this->set_metaboxes($metaboxes);
        $this->screen = $screen;
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        add_action('save_post_'.$screen, array($this, 'save'));

    }

    /**
     * Set metaboxes data
     *
     * @param $metaboxes - array
     */
    public function set_metaboxes($metaboxes){
        $this->metaboxes = $metaboxes;
    }

    /**
     *  Call adding metaboxes
     *
     */
    public function add_metaboxes(){
        foreach ($this->metaboxes as $k=>$metabox) {
            $args = array(
                'type' => $metabox['type'],
                'label' => $metabox['label'],
                'options' => (isset($metabox['values']))?$metabox['values']:null,
            );
            add_meta_box(
                $metabox['id'],
                $metabox['title'],
                array($this, 'html'),
                $this->screen,
                (!empty($metabox['context']))?$metabox['context']:'normal',
                'default',
                $args
            );
        }
    }

    /**
     * Show metabox in edit post page
     *
     * @param object $post
     * @param array $metabox data
     */
    public function html($post, $metabox){

        $type = $metabox['args']['type'];
        $metabox_value = get_post_meta($post->ID, $metabox['id'], true);
        wp_nonce_field( -1, 'matabox_nonce_field' );
        $content = '';
        if($type == 'checkbox'){
            $checked = ($metabox_value == 'on')?'checked':'';
            $content .= '<input type="checkbox" id="'.$metabox['id'].'" name="'.$metabox['id'].'" '.$checked.' />';
            $content .= '<label for="'.$metabox['id'].'">' . $metabox['args']['label'] . '</label> ';
        }elseif($type == 'number') {
            $content .= '<label for="' . $metabox['id'] . '">' . $metabox['args']['label'] . '</label><br>';
            $content .= '<input type="number" id="' . $metabox['id'] . '" name="' . $metabox['id'] . '" value="' . $metabox_value . '" maxsize="3" />';
        }elseif($type == 'email') {
            $content .= '<label for="' . $metabox['id'] . '">' . $metabox['args']['label'] . '</label><br>';
            $content .= '<input type="email" id="' . $metabox['id'] . '" name="' . $metabox['id'] . '" value="' . $metabox_value . '" maxsize="3" />';
        }else{
            $content .= '<input type="text" id= "myplugin_new_field" name="'.$metabox['id'].'" value="whatever" />';
        }

        echo $content;
    }


    /**
     * Save meta for post
     *
     * @param $post_id
     * @return mixed
     */
    public function save( $post_id ) {

        if ( ! wp_verify_nonce( $_POST['matabox_nonce_field'], -1 ) )
            return $post_id;

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
            return $post_id;

        if ( $this->screen == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        } elseif( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
        foreach($this->metaboxes as $k=>$m_box){
            $m_val = sanitize_text_field( $_POST[$m_box['id']] );
            if ($m_box['type'] == 'checkbox') {
                if($m_val == 'off') $m_val = null;
            }
            update_post_meta( $post_id, $m_box['id'], $m_val );
        }
    }
}

