<?php

if (!defined('ABSPATH')) exit;

?><div class="elfsight-admin-menu-actions">
	<div class="elfsight-admin-menu-actions-activation-container">
		<span class="elfsight-admin-menu-actions-activation-label"><?php _e('CodeCanyon License:', $this->textDomain); ?></span>
		<a class="elfsight-admin-menu-actions-activation-status" href="#/activation/" data-elfsight-admin-page="activation"><?php _e('Not Activated', $this->textDomain); ?></span>

    	<a class="elfsight-admin-menu-actions-activation-button elfsight-admin-button-black elfsight-admin-button-border elfsight-admin-button" href="#/activation/" data-elfsight-admin-page="activation"><?php _e('Activate License', $this->textDomain); ?></a>
	</div>

    <?php if ($has_new_version) {?>
        <span class="elfsight-admin-menu-actions-update-container">
        	<span class="elfsight-admin-menu-actions-update-label"><?php _e('A new version is available', $this->textDomain); ?></span>

        	<a class="elfsight-admin-menu-actions-update elfsight-admin-button-green elfsight-admin-button" href="<?php echo is_multisite() ? network_admin_url('update-core.php') : admin_url('update-core.php'); ?>"><?php _e('Update to', $this->textDomain); ?> <?php echo $latest_version; ?></a>
    	</span>
    <?php }?>
</div>