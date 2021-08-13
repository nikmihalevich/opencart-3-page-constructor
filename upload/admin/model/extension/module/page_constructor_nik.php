<?php
class ModelExtensionModulePageConstructorNik extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "page_constructor` (
			`page_constructor_id` INT(11) NOT NULL AUTO_INCREMENT,
			`module_id` INT(11) NOT NULL,
			`date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (`page_constructor_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "page_constructor_blocks` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `page_constructor_id` INT(11) NOT NULL,
            `grid_id` INT(11) NOT NULL,
            `bg_color` VARCHAR(10) DEFAULT NULL,
            `bg_image` VARCHAR(255) DEFAULT NULL,
            `width` INT(11) DEFAULT NULL,
            `width_type` VARCHAR(10) DEFAULT NULL,
            `padding` VARCHAR(20) DEFAULT NULL,
            `sort_ordinal` INT(11) DEFAULT NULL,
            `class` VARCHAR(50) DEFAULT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "page_constructor_blocks_data` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `block_id` INT(11) NOT NULL,
            `col_id` INT(11) NOT NULL,
            `block_grid_width` VARCHAR(20) DEFAULT NULL,
            `text` TEXT DEFAULT NULL,
            `text_ordinal` INT(11) DEFAULT NULL,
            `bg_color` VARCHAR(10) DEFAULT NULL,
            `bg_image` VARCHAR(255) DEFAULT NULL,
            `width` INT(11) DEFAULT NULL,
            `width_type` VARCHAR(10) NOT NULL,
            `padding` VARCHAR(20) DEFAULT NULL,
            `block_grid_width_tablet` VARCHAR(20) DEFAULT NULL,
            `block_grid_width_mobile` VARCHAR(20) DEFAULT NULL,
            `class` VARCHAR(50) DEFAULT NULL,
            PRIMARY KEY (`id`) 
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "page_constructor`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "page_constructor_blocks`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "page_constructor_blocks_data`");
    }

    public function add($module_id) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "page_constructor SET `module_id` = '" . (int)$module_id . "', date_added = NOW()");

        $page_constructor_id = $this->db->getLastId();

        $this->cache->delete('page_constructor');

        return $page_constructor_id;
    }

