<?php

if (!defined('ABSPATH')) exit;

?><article class="elfsight-admin-page-widgets elfsight-admin-page" data-elfsight-admin-page-id="widgets">
    <div class="elfsight-admin-page-heading">
        <h2><?php _e('Widgets', $this->textDomain); ?></h2>

        <a class="elfsight-admin-page-widgets-add-new elfsight-admin-button-green elfsight-admin-button" href="#/add-widget/" data-elfsight-admin-page="add-widget"><?php _e('Create widget', $this->textDomain); ?></a>

        <div class="elfsight-admin-page-heading-subheading"><?php _e('Create, edit or remove your widgets. Use their shortcodes to insert them into the required place.', $this->textDomain); ?></div>
    </div>

    <table class="elfsight-admin-page-widgets-list">
        <thead>
            <tr>
                <th><span><?php _e('Name', $this->textDomain); ?></span></th>
                <th><span><?php _e('Shortcode', $this->textDomain); ?></span></th>
                <th><span><?php _e('Last updated', $this->textDomain); ?></span></th>
                <th><span><?php _e('Actions', $this->textDomain); ?></span></th>
            </tr>
        </thead>

        <tbody></tbody>
    </table>

    <template class="elfsight-admin-template-widgets-list-item elfsight-admin-template">
        <tr class="elfsight-admin-page-widgets-list-item">
            <td class="elfsight-admin-page-widgets-list-item-name"><a href="#" data-elfsight-admin-page="edit-widget"></a></td>

            <td class="elfsight-admin-page-widgets-list-item-shortcode">
                <span class="elfsight-admin-page-widgets-list-item-shortcode-hidden"></span>

                <input type="text" class="elfsight-admin-page-widgets-list-item-shortcode-input" readonly></input>

                <span type="text" class="elfsight-admin-page-widgets-list-item-shortcode-value"></span>

                <div class="elfsight-admin-page-widgets-list-item-shortcode-copy">
                    <span class="elfsight-admin-page-widgets-list-item-shortcode-copy-trigger"><span>Copy</span></span>
                    
                    <div class="elfsight-admin-page-widgets-list-item-shortcode-copy-error">Press Cmd+C to copy</div>
                </div>
            </td>

            <td class="elfsight-admin-page-widgets-list-item-date"></td>

            <td class="elfsight-admin-page-widgets-list-item-actions">
                <a href="#" class="elfsight-admin-page-widgets-list-item-actions-edit"><?php _e('Edit', $this->textDomain); ?></a>
                <a href="#" class="elfsight-admin-page-widgets-list-item-actions-duplicate"><?php _e('Duplicate', $this->textDomain); ?></a>
                <a href="#" class="elfsight-admin-page-widgets-list-item-actions-remove"><?php _e('Remove', $this->textDomain); ?></a>

                <span class="elfsight-admin-page-widgets-list-item-actions-restore">
                    <span class="elfsight-admin-page-widgets-list-item-actions-restore-label"><?php _e('The widget has been removed.', $this->textDomain); ?></span>
                    <a href="#"><?php _e('Restore it', $this->textDomain); ?></a>
                </span>
            </td>
        </tr>
    </template>

     <template class="elfsight-admin-template-widgets-list-empty elfsight-admin-template">
        <tr class="elfsight-admin-page-widgets-list-empty-item">
            <td class="elfsight-admin-page-widgets-list-empty-item-text" colspan="3">
                <?php _e('There is no any widget yet.', $this->textDomain); ?>
                <a href="#/add-widget/" data-elfsight-admin-page="add-widget"><?php _e('Create the first one.', $this->textDomain); ?></a>
            </td>
        </tr>
    </template>
</article>
