<?php

if (!defined('ABSPATH')) exit;

?><article class="elfsight-admin-page-welcome elfsight-admin-page" data-elfsight-admin-page-id="welcome">
    <h1><?php _e('Welcome to ' . $this->pluginName, $this->textDomain); ?></h1>

    <p class="elfsight-admin-page-welcome-subheading">
        <?php _e($this->description, $this->textDomain); ?><br>
        <strong><?php _e('Let’s create your first widget!', $this->textDomain); ?></strong>
    </p>

    <a class="elfsight-admin-page-welcome-button elfsight-admin-button-large elfsight-admin-button-green elfsight-admin-button" href="#/add-widget/" data-elfsight-admin-page="add-widget"><?php _e('Create widget', $this->textDomain); ?></a>
</article>