<?php

namespace Listings\Ajax\Actions;

use Listings\Ajax\Action;

class UploadFile extends Action {

    public function getActionString()
    {
        return 'upload_file';
    }

    public function doAction()
    {
        $data = array( 'files' => array() );

        if ( ! empty( $_FILES ) ) {
            foreach ( $_FILES as $file_key => $file ) {
                $files_to_upload = listings_prepare_uploaded_files( $file );
                foreach ( $files_to_upload as $file_to_upload ) {
                    $uploaded_file = listings_upload_file( $file_to_upload, array( 'file_key' => $file_key ) );

                    if ( is_wp_error( $uploaded_file ) ) {
                        $data['files'][] = array( 'error' => $uploaded_file->get_error_message() );
                    } else {
                        $data['files'][] = $uploaded_file;
                    }
                }
            }
        }

        wp_send_json( $data );
    }
}