//    public function edit($page_constructor_id, $data) {
//        $this->db->query("UPDATE " . DB_PREFIX . "page_constructor SET `mailing_category_id` = '" . (int)$data['mailing_category_id'] . "', `name` = '" . $this->db->escape($data['template_name']) . "', counter_letters = '" . $this->db->escape($data['count_letters']) . "', date_start = '" . $this->db->escape($data['date_automailing']) . "', `repeat` = '" . (int)$data['repeat'] . "' WHERE mailing_id = '" . (int)$mailing_id . "'");
//
//        $this->db->query("DELETE FROM " . DB_PREFIX . "mailing_description WHERE mailing_id = '" . (int)$mailing_id . "'");
//        $this->db->query("INSERT INTO " . DB_PREFIX . "mailing_description SET mailing_id = '" . (int)$mailing_id . "', language_id = '" . (int)1 . "', theme = '" . $this->db->escape($data['letter_theme']) . "'");
//
//        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_mailing WHERE mailing_id = '" . (int)$mailing_id . "'");
//        if(isset($data['template_categories'])) {
//            foreach ($data['template_categories'] as $category_id) {
//                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_mailing SET mailing_id = '" . (int)$mailing_id . "', category_id = '" . (int)$category_id . "'");
//            }
//        }
//
//        $this->cache->delete('mailing');
//    }

    public function delete($page_constructor_id) {
        $blocks = $this->getBlocks($page_constructor_id);
        foreach ($blocks as $block) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "page_constructor_blocks_data WHERE block_id = '" . (int)$block['id'] . "'");
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "page_constructor WHERE page_constructor_id = '" . (int)$page_constructor_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "page_constructor_blocks WHERE page_constructor_id = '" . (int)$page_constructor_id . "'");

        $this->cache->delete('page_constructor');
    }

    public function addBlock($page_constructor_id, $grid_id) {
        $query = $this->db->query("SELECT MAX(`sort_ordinal`) as max_ordinal FROM " . DB_PREFIX . "page_constructor_blocks WHERE `page_constructor_id` = '" . (int)$page_constructor_id . "'");
        $max_ordinal = (int)$query->row['max_ordinal'] + 1;

        $this->db->query("INSERT INTO " . DB_PREFIX . "page_constructor_blocks SET `page_constructor_id` = '" . (int)$page_constructor_id . "', `grid_id` = '" . (int)$grid_id . "', `sort_ordinal` = '" . (int)$max_ordinal . "'");

        $block_id = $this->db->getLastId();

        $this->cache->delete('page_constructor_blocks');

        return $block_id;
    }

    public function editBlock($block_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "page_constructor_blocks SET `bg_color` = '" . $this->db->escape($data['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['bg_image']) . "', `width` = '" . (int)$data['width'] . "', `width_type` = '" . $this->db->escape($data['width_type']) . "', `padding` = '" . $this->db->escape($data['padding']) . "', `sort_ordinal` = '" . (int)$data['sort_ordinal'] . "', `class` = '" . $this->db->escape($data['class']) . "', `status` = '" . (int)$data['status'] . "' WHERE `id` = '" . (int)$block_id . "'");

        $this->cache->delete('page_constructor_blocks');
    }

    public function deleteBlock($block_id) {
        $blocks_data = $this->getBlockData($block_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "page_constructor_blocks WHERE `id` = '" . (int)$block_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "page_constructor_blocks_data WHERE `block_id` = '" . (int)$block_id . "'");

        $this->cache->delete('page_constructor_blocks');
        $this->cache->delete('page_constructor_blocks_data');
    }

    public function getBlocks($page_constructor_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor_blocks WHERE `page_constructor_id` = '" . (int)$page_constructor_id . "' ORDER BY `sort_ordinal` ASC");

        return $query->rows;
    }

    public function getBlock($block_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor_blocks WHERE `id` = '" . (int)$block_id . "'");

        return $query->row;
    }

    public function addBlockData($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "page_constructor_blocks_data SET `block_id` = '" . (int)$data['block_id'] . "', `col_id` = '" . (int)$data['col_id'] . "', `block_grid_width` = '" . $this->db->escape($data['block_grid_width']) . "', `text` = '" . $this->db->escape($data['block_data']['text']) . "', `text_ordinal` = '" . (int)$data['block_data']['text_ordinal'] . "', `bg_color` = '" . $this->db->escape($data['block_data']['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['block_data']['bg_image']) . "', `width` = '" . (int)$data['block_data']['width'] . "', `width_type` = '" . $this->db->escape($data['block_data']['width_type']) . "', `padding` = '" . $this->db->escape($data['block_data']['padding']) . "', `block_grid_width_tablet` = '" . $this->db->escape($data['block_data']['block_grid_width_tablet']) . "', `block_grid_width_mobile` = '" . $this->db->escape($data['block_data']['block_grid_width_mobile']) . "', `class` = '" . $this->db->escape($data['block_data']['class']) . "'");

        $block_data_id = $this->db->getLastId();

        $this->cache->delete('page_constructor_blocks_data');

        return $block_data_id;
    }

    public function editBlockData($data) {
        $this->db->query("UPDATE " . DB_PREFIX . "page_constructor_blocks_data SET `text` = '" . $this->db->escape($data['block_data']['text']) . "', `text_ordinal` = '" . (int)$data['block_data']['text_ordinal'] . "', `bg_color` = '" . $this->db->escape($data['block_data']['bg_color']) . "', `bg_image` = '" . $this->db->escape($data['block_data']['bg_image']) . "', `width` = '" . (int)$data['block_data']['width'] . "', `width_type` = '" . $this->db->escape($data['block_data']['width_type']) . "', `padding` = '" . $this->db->escape($data['block_data']['padding']) . "', `block_grid_width_tablet` = '" . $this->db->escape($data['block_data']['block_grid_width_tablet']) . "', `block_grid_width_mobile` = '" . $this->db->escape($data['block_data']['block_grid_width_mobile']) . "', `class` = '" . $this->db->escape($data['block_data']['class']) . "' WHERE `id` = '" . (int)$data['block_data_id'] . "'");

        $this->cache->delete('page_constructor_blocks_data');
    }

    public function deleteBlockData($block_data_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "page_constructor_blocks_data WHERE `id` = '" . (int)$block_data_id . "'");

        $this->cache->delete('page_constructor_blocks_data');
    }

    public function getBlockData($block_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor_blocks_data WHERE `block_id` = '" . (int)$block_id . "'");

        return $query->rows;
    }

    public function getBlockDataByBlockDataId($block_data_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor_blocks_data WHERE `id` = '" . (int)$block_data_id . "'");

        return $query->row;
    }

    public function getPageConstructor($page_constructor_id) {
        $page_constructor_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor WHERE page_constructor_id = '" . (int)$page_constructor_id . "'");

        foreach ($query->rows as $result) {
            $page_constructor_data = array(
                'page_constructor_id' => $result['page_constructor_id'],
                'module_id'           => $result['module_id'],
                'date_added'          => $result['date_added']
            );
        }

        return $page_constructor_data;
    }

    public function getPageConstructorByModuleId($module_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor WHERE module_id = '" . (int)$module_id . "'");

        return $query->row;
    }
}