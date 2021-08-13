<?php
class ControllerExtensionModulePageConstructorNik extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/page_constructor_nik');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		$this->load->model('extension/module/page_constructor_nik');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('page_constructor_nik', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$this->getList();
	}

	public function createTemplate() {
        $this->load->language('extension/module/page_constructor_nik');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');
        $this->load->model('extension/module/page_constructor_nik');

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $page_constructor = $this->model_extension_module_page_constructor_nik->getPageConstructorByModuleId($this->request->get['module_id']);
            if (empty($page_constructor)) {
                $this->model_extension_module_page_constructor_nik->add($this->request->get['module_id']);

                $this->session->data['success'] = $this->language->get('text_success');

                $this->response->redirect($this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true));
            }
        }

        $this->getList();
    }

	protected function getList() {
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['width'])) {
            $data['error_width'] = $this->error['width'];
        } else {
            $data['error_width'] = '';
        }

        if (isset($this->error['height'])) {
            $data['error_height'] = $this->error['height'];
        } else {
            $data['error_height'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
            $data['page_constructor'] = $this->model_extension_module_page_constructor_nik->getPageConstructorByModuleId($this->request->get['module_id']);
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'], true);
        } else if (isset($this->request->get['module_id']) && empty($data['page_constructor'])) {
            $data['action'] = $this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
            $data['actionTemplate'] = $this->url->link('extension/module/page_constructor_nik/createTemplate', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
            $data['module_id'] = $this->request->get['module_id'];
        } else {
            $data['action'] = $this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
            $data['actionTemplate'] = $this->url->link('extension/module/page_constructor_nik', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
            $data['module_id'] = $this->request->get['module_id'];
        }

        if (!empty($data['page_constructor']) && isset($data['page_constructor']['page_constructor_id'])) {
            $blocks_info  = $this->model_extension_module_page_constructor_nik->getBlocks($data['page_constructor']['page_constructor_id']);

            $this->load->model('tool/image');
            foreach ($blocks_info as $k => $block) {
                $blocks_info[$k]['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block['bg_image'];
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

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $this->load->model('tool/image');
        $data['img_placeholder'] = $this->model_tool_image->resize('no_image.png', 40, 40);
        $data['img_min_placeholder'] = $this->model_tool_image->resize('no_image.png', 20, 20);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/page_constructor_nik', $data));
    }

    public function addBlock() {
        if(isset($this->request->get['page_constructor_id']) && isset($this->request->get['grid_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/page_constructor_nik');
            $block_id = $this->model_extension_module_page_constructor_nik->addBlock($this->request->get['page_constructor_id'], $this->request->get['grid_id']);
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block_id));
        }
    }

    public function editBlock() {
        if(isset($this->request->get['block_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/page_constructor_nik');
            $formData = $_POST['block'];

            $this->model_extension_module_page_constructor_nik->editBlock($this->request->get['block_id'], $formData);
        }
    }

    public function deleteBlock() {
        if(isset($this->request->get['block_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/page_constructor_nik');

            $this->model_extension_module_page_constructor_nik->deleteBlock($this->request->get['block_id']);
        }
    }

    public function getBlock() {
        if(isset($this->request->get['block_id']) && $this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model('extension/module/page_constructor_nik');

            $block = $this->model_extension_module_page_constructor_nik->getBlock($this->request->get['block_id']);

            $block['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block['bg_image'];

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block));
        }
    }

    public function addBlockData() {
        if(isset($this->request->get['block_id']) && isset($this->request->get['col_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/page_constructor_nik');
            // end this
            $formData = $_POST;
            $formData['block_id'] = $this->request->get['block_id'];
            $formData['col_id'] = $this->request->get['col_id'];
            $block = $this->model_extension_module_page_constructor_nik->getBlock($this->request->get['block_id']);
            $block_grid_width = '';
            switch ($block['grid_id']) {
                case '1':
                    $block_grid_width = '12';
                    break;
                case '2':
                    $block_grid_width = '6';
                    break;
                case '3':
                    $block_grid_width = '4';
                    break;
                case '4':
                    $block_grid_width = '3';
                    break;
                case '5':
                    if($formData['col_id'] == '1') {
                        $block_grid_width = '4';
                    } else {
                        $block_grid_width = '8';
                    }
                    break;
                case '6':
                    if($formData['col_id'] == '1') {
                        $block_grid_width = '8';
                    } else {
                        $block_grid_width = '4';
                    }
                    break;
                case '7':
                    if($formData['col_id'] == '3') {
                        $block_grid_width = '6';
                    } else {
                        $block_grid_width = '3';
                    }
                    break;
                case '8':
                    if($formData['col_id'] == '1') {
                        $block_grid_width = '6';
                    } else {
                        $block_grid_width = '3';
                    }
                    break;
                case '9':
                    if($formData['col_id'] == '2') {
                        $block_grid_width = '6';
                    } else {
                        $block_grid_width = '3';
                    }
                    break;
                case '10':
                    $block_grid_width = '2';
                    break;
                case '11':
                    $block_grid_width = '1';
                    break;
                case '12':
                    if($formData['col_id'] == '2') {
                        $block_grid_width = '10';
                    } else {
                        $block_grid_width = '1';
                    }
                    break;

                default:
                    break;
            }
            $formData['block_grid_width'] = $block_grid_width;
            $block_data_id = $this->model_extension_module_page_constructor_nik->addBlockData($formData);
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block_data_id));
        }
    }

    public function editBlockData() {
        if(isset($this->request->get['block_data_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/page_constructor_nik');
            $formData = $_POST;
            $formData['block_data_id'] = $this->request->get['block_data_id'];
            $this->model_extension_module_page_constructor_nik->editBlockData($formData);
        }
    }

    public function deleteBlockData() {
        if(isset($this->request->get['block_data_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/page_constructor_nik');

            $this->model_extension_module_page_constructor_nik->deleteBlockData($this->request->get['block_data_id']);
        }
    }

    public function getBlockData() {
        if(isset($this->request->get['block_data_id']) && $this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model('extension/module/page_constructor_nik');

            $block_data = $this->model_extension_module_page_constructor_nik->getBlockDataByBlockDataId($this->request->get['block_data_id']);

            $block_data['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block_data['bg_image'];

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block_data));
        }
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/module/page_constructor_nik')) {
            $this->load->model('extension/module/page_constructor_nik');

            $this->model_extension_module_page_constructor_nik->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/module/page_constructor_nik')) {
            $this->load->model('extension/module/page_constructor_nik');

            $this->model_extension_module_page_constructor_nik->uninstall();
        }
    }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/page_constructor_nik')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
