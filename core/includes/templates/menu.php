<?php

if (!defined('ABSPATH')) exit;

?><nav class="elfsight-admin-menu">
    <ul class="elfsight-admin-menu-list">
        <?php foreach ($this->menu as $menu_item) { ?>
            <?php if (!empty($menu_item['menu_title'])) {?>
                <li class="elfsight-admin-menu-list-item-<?php echo $menu_item['id'] ?> elfsight-admin-menu-list-item">
                    <a href="#/<?php echo $menu_item['id'] ?>/" data-elfsight-admin-page="<?php echo $menu_item['id'] ?>"<?php echo !empty($menu_item['notification']) ? ' class="elfsight-admin-tooltip-trigger"' : ''?>>
                        <?php echo $menu_item['menu_title'] ?>

                        <?php if (!empty($menu_item['notification'])) {?>
                            <span class="elfsight-admin-menu-list-item-notification"></span>

                            <span class="elfsight-admin-tooltip-content">
                                <span class="elfsight-admin-tooltip-content-inner">
                                    <?php echo $menu_item['notification']; ?>
                                </span>
                            </span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
</nav>   