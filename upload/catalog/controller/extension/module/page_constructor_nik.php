<?php
class ControllerExtensionModulePageConstructorNik extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/page_constructor_nik');

		$this->load->model('extension/module/page_constructor_nik');
		$this->load->model('tool/image');

		$data = array();

		if (isset($setting['module_id'])) {
		    $page_constructor_info = $this->model_extension_module_page_constructor_nik->getPageConstructorByModuleId($setting['module_id']);
            if (isset($page_constructor_info['page_constructor_id'])) {
                $blocks_info  = $this->model_extension_module_page_constructor_nik->getBlocks($page_constructor_info['page_constructor_id']);

                foreach ($blocks_info as $k => $block) {
                    $blocks_info[$k]['background_image'] = $this->model_tool_image->resize($block['bg_image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));;
                    $block_data_info = $this->model_extension_module_page_constructor_nik->getBlockData($block['id']);
                    foreach ($block_data_info as $kk => $block_data) {
                        $contents = array();
                        if (!empty($block_data['text'])) {
                            $contents[] = array(
                                'value'   => $block_data['text'],
                                'sort'    => $block_data['text_ordinal'],
                                'type'    => 'text'
                            );
                        }

                        if ($block_data['bg_image']) {
                            $block_data_info[$kk]['thumb'] = $this->model_tool_image->resize($block_data['bg_image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                        } else {
                            $block_data_info[$kk]['thumb'] = '';
                        }

                        $sort_order = array();

                        foreach ($contents as $key => $value) {
                            $sort_order[$key] = $value['sort'];
                        }

                        array_multisort($sort_order, SORT_ASC, $contents);

                        $block_data_info[$kk]['contents'] = $contents;
                    }

                    $blocks_info[$k]['blocks_data'] = $block_data_info;
                }

                $data['blocks'] = $blocks_info;
            }
        }

        return $this->load->view('extension/module/page_constructor_nik', $data);
	}
}