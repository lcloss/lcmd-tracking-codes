<?php if ( !defined('ABSPATH') ) exit; ?>
    <div id="lcmd-tracking-codes-panel" class="wrap">
        <h1>LC Tracking Codes</h1>
        <h2 class="nav-tab-wrapper">
            <a href="admin.php?page=lcmd-tracking-codes&tab=google" class="nav-tab">Google</a>
            <a href="admin.php?page=lcmd-tracking-codes&tab=bing" class="nav-tab">Bing</a>
            <a href="admin.php?page=lcmd-tracking-codes&tab=general" class="nav-tab">General</a>
        </h2>
        <?php settings_errors( 'lcmd_tracking_codes_messages' ); ?>
        <div class="wrap">
            <?php if ( $tab == 'google' ) : ?>
            <h2>Google Tracking Codes</h2>
            <form method="POST" enctype="multipart/form-data">
                <?php settings_fields( 'lcmd_tracking_codes_settings_group_' . $tab ); ?>
                <input type="hidden" name="tab" value="google">
                <?php foreach($google_fields as $field) : ?>
                <div class="form-group">
                    <label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
                    <p class="help"><?php echo $field['help']; ?></p>
                    <?php if ( 'lcmd_gsvf' == $field['name'] ) : ?>
                    <?php if ( $field['value'] == '' ) : ?>
                    <input type="file" class="form-control" id="<?php echo $field['id']; ?>" name="<?php echo $field['name']; ?>">
                    <?php else : ?>
                    <p id="file_link" class="file_link">
                        <a href="<?php echo $field['value']; ?>" target="_blank"><?php echo basename( $field['value'] ); ?></a>
                        &nbsp;&nbsp;<button type="button" id="file_link_delete" file="<?php echo basename( $field['name'] ) . '_name'; ?>"><?php echo __('Delete', 'lcmd_tracking_codes'); ?></button>
                    </p>
                    <?php endif; ?>
                    <?php else : ?>
                    <input type="text" class="form-control" id="<?php echo $field['id']; ?>" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" placeholder="<?php echo $field['placeholder']; ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php submit_button( __('Save Settings', self::get_text_domain()) ); ?>
            </form>
            <?php endif; ?>

            <?php if ( $tab == 'bing' ) : ?>
            <h2>Bing Tracking Codes</h2>
            <form method="POST" enctype="multipart/form-data">
                <?php settings_fields( 'lcmd_tracking_codes_settings_group_' . $tab ); ?>
                <input type="hidden" name="tab" value="bing">
                <?php foreach($bing_fields as $field) : ?>
                <div class="form-group">
                    <label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
                    <p class="help"><?php echo $field['help']; ?></p>
                    <?php if ( 'lcmd_bcf' == $field['name'] ) : ?>
                    <?php if ( $field['value'] == '' ) : ?>
                    <input type="file" class="form-control" id="<?php echo $field['id']; ?>" name="<?php echo $field['name']; ?>">
                    <?php else : ?>
                    <p class="file_link">
                        <a href="<?php echo $field['value']; ?>" target="_blank"><?php echo basename( $field['value'] ); ?></a>
                        &nbsp;&nbsp;<button type="button" id="file_link_delete" file="<?php echo basename( $field['name'] ) . '_name'; ?>"><?php echo __('Delete', 'lcmd_tracking_codes'); ?></button>
                    </p>
                    <?php endif; ?>
                    <?php else : ?>
                    <input type="text" class="form-control" id="<?php echo $field['id']; ?>" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php submit_button( __('Save Settings', self::get_text_domain()) ); ?>
            </form>
            <?php endif; ?>

            <?php if ( $tab == 'general' ) : ?>
            <h2>General Tracking Codes</h2>
            <form method="POST">
                <?php settings_fields( 'lcmd_tracking_codes_settings_group_' . $tab ); ?>
                <input type="hidden" name="tab" value="general">
                <?php foreach($general_fields as $field) : ?>
                <div class="form-group">
                    <label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
                    <p class="help"><?php echo $field['help']; ?></p>
                    <textarea class="form-control" id="<?php echo $field['id']; ?>" name="<?php echo $field['name']; ?>" cols="100" rows="6"><?php echo $field['value']; ?></textarea>
                </div>
                <?php endforeach; ?>
                <?php submit_button( __('Save Settings', self::get_text_domain()) ); ?>
            </form>
            <?php endif; ?>

        </div>
    </div>
