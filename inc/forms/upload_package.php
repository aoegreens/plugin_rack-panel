<?php

function get_package($package_name)
{
    $package_post = get_page_by_path($package_name, OBJECT, 'package');
    if ($package_post)
    {
        GFCommon::log_debug(__METHOD__ . '('.$package_name.'): Found package: '. $package_post->ID);
        return $package_post;
    }
    GFCommon::log_debug(__METHOD__ . '('.$package_name.'): Package not found.');
    return NULL;
}

add_action( 'gform_pre_process_1', function ( $form ) {
    GFCommon::log_debug( 'gform_pre_process: running' );
    $package_name = rgpost('input_1');
    $base64_string = rgpost( 'input_3' );

    if ( ! empty( $base64_string ) ) {
        GFCommon::log_debug( 'gform_pre_process: found string' );
        $target_dir = GFFormsModel::get_upload_path( $form['id'] ) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        if ( ! is_dir( $target_dir ) ) {
            GFCommon::log_debug( 'gform_pre_process: creating tmp folder' );
            if ( ! wp_mkdir_p( $target_dir ) ) {
                GFCommon::log_debug( "gform_pre_process: Couldn't create the tmp folder: " . $target_dir );

                return;
            } else {
                GFCommon::recursive_add_index_file( $target_dir );
            }
        }

        $upload_field_id = 3;
        $file_extension  = 'zip';

        $file_contents = base64_decode($base64_string); //preg_replace( '#^application:zip/\w+;base64,#i', '', $base64_string ) );
        $temp_filename = sprintf( '%s_input_%s.%s', GFFormsModel::get_form_unique_id( $form['id'] ), $upload_field_id, $file_extension );

        $result = file_put_contents( $target_dir . $temp_filename, $file_contents );
        GFCommon::log_debug( 'gform_pre_process: file_put_contents result: ' . var_export( $result, true ) );

        $uploaded_file_name = $package_name.'.zip';

        $_POST['gform_uploaded_files'] = json_encode( array( 'input_' . $upload_field_id => $uploaded_file_name ) );
    }
} );

function verify_user_access( $validation_result )
{
    $form  = $validation_result['form'];
    $entry = GFFormsModel::get_current_lead();

    //If there is no package, allow the user to create one.
    $validation_result['is_valid'] = true;
    $user_has_access = true;

    $package_name = rgar($entry, '1');
    GFCommon::log_debug(__METHOD__ . '(): Checking user access for ' . $package_name);
    $package = get_package($package_name);

    if ($package)
    {
        $user_has_access = false; //Package exists. Check if user has access.
        $owner = $package->post_author;
        GFCommon::log_debug(__METHOD__ . '(): Checking access for user ' . get_current_user_id(). ' for package ' . $package_name . '(' . $package->ID . '; owner=' . $owner . ')');
        $user_has_access = $owner == get_current_user_id();
    }

    if (!$user_has_access)
    {
        $validation_result['is_valid'] = false;
        $form['fields'][0]->failed_validation = true;
        $form['fields'][0]->validation_message = 'You do not have access to update this package.';
        GFCommon::log_debug(__METHOD__ . '(): User ' . get_current_user_id(). ' does not have access to package ' . $package_name . '(' . $package->ID . ')');
    }

    $validation_result['form'] = $form;
    return $validation_result;
}
add_filter( 'gform_validation_1', 'verify_user_access' );

function update_package($entry, $form)
{
    //upload package submissions for logged in users only.
    if ($form['id'] != 1 || !is_user_logged_in())
    {
        return;
    }

    $package_name = rgar($entry, '1');
    $package_version = rgar($entry, '2');
    $package_file = rgar($entry, '3');
    $package_visibility = rgar($entry, '4');
    $package_type = rgar($entry, '5');
    $package_description = rgar($entry, '6');
    $package = get_package($package_name);

    $package_data = array(
        'post_name' => $package_name,
        'author' => get_current_user_id(),
        'post_title' => $package_name,
        'post_content' => $package_description,
        'post_type' => 'package',
// 		'post_date' => date('Y-m-d H:i:s'),
        'post_status' => $package_visibility,
        'meta_input' => array(
            'version' => $package_version,
            'package_type' => $package_type,
            'file' => $package_file,
        )
    );
    if ($package)
    {
        GFCommon::log_debug(__METHOD__ . '(): Updating existing package: ' . $package->ID);
        $package_data['ID'] = $package->ID;
    }
    else
    {
        $package_data['meta_input']['authorized_users'] = array(get_current_user_id());
    }
    wp_insert_post($package_data);
}
add_action('gform_after_submission_1', 'update_package', 10, 2 );
