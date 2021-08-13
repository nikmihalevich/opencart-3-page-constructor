<?php
class ModelExtensionModulePageConstructorNik extends Model {
    public function getPageConstructorByModuleId($module_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor WHERE module_id = '" . (int)$module_id . "'");

        return $query->row;
    }

    public function getBlocks($page_constructor_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor_blocks WHERE `page_constructor_id` = '" . (int)$page_constructor_id . "' AND `status` = '1' ORDER BY `sort_ordinal` ASC");

        return $query->rows;
    }

    public function getBlockData($block_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_constructor_blocks_data WHERE `block_id` = '" . (int)$block_id . "'");

        return $query->rows;
    }

	public function getOrder($order_id) {
		
		$query = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");
		
		return $query->row['order_status_id'];
		
	}
}