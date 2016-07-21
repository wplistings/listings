<?php

namespace Listings\Admin;

abstract class Metabox
{
    /**
     * input_file function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_file( $key, $field ) {
        global $thepostid;

        if ( ! isset( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( empty( $field['placeholder'] ) ) {
            $field['placeholder'] = 'http://';
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
            <?php
            if ( ! empty( $field['multiple'] ) ) {
                foreach ( (array) $field['value'] as $value ) {
                    ?><span class="file_url"><input type="text" name="<?php echo esc_attr( $name ); ?>[]" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $value ); ?>" /><button class="button button-small listings_upload_file_button" data-uploader_button_text="<?php _e( 'Use file', 'listings' ); ?>"><?php _e( 'Upload', 'listings' ); ?></button></span><?php
                }
            } else {
                ?><span class="file_url"><input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" /><button class="button button-small listings_upload_file_button" data-uploader_button_text="<?php _e( 'Use file', 'listings' ); ?>"><?php _e( 'Upload', 'listings' ); ?></button></span><?php
            }
            if ( ! empty( $field['multiple'] ) ) {
                ?><button class="button button-small listings_add_another_file_button" data-field_name="<?php echo esc_attr( $key ); ?>" data-field_placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" data-uploader_button_text="<?php _e( 'Use file', 'listings' ); ?>" data-uploader_button="<?php _e( 'Upload', 'listings' ); ?>"><?php _e( 'Add file', 'listings' ); ?></button><?php
            }
            ?>
        </p>
        <?php
    }

    /**
     * input_text function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_text( $key, $field ) {
        global $thepostid;

        if ( ! isset( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        if ( ! empty( $field['classes'] ) ) {
            $classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : array( $field['classes'] ) );
        } else {
            $classes = '';
        }
        ?>
        <p class="form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
            <input type="text" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
        </p>
        <?php
    }

    /**
     * input_text function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_textarea( $key, $field ) {
        global $thepostid;

        if ( ! isset( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
            <textarea name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_html( $field['value'] ); ?></textarea>
        </p>
        <?php
    }

    /**
     * input_select function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_select( $key, $field ) {
        global $thepostid;

        if ( ! isset( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
            <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>">
                <?php foreach ( $field['options'] as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php if ( isset( $field['value'] ) ) selected( $field['value'], $key ); ?>><?php echo esc_html( $value ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * input_select function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_multiselect( $key, $field ) {
        global $thepostid;

        if ( ! isset( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>: <?php if ( ! empty( $field['description'] ) ) : ?><span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span><?php endif; ?></label>
            <select multiple="multiple" name="<?php echo esc_attr( $name ); ?>[]" id="<?php echo esc_attr( $key ); ?>">
                <?php foreach ( $field['options'] as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) selected( in_array( $key, $field['value'] ), true ); ?>><?php echo esc_html( $value ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * input_checkbox function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_checkbox( $key, $field ) {
        global $thepostid;

        if ( empty( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="form-field form-field-checkbox">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?></label>
            <input type="checkbox" class="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $field['value'], 1 ); ?> />
            <?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
        </p>
        <?php
    }

    /**
     * Box to choose who posted the job
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_author( $key, $field ) {
        global $thepostid, $post;

        if ( ! $post || $thepostid !== $post->ID ) {
            $the_post  = get_post( $thepostid );
            $author_id = $the_post->post_author;
        } else {
            $author_id = $post->post_author;
        }

        $posted_by      = get_user_by( 'id', $author_id );
        $field['value'] = ! isset( $field['value'] ) ? get_post_meta( $thepostid, $key, true ) : $field['value'];
        $name           = ! empty( $field['name'] ) ? $field['name'] : $key;
        ?>
        <p class="form-field form-field-author">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<span class="current-author">
				<?php
                if ( $posted_by ) {
                    echo '<a href="' . admin_url( 'user-edit.php?user_id=' . absint( $author_id ) ) . '">#' . absint( $author_id ) . ' &ndash; ' . $posted_by->user_login . '</a>';
                } else {
                    _e( 'Guest User', 'listings' );
                }
                ?> <a href="#" class="change-author button button-small"><?php _e( 'Change', 'listings' ); ?></a>
			</span>
			<span class="hidden change-author">
				<input type="number" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" step="1" value="<?php echo esc_attr( $author_id ); ?>" style="width: 4em;" />
				<span class="description"><?php _e( 'Enter the ID of the user, or leave blank if submitted by a guest.', 'listings' ) ?></span>
			</span>
        </p>
        <?php
    }

    /**
     * input_radio function.
     *
     * @param mixed $key
     * @param mixed $field
     */
    public static function input_radio( $key, $field ) {
        global $thepostid;

        if ( empty( $field['value'] ) ) {
            $field['value'] = get_post_meta( $thepostid, $key, true );
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="form-field form-field-checkbox">
            <label><?php echo esc_html( $field['label'] ) ; ?></label>
            <?php foreach ( $field['options'] as $option_key => $value ) : ?>
                <label><input type="radio" class="radio" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $field['value'], $option_key ); ?> /> <?php echo esc_html( $value ); ?></label>
            <?php endforeach; ?>
            <?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
        </p>
        <?php
    }
}