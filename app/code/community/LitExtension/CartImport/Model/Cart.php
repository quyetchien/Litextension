<?php
/**
 * @project: CartImport
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */
class LitExtension_CartImport_Model_Cart{

    const DEMO_MODE = false;
    const TABLE_IMPORT      = 'lecaip/import';
    const TABLE_USER        = 'lecaip/user';
    const FOLDER_SUFFIX     = '/litextension/cartimport/';
    const TYPE_TAX          = 'tax';
    const TYPE_TAX_CUSTOMER = 'tax_customer';
    const TYPE_TAX_PRODUCT  = 'tax_product';
    const TYPE_TAX_RATE     = 'tax_rate';
    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_MAN_ATTR     = 'man_attr';
    const TYPE_CATEGORY     = 'category';
    const TYPE_PRODUCT      = 'product';
    const TYPE_ATTR         = 'attribute';
    const TYPE_ATTR_OPTION  = 'attribute_option';
    const TYPE_CUSTOMER     = 'customer';
    const TYPE_ORDER        = 'order';
    const TYPE_REVIEW       = 'review';
    const MANUFACTURER_CODE = 'manufacturer';

    protected $_resource = null;
    protected $_write = null;
    protected $_read = null;
    protected $_notice = null;
    protected $_cart_url = null;
    protected $_folder = null;
    protected $_custom = null;
    protected $_process = null;
    protected $_seo = null;

    protected $_demo_limit = array(
        'taxes' => 10,
        'manufacturers' => 10,
        'categories' => 10,
        'products' => 10,
        'customers' => 10,
        'orders' => 10,
        'reviews' => 0
    );

    public function __construct(){
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_write = $this->_resource->getConnection('core_write');
        $this->_read = $this->_resource->getConnection('core_read');
        $this->_process = Mage::getModel('lecaip/process');
    }

    /**
     * TODO : Router to model process import
     */

    /**
     * Router to model process migration
     *
     * @param string $cart_type
     * @return string
     */
    public function getCart($cart_type){
        if(!$cart_type){
            return 'cart';
        }
        if($cart_type == 'volusion'){
            return 'cart_volusion';
        }
        if($cart_type == 'mivamerchant'){
            return 'cart_mivamerchant';
        }
        if ($cart_type == 'amazonstore'){
            return 'cart_amazonstore';
        }        
        if ($cart_type == 'yahoostore'){
            return 'cart_yahoostore';
        }
        if ($cart_type == 'nopcommerce'){
            return 'cart_nopcommerce';
        }
        if ($cart_type == 'squarespace'){
            return 'cart_squarespace';
        }
        if ($cart_type == 'weebly'){
            return 'cart_weebly';
        }
        return 'cart';
    }

    /**
     * TODO : Work with notice
     */

    /**
     * Set notice use for migration in model
     */
    public function setNotice($notice, $custom = true){
        $this->_notice = $notice;
        $this->_cart_url = $notice['config']['cart_url'];
        $this->_folder = $notice['config']['folder'];
        if($custom){
            $this->_custom = Mage::getModel('lecaip/custom');
        }
    }

    /**
     * Get notice of migration after config or process
     */
    public function getNotice(){
        return $this->_notice;
    }

    /**
     * Default construct of notice migration use for pass php notice warning
     */
    public function getDefaultNotice(){
        return array(
            'config' => array(
                'cart_type' => '',
                'cart_url' => '',
                'merchantID' => '',
                'folder' => '',
                'files' => array(),
                'file_data' => array(),
                'upload_success' => true,
                'cats' => array(),
                'category_data' => array(),
                'root_category_id' => '',
                'attributes' => array(),
                'attribute_data' => array(),
                'attribute_set_id' => '',
                'languages' => array(),
                'languages_data' => array(),
                'currencies' => array(),
                'currencies_data' => array(),
                'order_status' => array(),
                'order_status_data' => array(),
                'countries' => array(),
                'countries_data' => array(),
                'default_lang' => '',
                'default_currency' => '',
                'website_id' => '',
                'config_support' => array(
                    'category_map' => true,
                    'attribute_map' => true,
                    'language_map' => true,
                    'order_status_map' => true,
                    'currency_map' => true,
                    'country_map' => true
                ),
                'import_support' => array(
                    'taxes' => true,
                    'manufacturers' => true,
                    'categories' => true,
                    'products' => true,
                    'customers' => true,
                    'orders' => true,
                    'reviews' => true
                ),
                'import' => array(
                    'taxes' => false,
                    'manufacturers' => false,
                    'categories' => false,
                    'products' => false,
                    'customers' => false,
                    'orders' => false,
                    'reviews' => false
                ),
                'add_option' => array(
                    'clear_data' => false,
                    'img_des' => false,
                    'pre_cus' => false,
                    'pre_ord' => false,
                    'stock' => false,
                    'seo_url' => false,
                    'seo_plugin' => ''
                ),
                'limit' => 0
            ),
            'clear_info' => array(
                'result' => 'process',
                'function' => '_clearProducts',
                'msg' => '',
                'limit' => 20
            ),
            'csv_import' => array(
                'result' => 'process',
                'function' => '',
                'msg' => $this->consoleSuccess("Preparing storage data ..."),
                'count' => 0
            ),
            'taxes' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'manufacturers' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'categories' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'products' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'customers' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'orders' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'reviews' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'setting' => Mage::getStoreConfig('lecaip/general'),
            'is_running' => false,
            'fn_resume' => 'clearStore',
            'msg_start' => '',
            'extend' => array()
        );
    }

    /**
     * Save notice to database with admin id
     *
     * @param int $user_id
     * @param array $notice
     * @return boolean
     */
    public function saveUserNotice($user_id, $notice){
        if(!$user_id){
            return false;
        }
        $exists = $this->selectTable(self::TABLE_USER, array('user_id' => $user_id));
        if($exists){
            return $this->updateTable(self::TABLE_USER, array('notice' => serialize($notice)), array('user_id' => $user_id));
        } else {
            return $this->insertTable(self::TABLE_USER, array(
                'user_id' => $user_id,
                'notice' => serialize($notice)
            ));
        }
    }

    /**
     * Get notice of import in database with admin id
     * @param int $user_id
     * @return array
     */
    public function getUserNotice($user_id){
        if(!$user_id){
            return false;
        }
        $notice = false;
        $result = $this->selectTableRow(self::TABLE_USER, array('user_id' => $user_id));
        if($result){
            $notice = (isset($result['notice'])) ? unserialize($result['notice']) : false;
        }
        return $notice;
    }

    /**
     * Delete notice of import with admin id
     *
     * @param int $user_id
     * @return boolean
     */
    public function deleteUserNotice($user_id){
        if(!$user_id){
            return true;
        }
        return $this->deleteTable(self::TABLE_USER, array('user_id' => $user_id));
    }

    /**
     * TODO : import data
     */

    /**
     * Import data from csv to database
     */
    protected function _storageCsvByType($type, $next, $success = false, $finish = false, $unset = array()){
        if(!$success){
            $success = $next;
        }
        if(!$this->_notice['config']['files'][$type]){
            if($finish){
                $this->_notice['csv_import']['result'] = 'success';
            } else {
                $this->_notice['csv_import']['result'] = 'process';
            }
            $this->_notice['csv_import']['function'] = '_storageCsv' . ucfirst($next);
            $this->_notice['csv_import']['msg'] = '';
            $this->_notice['csv_import']['count'] = 0;
            return $this->_notice['csv_import'];
        }
        $start = $this->_notice['csv_import']['count'];
        $demo = $this->_limitDemoModel($type);
        $csv_file = Mage::getBaseDir('media'). self::FOLDER_SUFFIX . $this->_notice['config']['folder'] . '/' . $type . '.csv';
        $readCsv = $this->readCsv($csv_file, $start, $this->_notice['setting']['csv'], $demo);
        if($readCsv['result'] != 'success'){
            $readCsv['msg'] = $this->consoleError($readCsv['msg']);
            return $readCsv;
        }
        $allowData = array();
        $fn_construct = $type . 'TableConstruct';
        $table = $this->$fn_construct();
        if(LitExtension_CartImport_Model_Custom::CSV_IMPORT){
            $allowData = $this->_custom->storageCsvCustom($this);
        }
        $validation = false;
        if(!$allowData){
            $rows = $table['rows'];
            $validation = isset($table['validation']) ? $table['validation'] : false;
            if($unset){
                $rows = $this->unsetListArray($unset, $rows);
            }
            $allowData = array_keys($rows);
            $custom_allow = $this->_custom->storageCsvCustom($this);
            if($custom_allow){
                $allowData = array_merge($allowData, $custom_allow);
            }
        }
        foreach($readCsv['data'] as $item){
            $data = $this->syncCsvTitleRow($item['title'], $item['row']);
            if(!empty($data)){
                if($validation){
                    foreach($validation as $column_name){
                        if(!isset($data[$column_name]) || !$data[$column_name]){
                            continue 2;
                        }
                    }
                }
                $data = $this->addConfigToArray($data);             
                $insert = $this->insertTable($table['table'], $data, $allowData);
                if(!$insert){
                    return array(
                        'result' => 'error',
                        'msg' => $this->consoleError('Could not import csv to database.')
                    );
                }
            }
        }
        if($readCsv['finish']){
            if($finish){
                $this->_notice['csv_import']['result'] = 'success';
            } else {
                $this->_notice['csv_import']['result'] = 'process';
            }
            $this->_notice['csv_import']['function'] = '_storageCsv' . ucfirst($success);
            $this->_notice['csv_import']['msg'] = $this->consoleSuccess("Finish importing " . $type);
            $this->_notice['csv_import']['count'] = 0;
            return $this->_notice['csv_import'];
        }
        $this->_notice['csv_import']['result'] = 'process';
        $this->_notice['csv_import']['function'] = '_storageCsv' . ucfirst($type);
        $this->_notice['csv_import']['msg'] = '';
        $this->_notice['csv_import']['count'] = $readCsv['count'];
        return $this->_notice['csv_import'];
    }

    /**
     * Process and get data use for config display
     *
     * @return array : Response as success or error with msg
     */
    public function displayConfig(){
        $license = trim(Mage::getStoreConfig('lecaip/general/license'));
        if(!$license){
            return array(
                'result' => 'error',
                'msg' => $this->consoleError('Please enter License Key (in Configuration)')
            );
        }
        $check_license = $this->request(
            chr(104).chr(116).chr(116).chr(112).chr(58).chr(47).chr(47).chr(108).chr(105).chr(116).chr(101).chr(120).chr(116).chr(101).chr(110).chr(115).chr(105).chr(111).chr(110).chr(46).chr(99).chr(111).chr(109).chr(47).chr(108).chr(105).chr(99).chr(101).chr(110).chr(115).chr(101).chr(46).chr(112).chr(104).chr(112),
            Zend_Http_Client::GET,
            array(
                'user' => "bGl0ZXg=",
                'pass' => "YUExMjM0NTY=",
                'action' => "Y2hlY2s=",
                'license' => base64_encode($license),
                'cart_type' => base64_encode($this->_notice['config']['cart_type']),
                'url' => base64_encode($this->_cart_url),
                'target_type' => base64_encode('magento1'),
            )
        );
        if(!$check_license){
            return array(
                'result' => 'error',
                'msg' => $this->consoleError('Could not get your license info, please check network connection.')
            );
        }
        $check_license = unserialize(base64_decode($check_license));
        if($check_license['result'] != 'success'){
            return array(
                'result' => $check_license['result'],
                'msg' => $this->consoleError($check_license['msg'])
            );
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Save config of use in config step to notice
     */
    public function displayConfirm($params){
        $configs = array('cats', 'attributes', 'languages', 'currencies', 'order_status', 'countries');
        foreach($configs as $config){
            $this->_notice['config'][$config] = isset($params[$config]) ? $params[$config] : array();
        }
        $imports = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews');
        foreach ($imports as $import) {
            if (isset($params[$import]) && $params[$import]) {
                $this->_notice['config']['import'][$import] = true;
            } else {
                $this->_notice['config']['import'][$import] = false;
            }
        }
        $addOption = array('add_new', 'clear_data', 'img_des', 'pre_cus', 'pre_ord', 'stock', 'seo_url');
        foreach ($addOption as $add_opt) {
            if (isset($params[$add_opt]) && $params[$add_opt]) {
                $this->_notice['config']['add_option'][$add_opt] = true;
            } else {
                $this->_notice['config']['add_option'][$add_opt] = false;
            }
        }
        $this->_notice['config']['add_option']['add_new'] = !LitExtension_CartImport_Model_Custom::CLEAR_IMPORT;
        if(isset($params['seo_plugin']) && $params['seo_plugin']){
            $this->_notice['config']['add_option']['seo_plugin'] = $params['seo_plugin'];
        }
        $this->_notice['config']['languages'] = $this->filterArrayValueFalse($this->_notice['config']['languages']);
        $categories = array_values($this->_notice['config']['cats']);
        $this->_notice['config']['root_category_id'] = isset($categories[0])? $categories[0] : null;
        $attributes = array_values($this->_notice['config']['attributes']);
        $this->_notice['config']['attribute_set_id'] = isset($attributes[0])? $attributes[0] : null;
        if(isset($this->_notice['config']['languages']) && $this->_notice['config']['languages']){
            $store_default = isset($this->_notice['config']['languages'][$this->_notice['config']['default_lang']]) ? $this->_notice['config']['languages'][$this->_notice['config']['default_lang']] : false;
            if($store_default){
                $this->_notice['config']['website_id'] = $this->getWebsiteIdByStoreId($store_default);
            } else {
                $this->_notice['config']['website_id'] = 0;
            }
        } else {
            $this->_notice['config']['website_id'] = 0;
        }
        return $this;
    }

    /**
     * Clear data of store
     */
    public function clearStore(){
        if(!$this->_notice['config']['add_option']['clear_data']){
            if(!$this->_notice['config']['add_option']['add_new']){
                $del = $this->deleteTable(self::TABLE_IMPORT, array('folder' => $this->_folder));
                if(!$del){
                    return $this->errorDatabase(true);
                }
            }
            return array(
                'result' => 'no-clear'
            );
        }
        $clear = $this->_process->clearStore($this);
        $this->_notice['clear_info']['result'] = $clear['result'];
        $this->_notice['clear_info']['function'] = isset($clear['function']) ? $clear['function'] : '';
        if($clear['result'] == 'success'){
            $entity = array();
            foreach($this->_notice['config']['import'] as $type => $value){
                if($value){
                    $entity[] = ucfirst(($type));
                }
            }
            $msg = "Current " . implode(', ', $entity) . " cleared!";
            $clear['msg'] = $this->consoleSuccess($msg);
            $clear['msg'] .= $this->getMsgStartImport('taxes');
            if(!$this->_notice['config']['add_option']['add_new']){
                $del = $this->deleteTable(self::TABLE_IMPORT, array('folder' => $this->_folder));
                if(!$del){
                    return $this->errorDatabase(true);
                }
            }
        }
        return $clear;
    }

    /**
     * Config currency
     */
    public function configCurrency(){
        return array(
            'result' => 'success'
        );
    }

    /**
     * Process before import taxes
     */
    public function prepareImportTaxes(){
        $this->_custom->prepareImportTaxesCustom($this);
    }

    /**
     * Check tax has imported
     *
     * @param array $tax : One row of function getTaxes
     * @return boolean
     */
    public function checkTaxImport($tax){
        $id_src = $this->getTaxId($tax);
        return $this->getIdDescTax($id_src);
    }

    /**
     * Import tax with data convert of function convertTax
     *
     * @param array $data : Data of function convertTax
     * @param array $tax : One row of function getTaxes
     * @return array
     */
    public function importTax($data, $tax){
        if(LitExtension_CartImport_Model_Custom::TAX_IMPORT){
            return $this->_custom->importTaxCustom($this, $data, $tax);
        }
        $id_src = $this->getTaxId($tax);
        $taxIpt = $this->_process->taxRule($data);
        if($taxIpt['result'] == 'success'){
            $id_desc = $taxIpt['mage_id'];
            $this->taxSuccess($id_src, $id_desc);
        } else {
            $taxIpt['result'] = 'warning';
            $msg = "Tax Id = {$id_src} import failed. Error: " . $taxIpt['msg'];
            $taxIpt['msg'] = $this->consoleWarning($msg);
        }
        return $taxIpt;
    }

    /**
     * Process after import success one row of tax main
     *
     * @param int $tax_mage_id : Id of tax import to magento
     * @param array $data : Data of function convertTax
     * @param array $tax : One row of function getTaxes
     * @return boolean
     */
    public function afterSaveTax($tax_mage_id, $data, $tax){
        $this->_custom->afterSaveTaxCustom($this, $tax_mage_id, $data, $tax);
        return LitExtension_CartImport_Model_Custom::TAX_AFTER_SAVE;
    }

    /**
     * Process before import manufacturers
     */
    public function prepareImportManufacturers(){
        $this->_custom->prepareImportManufacturersCustom($this);
    }

    /**
     * Check manufacturer has been imported
     *
     * @param array $manufacturer : One row of object in function getManufacturers
     * @return boolean
     */
    public function checkManufacturerImport($manufacturer){
        $id_src = $this->getManufacturerId($manufacturer);
        return $this->getIdDescManufacturer($id_src);
    }

    /**
     * Import manufacturer with data of function convertManufacturer
     *
     * @param array $data : Data of function convertManufacturer
     * @param array $manufacturer : One row of object in function getManufacturers
     * @return array
     */
    public function importManufacturer($data, $manufacturer){
        if(LitExtension_CartImport_Model_Custom::MANUFACTURER_IMPORT){
            return $this->_custom->importManufacturerCustom($this, $data, $manufacturer);
        }
        $id_src = $this->getManufacturerId($manufacturer);
        $manufacturerIpt = $this->_process->manufacturer($data);
        if($manufacturerIpt['result'] == 'success'){
            $id_desc = $manufacturerIpt['mage_id'];
            $this->manufacturerSuccess($id_src, $id_desc);
        } else {
            $manufacturerIpt['result'] = 'warning';
            $msg = "Manufacturer Id = {$id_src} import failed. Error: " . $manufacturerIpt['msg'];
            $manufacturerIpt['msg'] = $this->consoleWarning($msg);
        }
        return $manufacturerIpt;
    }

    /**
     * Process after one manufacturer import successful
     *
     * @param int $manufacturer_mage_id : Id of manufacturer import success to magento
     * @param array $data : Data of function convertManufacturer
     * @param array $manufacturer : One row of object in function getManufacturers
     * @return boolean
     */
    public function afterSaveManufacturer($manufacturer_mage_id, $data, $manufacturer){
        $this->_custom->afterSaveManufacturerCustom($this, $manufacturer_mage_id, $data, $manufacturer);
        return LitExtension_CartImport_Model_Custom::MANUFACTURER_AFTER_SAVE;
    }

    /**
     * Process before import categories
     */
    public function prepareImportCategories(){
        $this->_custom->prepareImportCategoriesCustom($this);
        $this->_process->stopIndexes();
    }

    /**
     * Check category has been imported
     *
     * @param array $category : One row of object in function getCategories
     * @return boolean
     */
    public function checkCategoryImport($category){
        $id_src = $this->getCategoryId($category);
        return $this->getIdDescCategory($id_src);
    }

    /**
     * Import category with data convert in function convertCategory
     *
     * @param array $data : Data of function convertCategory
     * @param array $category : One row of object in function getCategories
     * @return array
     */
    public function importCategory($data, $category){
        if(LitExtension_CartImport_Model_Custom::CATEGORY_IMPORT){
            return $this->_custom->importCategoryCustom($this, $data, $category);
        }
        $id_src = $this->getCategoryId($category);
        $categoryIpt = $this->_process->category($data);
        if($categoryIpt['result'] == 'success'){
            $id_desc = $categoryIpt['mage_id'];
            $this->categorySuccess($id_src, $id_desc);
        } else {
            $categoryIpt['result'] = 'warning';
            $msg = "Category Id = {$id_src} import failed. Error: " . $categoryIpt['msg'];
            $categoryIpt['msg'] = $this->consoleWarning($msg);
        }
        return $categoryIpt;
    }

    /**
     * Process after one category import successful
     *
     * @param int $category_mage_id : Id of category import successful to magento
     * @param array $data : Data of function convertCategory
     * @param array $category : One row of object in function getCategories
     * @return boolean
     */
    public function afterSaveCategory($category_mage_id, $data, $category){
        $this->_custom->afterSaveCategoryCustom($this, $category_mage_id, $data, $category);
        return LitExtension_CartImport_Model_Custom::CATEGORY_AFTER_SAVE;
    }

    /**
     * Process before import products
     */
    public function prepareImportProducts(){
        $this->_custom->prepareImportProductsCustom($this);
        $this->_process->stopIndexes();
    }

    /**
     * Check product has been imported
     *
     * @param array $product : One row of object in function getProducts
     * @return boolean
     */
    public function checkProductImport($product){
        $id_src = $this->getProductId($product);
        return $this->getIdDescProduct($id_src);
    }

    /**
     * Import product with data convert in function convertProduct
     *
     * @param array $data : Data of function convertProduct
     * @param array $product : One row of object in function getProducts
     * @return array
     */
    public function importProduct($data, $product){
        if(LitExtension_CartImport_Model_Custom::PRODUCT_IMPORT){
            return $this->_custom->importProductCustom($this, $data, $product);
        }
        $id_src = $this->getProductId($product);
        $productIpt = $this->_process->product($data);
        if($productIpt['result'] == 'success'){
            $id_desc = $productIpt['mage_id'];
            $this->productSuccess($id_src, $id_desc);
        } else {
            $productIpt['result'] = 'warning';
            $msg = "Product Id = {$id_src} import failed. Error: " . $productIpt['msg'];
            $productIpt['msg'] = $this->consoleWarning($msg);
        }
        return $productIpt;
    }

    /**
     * Process after one product import successful
     *
     * @param int $product_mage_id : Id of product save successful to magento
     * @param array $data : Data of function convertProduct
     * @param array $product : One row of object in function getProducts
     * @return boolean
     */
    public function afterSaveProduct($product_mage_id, $data, $product){
        $this->_custom->afterSaveProductCustom($this, $product_mage_id, $data, $product);
        return LitExtension_CartImport_Model_Custom::PRODUCT_AFTER_SAVE;
    }

    /**
     * Process before import import customers
     */
    public function prepareImportCustomers(){
        $this->_custom->prepareImportCustomersCustom($this);
    }

    /**
     * Check customer has been imported
     *
     * @param array $customer : One row of object in function getCustomers
     * @return boolean
     */
    public function checkCustomerImport($customer){
        $id_src = $this->getCustomerId($customer);
        return $this->getIdDescCustomer($id_src);
    }

    /**
     * Import customer with data convert in function convertCustomer
     *
     * @param array $data : Data of function convertCustomer
     * @param array $customer : One row of object in function getCustomers
     * @return array
     */
    public function importCustomer($data, $customer){
        if(LitExtension_CartImport_Model_Custom::CUSTOMER_IMPORT){
            return $this->_custom->importCustomerCustom($this, $data, $customer);
        }
        $id_src = $this->getCustomerId($customer);
        $customerIpt = $this->_process->customer($data);
        if($customerIpt['result'] == 'success'){
            $id_desc = $customerIpt['mage_id'];
            $this->customerSuccess($id_src, $id_desc);
        } else {
            $customerIpt['result'] = 'warning';
            $msg = "Customer Id = {$id_src} import failed. Error: " . $customerIpt['msg'];
            $customerIpt['msg'] = $this->consoleWarning($msg);
        }
        return $customerIpt;
    }

    /**
     * Process after one customer import successful
     *
     * @param int $customer_mage_id : Id of customer import to magento
     * @param array $data : Data of function convertCustomer
     * @param array $customer : One row of object function getCustomers
     * @return boolean
     */
    public function afterSaveCustomer($customer_mage_id, $data, $customer){
        $this->_custom->afterSaveCustomerCustom($this, $customer_mage_id, $data, $customer);
        return LitExtension_CartImport_Model_Custom::CUSTOMER_AFTER_SAVE;
    }

    /**
     * Process before import orders
     */
    public function prepareImportOrders(){
        $this->_custom->prepareImportOrdersCustom($this);
    }

    /**
     * Check order has been imported
     *
     * @param array $order : One row of object in function getOrders
     * @return boolean
     */
    public function checkOrderImport($order){
        $id_src = $this->getOrderId($order);
        return $this->getIdDescOrder($id_src);
    }

    /**
     * Import order with data convert in function convertOrder
     *
     * @param array $data : Data of function convertOrder
     * @param array $order : One row of object in function getOrders
     * @return boolean
     */
    public function importOrder($data, $order){
        if(LitExtension_CartImport_Model_Custom::ORDER_IMPORT){
            return $this->_custom->importOrderCustom($this, $data, $order);
        }
        $id_src = $this->getOrderId($order);
        $orderIpt = $this->_process->order($data, $this->_notice['config']['add_option']['pre_ord']);
        if($orderIpt['result'] == 'success'){
            $id_desc = $orderIpt['mage_id'];
            $this->orderSuccess($id_src, $id_desc);
        } else {
            $orderIpt['result'] = 'warning';
            $msg = "Order Id = {$id_src} import failed. Error: " . $orderIpt['msg'];
            $orderIpt['msg'] = $this->consoleWarning($msg);
        }
        return $orderIpt;
    }

    /**
     * Process after one order save successful
     *
     * @param int $order_mage_id : Id of order import to magento
     * @param array $data : Data of function convertOrder
     * @param array $order : One row of object in function getOrders
     * @return boolean
     */
    public function afterSaveOrder($order_mage_id, $data, $order){
        $this->_custom->afterSaveOrderCustom($this, $order_mage_id, $data, $order);
        return LitExtension_CartImport_Model_Custom::ORDER_AFTER_SAVE;
    }

    /**
     * Process before import reviews
     */
    public function prepareImportReviews(){
        $this->_custom->prepareImportReviewsCustom($this);
        $this->_notice['extend']['rating'] = $this->getRatingOptions();
    }

    /**
     * Check review has been imported
     *
     * @param array $review : One row of object in function getReviews
     * @return boolean
     */
    public function checkReviewImport($review){
        $id_src = $this->getReviewId($review);
        return $this->getIdDescReview($id_src);
    }

    /**
     * Import review with data convert in function convertReview
     *
     * @param array $data : Data of function convertReview
     * @param array $review : One row of object in function getReviews
     * @return array
     */
    public function importReview($data, $review){
        if(LitExtension_CartImport_Model_Custom::REVIEW_IMPORT){
            return $this->_custom->importReviewCustom($this, $data, $review);
        }
        $id_src = $this->getReviewId($review);
        $reviewIpt = $this->_process->review($data, $this->_notice['extend']['rating']);
        if($reviewIpt['result'] == 'success'){
            $id_desc = $reviewIpt['mage_id'];
            $this->reviewSuccess($id_src, $id_desc);
        } else {
            $reviewIpt['result'] = 'warning';
            $msg = "Review Id = {$id_src} import failed. Error: " . $reviewIpt['msg'];
            $reviewIpt['msg'] = $this->consoleWarning($msg);
        }
        return $reviewIpt;
    }

    /**
     * Process after one review save successful
     *
     * @param int $review_mage_id : Id of review import to magento
     * @param array $data : Data of function convertReview
     * @param array $review : One row of object in function getReviews
     * @return boolean
     */
    public function afterSaveReview($review_mage_id, $data, $review){
        $this->_custom->afterSaveReviewCustom($this, $review_mage_id, $data, $review);
        return LitExtension_CartImport_Model_Custom::REVIEW_AFTER_SAVE;
    }

    /**
     * Process clear cache and reindex data after finish migration
     *
     * @return array
     */
    public function finishImport(){
        $response = array(
            'result' => 'success',
            'msg' => ''
        );
        $clear = $this->_process->clearCache();
        $index = $this->_process->reIndexes();
        $folder = Mage::getBaseDir('media') . '/litextension/cartimport/' . $this->_notice['config']['folder'];
        $this->deleteDir($folder);
        $this->clearPreSection();
        if($clear['result'] != 'success' || $index['result'] != 'success'){
            if($clear['msg']){
                $response['msg'] .= $this->consoleWarning($clear['msg']);
            }
            if($index['msg']){
                $response['msg'] .= $this->consoleWarning($index['msg']);
            }
        } else {
            $response['msg'] = $this->consoleSuccess("Finished Clear cache & Reindex data");
        }
        return $response;
    }
    
    public function clearPreSection()
    {
        return array();
    }

    /**
     * TODO : Work with database
     */

    /**
     * Convert array to string insert use in raw query
     *
     * @param array $data
     * @param array $allow_keys
     * @return array
     */
    public function arrayToInsertQueryObject($data, $allow_keys = array()){
        if(!$data){
            return false;
        }
        $items = array();
        $keys = array_keys($data);
        $data_allow = array();
        if(!$allow_keys){
            $items = $keys;
            $data_allow = $data;
        } else {
            foreach($keys as $key){
                if(in_array($key, $allow_keys)){
                    $items[] = $key;
                    $data_allow[$key] = $data[$key];
                }
            }
        }
        if(!$items){
            return false;
        }
        $row = '(`' . implode('`, `', $items) . '`)';
        $value = '(:' . implode(', :', $items) . ')';
        return array(
            'row' => $row,
            'value' => $value,
            'data' => $data_allow
        );
    }

    /**
     * Convert array to string update use in raw query
     *
     * @param array $data
     * @param array $allow_keys
     * @return array
     */
    public function arrayToUpdateQueryObject($data, $allow_keys = array()){
        if(!$data){
            return false;
        }
        $items = array();
        $keys = array_keys($data);
        if(!$allow_keys){
            $allow_keys = $keys;
        }
        foreach($keys as $key){
            if(in_array($key, $allow_keys)){
                $set = '`' . $key . '` = :' . $key;
                $items[] = $set;
            }
        }
        if(!$items){
            return false;
        }
        $set_query = implode(', ', $items);
        return $set_query;
    }

    /**
     * Convert array to where condition in mysql query
     */
    public function arrayToWhereCondition($array){
        if(empty($array)){
            return '1 = 1';
        }
        $data = array();
        foreach($array as $key => $value){
            $data[] = "`{$key}` = '{$value}'";
        }
        $result = implode(" AND ", $data);
        return $result;
    }

    /**
     * Convert array to create table query
     */
    public function arrayToCreateSql($array){
        if(!$array){
            return array(
                'result' => 'error',
                'msg' => "Data not exists."
            );
        }
        $table = $array['table'];
        $rowData = $array['rows'];
        if(!$table || !$rowData){
            return array(
                'result' => 'error',
                'msg' => 'Table data not exists'
            );
        }
        $rows = array();
        foreach($rowData as $row_name => $row_data){
            $row = "`{$row_name}` {$row_data}";
            $rows[] = $row;
        }
        $table_name = $this->getTableName($table);
        $query = "CREATE TABLE IF NOT EXISTS {$table_name} (";
        $query .= implode(',', $rows);
        $query .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8";
        return array(
            'result' => 'success',
            'query' => $query
        );
    }

    /**
     * Get table in magento database with table prefix
     */
    public function getTableName($name){
        return $this->_resource->getTableName($name);
    }

    /**
     * Run write query with magento database
     */
    public function writeQuery($query, $bind = array()){
        try{
            $this->_write->query($query, $bind);
            return true;
        }catch (Exception $e){
            if(LitExtension_CartImport_Model_Custom::DEV_MODE){
                $message = "LitExtension_CartImport_Model_Cart::writeQuery() error: " . $e->getMessage();
                Mage::log($message, null, 'LitExtension_CartImport.log');
            }
            return false;
        }
    }

    /**
     * Run read query with magento database
     */
    public function readQuery($query, $bind= array()){
        try{
            $result = $this->_read->fetchAll($query, $bind);
            return array(
                'result' => 'success',
                'data' => $result
            );
        }catch (Exception $e){
            if(LitExtension_CartImport_Model_Custom::DEV_MODE){
                $message = "LitExtension_CartImport_Model_Cart::readQuery() error: " . $e->getMessage();
                Mage::log($message, null, 'LitExtension_CartImport.log');
            }
            return array(
                'result' => 'error',
                'msg' => $e->getMessage()
            );
        }
    }

    /**
     * Get data from table by where condition
     *
     * @param string $table
     * @param array $where
     * @return array
     */
    public function selectTable($table, $where){
        $where_query = $this->arrayToWhereCondition($where);
        $table_name = $this->_resource->getTableName($table);
        $query = "SELECT * FROM {$table_name} WHERE {$where_query}";
        try{
            $result = $this->_read->fetchAll($query);
            return $result;
        }catch (Exception $e){
            if(LitExtension_CartImport_Model_Custom::DEV_MODE){
                $message = "LitExtension_CartImport_Model_Cart::selectTable() error: " . $e->getMessage();
                Mage::log($message, null, 'LitExtension_CartImport.log');
            }
            return false;
        }
    }

    /**
     * Insert data with type array to table
     *
     * @param string $table
     * @param array $data
     * @param array $allow_keys
     * @return boolean
     */
    public function insertTable($table, $data, $allow_keys = array()){
        $obj = $this->arrayToInsertQueryObject($data, $allow_keys);
        if(!$obj){
            return false;
        }
        $row = $obj['row'];
        $value = $obj['value'];
        $data_allow = $obj['data'];
        $table_name = $this->_resource->getTableName($table);
        $query = "INSERT INTO {$table_name} {$row} VALUES {$value}";
        try{
            $this->_write->query($query, $data_allow);
            return true;
        }catch (Exception $e){          
            if(LitExtension_CartImport_Model_Custom::DEV_MODE){
                $message = "LitExtension_CartImport_Model_Cart::insertTable() error: " . $e->getMessage();
                print_r($message);exit;
                Mage::log($message, null, 'LitExtension_CartImport.log');
            }
            return false;
        }
    }

    /**
     * Update data with type array to table by where condition
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array $allow_keys
     * @return boolean
     */
    public function updateTable($table, $data, $where, $allow_keys = array()){
        $set_query = $this->arrayToUpdateQueryObject($data, $allow_keys);
        if(!$set_query){
            return false;
        }
        $where_query = $this->arrayToWhereCondition($where);
        $table_name = $this->_resource->getTableName($table);
        $query = "UPDATE {$table_name} SET {$set_query} WHERE {$where_query}";
        try{
            $this->_write->query($query, $data);
            return true;
        }catch (Exception $e){
            if(LitExtension_CartImport_Model_Custom::DEV_MODE){
                $message = "LitExtension_CartImport_Model_Cart::updateTable() error: " . $e->getMessage();
                Mage::log($message, null, 'LitExtension_CartImport.log');
            }
            return false;
        }
    }

    /**
     * Delete data from table by where condition
     *
     * @param string $table
     * @param array $where
     * @return boolean
     */
    public function deleteTable($table, $where){
        $where_query = $this->arrayToWhereCondition($where);
        $table_name = $this->_resource->getTableName($table);
        $query = "DELETE FROM {$table_name} WHERE {$where_query}";
        try{
            $this->_write->query($query);
            return true;
        }catch (Exception $e){
            if(LitExtension_CartImport_Model_Custom::DEV_MODE){
                $message = "LitExtension_CartImport_Model_Cart::deleteTable() error: " . $e->getMessage();
                Mage::log($message, null, 'LitExtension_CartImport.log');
            }
            return false;
        }
    }

    public function dropTable($table){
        $table_name = $this->_resource->getTableName($table);
        $query = "DROP TABLE IF EXISTS `" . $table_name . "`";
        return $this->writeQuery($query);
    }

    /**
     * Get one row of result select
     *
     * @param string $table
     * @param array $where
     * @return array
     */
    public function selectTableRow($table, $where){
        $result = $this->selectTable($table, $where);
        if(!$result){
            return false;
        }
        return (isset($result[0])) ? $result[0] : false;
    }

    /**
     * Get id_desc in import table by type and id_src
     */
    public function getLeCaIpImportIdDesc($type, $id_src){
        $result = $this->selectTableRow(self::TABLE_IMPORT, array(
            'folder' => $this->_folder,
            'type' => $type,
            'id_src' => $id_src
        ));
        if(!$result){
            return false;
        }
        return (isset($result['id_desc'])) ? $result['id_desc'] : false;
    }
    
    /**
     * Get id_desc in import table by type and value
     */
    public function getIdDescByValue($value, $type){
        $result = $this->selectTable(self::TABLE_IMPORT, array(
            'folder' => $this->_folder,
            'type' => $type,
            'value' => $value
        ));
        if($result['result'] != 'success'){
            return false;
        }
        $data = $result['data'];
        if(!$data){
            return false;
        }
        return $data[0]['id_desc'];
    }

    /**
     * Get magento tax id import by id src
     */
    public function getIdDescTax($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_TAX, $id_src);
    }

    /**
     * Get magento tax customer id import by id src
     */
    public function getIdDescTaxCustomer($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_TAX_CUSTOMER, $id_src);
    }

    /**
     * Get magento tax product id import by id src
     */
    public function getIdDescTaxProduct($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_TAX_PRODUCT, $id_src);
    }

    /**
     * Get magento tax rate id import by id src
     */
    public function getIdDescTaxRate($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_TAX_RATE, $id_src);
    }

    /**
     * Get magento attribute manufacturer id import by id src
     */
    public function getIdDescManAttr($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_MAN_ATTR, $id_src);
    }

    /**
     * Get magento manufacturer option id import by id src
     */
    public function getIdDescManufacturer($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_MANUFACTURER, $id_src);
    }

    /**
     * Get magento category id import by id src
     */
    public function getIdDescCategory($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_CATEGORY, $id_src);
    }

    /**
     * Get magento product id import by id src
     */
    public function getIdDescProduct($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_PRODUCT, $id_src);
    }

    /**
     * Get magento attribute id import by id src
     */
    public function getIdDescAttribute($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_ATTR, $id_src);
    }

    /**
     * Get magento attribute option id import by id src
     */
    public function getIdDescAttrOption($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_ATTR_OPTION, $id_src);
    }

    /**
     * Get magento customer id import by id src
     */
    public function getIdDescCustomer($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_CUSTOMER, $id_src);
    }

    /**
     * Get magento order id import by id src
     */
    public function getIdDescOrder($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_ORDER, $id_src);
    }

    /**
     * Get magento review id import by id src
     */
    public function getIdDescReview($id_src){
        return $this->getLeCaIpImportIdDesc(self::TYPE_REVIEW, $id_src);
    }

    /**
     * Save info to import table
     */
    public function insertLeCaIpImport($type, $id_src, $id_desc, $status, $value){
        return $this->insertTable(self::TABLE_IMPORT, array(
            'folder' => $this->_folder,
            'domain' => $this->_cart_url,
            'type' => $type,
            'id_src' => $id_src,
            'id_desc' => $id_desc,
            'status' => $status,
            'value' => $value
        ));
    }

    /**
     * Save info of tax import successful to table lecaip_import
     */
    public function taxSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of tax customer import successful to table lecaip_import
     */
    public function taxCustomerSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX_CUSTOMER, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of tax product import successful to table lecaip_import
     */
    public function taxProductSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX_PRODUCT, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of tax rate import successful to table lecaip_import
     */
    public function taxRateSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX_RATE, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of manufacturer attribute import successful to table lecaip_import
     */
    public function manAttrSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_MAN_ATTR, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of manufacturer option import successful to table lecaip_import
     */
    public function manufacturerSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_MANUFACTURER, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of category import successful to table lecaip_import
     */
    public function categorySuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_CATEGORY, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of product import successful to table lecaip_import
     */
    public function productSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_PRODUCT, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of attribute import successful to table lecaip_import
     */
    public function attributeSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_ATTR, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of attribute option import successful to table lecaip_import
     */
    public function attrOptionSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_ATTR_OPTION, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of customer import successful to table lecaip_import
     */
    public function customerSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_CUSTOMER, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of order import successful to table lecaip_import
     */
    public function orderSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_ORDER, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of review import successful to table lecaip_import
     */
    public function reviewSuccess($id_src, $id_desc, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_REVIEW, $id_src, $id_desc, 1, $value);
    }

    /**
     * Save info of tax import error to table lecaip_import
     */
    public function taxError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX, $id_src, false, 0, $value);
    }

    /**
     * Save info of tax customer import error to table lecaip_import
     */
    public function taxCustomerError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX_CUSTOMER, $id_src, false, 0, $value);
    }

    /**
     * Save info of tax product import error to table lecaip_import
     */
    public function taxProductError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX_PRODUCT, $id_src, false, 0, $value);
    }

    /**
     * Save info of tax rate import error to table lecaip_import
     */
    public function taxRateError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_TAX_RATE, $id_src, false, 0, $value);
    }

    /**
     * Save info of manufacturer attribute import error to table lecaip_import
     */
    public function manAttrError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_MAN_ATTR, $id_src, false, 0, $value);
    }

    /**
     * Save info of manufacturer import error to table lecaip_import
     */
    public function manufacturerError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_MANUFACTURER, $id_src, false, 0, $value);
    }

    /**
     * Save info of category import error to table lecaip_import
     */
    public function categoryError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_CATEGORY, $id_src, false, 0, $value);
    }

    /**
     * Save info of product import error to table lecaip_import
     */
    public function productError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_PRODUCT, $id_src, false, 0, $value);
    }

    /**
     * Save info of attribute import error to table lecaip_import
     */
    public function attributeError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_ATTR, $id_src, false, 0, $value);
    }

    /**
     * Save info of attribute option import error to table lecaip_import
     */
    public function attrOptionError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_ATTR_OPTION, $id_src, false, 0, $value);
    }

    /**
     * Save info of customer import error to table lecaip_import
     */
    public function customerError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_CUSTOMER, $id_src, false, 0, $value);
    }

    /**
     * Save info of order import error to table lecaip_import
     */
    public function orderError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_ORDER, $id_src, false, 0, $value);
    }

    /**
     * Save info of review import error to table lecaip_import
     */
    public function reviewError($id_src, $value = false){
        return $this->insertLeCaIpImport(self::TYPE_REVIEW, $id_src, false, 0, $value);
    }

    /**
     * TODO : Work with Magento
     */

    /**
     * Get website id by store id
     */
    public function getWebsiteIdByStoreId($store_id){
        $store = Mage::getModel('core/store')->load($store_id);
        $website_id = $store->getWebsiteId();
        return $website_id;
    }

    /**
     * Get list website id by list store id
     */
    public function getWebsiteIdsByStoreIds($store_ids){
        if($store_ids && !empty($store_ids)){
            $website_id = array();
            foreach($store_ids as $store_id){
                $store = Mage::getModel('core/store')->load($store_id);
                $website_id[] = $store->getWebsiteId();
            }
            return $this->_filterArrayValueDuplicate($website_id);
        }
        return false;
    }

    /**
     * Get currency config of store and base website
     */
    public function getStoreCurrencyCode($store_id){
        $result = array();
        $store = Mage::getModel('core/store')->load($store_id);
        $result['base'] = $store->getBaseCurrencyCode();
        $result['current'] = $store->getCurrentCurrencyCode();
        return $result;
    }

    /**
     * Pass customer pass to database not encrypt
     */
    public function importCustomerRawPass($customer_id, $pass){
        try{
            $entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
            $attrPass = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'password_hash');
            $attrPassId = $attrPass->getAttributeId();
            $table = $this->_resource->getTableName('customer_entity_varchar');
            $query = "UPDATE {$table} SET `value`='{$pass}'
                        WHERE entity_type_id = '{$entityTypeId}'
                            AND attribute_id = '{$attrPassId}'
                            AND entity_id = '{$customer_id}'";
            $this->_write->query($query);
        } catch(LitExtension_CartMigration_Exception $e){
        } catch(Exception $e){}
    }

    /**
     * Set attribute select to product
     */
    public function setProAttrSelect($entity_type_id, $attribute_id, $product_id, $option_id){
        $table = $this->_resource->getTableName('catalog_product_entity_int');
        $query = "INSERT INTO {$table} (`value_id`, `entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (null, {$entity_type_id}, {$attribute_id}, '0', {$product_id}, {$option_id})";
        try{
            $this->_write->query($query);
        }catch (LitExtension_CartMigration_Exception $e){
        }catch(Exception $e){
        }
    }

    /**
     * Set attribute date to product
     */
    public function setProAttrDate($entity_type_id, $attribute_id, $product_id, $date){
        $table = $this->_resource->getTableName('catalog_product_entity_datetime');
        $query = "INSERT INTO {$table} (`value_id`, `entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (null, {$entity_type_id}, {$attribute_id}, '0', {$product_id}, '{$date}')";
        try{
            $this->_write->query($query);
        }catch (LitExtension_CartMigration_Exception $e){
        }catch(Exception $e){
        }
    }

    /**
     * Set attribute text to product
     */
    public function setProAttrText($entity_type_id, $attribute_id, $product_id, $text){
        $table = $this->_resource->getTableName('catalog_product_entity_text');
        $query = "INSERT INTO {$table} (`value_id`, `entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (null, {$entity_type_id}, {$attribute_id}, '0', {$product_id}, '{$text}')";
        try{
            $this->_write->query($query);
        }catch (LitExtension_CartMigration_Exception $e){
        }catch(Exception $e){
        }
    }

    /**
     * Set attribute varchar to product
     */
    public function setProAttrVarchar($entity_type_id, $attribute_id, $product_id, $varchar){
        $table = $this->_resource->getTableName('catalog_product_entity_varchar');
        $query = "INSERT INTO {$table} (`value_id`, `entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (null, {$entity_type_id}, {$attribute_id}, '0', {$product_id}, '{$varchar}')";
        try{
            $this->_write->query($query);
        }catch (LitExtension_CartMigration_Exception $e){
        }catch(Exception $e){
        }
    }

    /**
     * Set option to product
     */
    public function setProductHasOption($product_id){
        $table = $this->_resource->getTableName('catalog_product_entity');
        $query = "UPDATE {$table} SET has_options = 1, required_options = 1 WHERE entity_id = {$product_id}";
        try{
            $this->_write->query($query);
        }catch (LitExtension_CartMigration_Exception $e){
        }catch(Exception $e){
        }
    }

    /**
     * Import custom option to product
     */
    public function importProductOption($product_id, $options){
        try{
            $product = Mage::getModel('catalog/product')->load($product_id);
            if(!$product->getOptionsReadonly()) {
                foreach($options as $option){
                    $opt = Mage::getModel('catalog/product_option');
                    $opt->setProduct($product);
                    $opt->addOption($option);
                    $opt->saveOptions();
                }
                $this->setProductHasOption($product_id);
            }
        } catch(LitExtension_CartMigration_Exception $e){
        } catch(Exception $e){}
    }

    /**
     * Set increment for order
     */
    public function setOrderIncrement($store_ids, $increment_id){
        $store_ids = array_values($store_ids);
        $store_id  = $store_ids[0];
        try{
            $entityStoreConfig = Mage::getModel('eav/entity_store')
                ->loadByEntityStore(5, $store_id);
            $increment_id = $this->formatIncrementId($store_id, $increment_id);
            if (!$entityStoreConfig->getId()) {
                $entityStoreConfig
                    ->setEntityTypeId(5)
                    ->setStoreId($store_id)
                    ->setIncrementPrefix($store_id)
                    ->setIncrementLastId($increment_id)
                    ->save();
            } else {
                $entityStoreConfig
                    ->setIncrementLastId($increment_id)
                    ->save();
            }
        }catch (LitExtension_CartMigration_Exception $e){
        }catch (Exception $e){}
    }

    /**
     * Format increment to increment construct of magento
     */
    public function formatIncrementId($store_id, $id, $pad_length = 8, $pad_char = '0'){
        $increment_id = ($id < 0)? '-' : '';
        $increment_id .= $store_id . str_pad((string)abs($id), $pad_length, $pad_char, STR_PAD_LEFT);
        return $increment_id;
    }

    /**
     * Get list rating review
     */
    public function getRatingOptions(){
        $data = array();
        $ratings = Mage::getModel('rating/rating')->getCollection();
        foreach($ratings as $rating){
            $rating_id = $rating->getId();
            $options = Mage::getModel('rating/rating_option')->getCollection();
            $options->addFieldToFilter('rating_id', array('eq' => $rating_id));
            $tmp = array();
            foreach($options as $option){
                $tmp = array_merge($tmp, array($option->getId()));
            }
            $data[$rating_id] = array_values($tmp);
        }
        return $data;
    }

    /**
     * Get or create default tax customer
     */
    public function getTaxCustomerDefault(){
        $response = array();
        $customerTax = Mage::getModel('tax/class')
            ->getCollection()
            ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
            ->getFirstItem();
        if($customerTax->getId()){
            $response['result'] = 'success';
            $response['mage_id'] = $customerTax->getId();
        } else{
            $newCustomerTax = Mage::getModel('tax/class');
            $newCustomerTax->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
            $newCustomerTax->setClassName('Retail Customer');
            try{
                $newCustomerTax->save();
                $response['result'] = 'success';
                $new_tax_customer_id = $newCustomerTax->getId();
                $response['mage_id'] = $new_tax_customer_id;
                $group = Mage::getModel('customer/group');
                $collection = $group->getCollection();
                foreach($collection as $item){
                    if($item->getCustomerGroupCode() == 'NOT LOGGED IN' || $item->getCustomerGroupCode() == 'General'){
                        $item->setTaxClassId($new_tax_customer_id);
                        try{$item->save();}catch (Exception $e){}
                    }
                }
            }catch (LitExtension_CartMigration_Exception $e){
                $response['result'] = 'error';
                $response['msg'] = $e->getMessage();
            } catch(Exception $e){
                $response['result'] = 'error';
                $response['msg'] = $e->getMessage();
            }
        }
        return $response;
    }

    /**
     * Get or create manufacturer attribute
     */
    public function getManufacturerAttributeId($attribute_set_id){
        $result = array();
        $data = array(
            'attribute_code'                => self::MANUFACTURER_CODE,
            'frontend_input'                => 'select',
            'backend_type'                  => 'int',
            'apply_to'                      => array(),
            'is_global'                     => 1,
            'is_unique'                     => 0,
            'is_required'                   => 0,
            'is_configurable'               => 1,
            'is_searchable'                 => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable'                 => 0,
            'is_filterable'                 => 0,
            'is_filterable_in_search'       => 0,
            'is_used_for_promo_rules'       => 0,
            'is_user_defined'               => 1,
            'is_html_allowed_on_front'      => 1,
            'is_visible_on_front'           => 0,
            'used_in_product_listing'       => 0,
            'used_for_sort_by'              => 0,
            'frontend_label'                => array(
                '0' => 'Manufacture',
            ),
        );
        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $manufacture = Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->addFieldToFilter('attribute_code', $data['attribute_code'])
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->getFirstItem();
        if($manufacture->getId()){
            $result['result'] = 'success';
            $result['mage_id'] = $manufacture->getId();
            if($attribute_group_id = $this->_getAttributeGroupId($attribute_set_id)){
                $manufacture->setAttributeSetId($attribute_set_id);
                $manufacture->setAttributeGroupId($attribute_group_id);
                try{
                    $manufacture->save();
                } catch(Exception $e){
                    // do nothing
                }
            }
        } else {
            $attr = Mage::getModel('catalog/resource_eav_attribute');
            $attr->setData($data);
            $attr->setEntityTypeId($entityTypeId);
            $attr->setAttributeSetId($attribute_set_id);
            if($attribute_group_id = $this->_getAttributeGroupId($attribute_set_id)){
                $attr->setAttributeGroupId($attribute_group_id);
            }
            try{
                $attr->save();
                $result['result'] = 'success';
                $result['mage_id'] = $attr->getId();
            } catch(LitExtension_CartMigration_Exception $e){
                $result['result'] = 'error';
                $result['msg'] = $e->getMessage();
            } catch(Exception $e){
                $result['result'] = 'error';
                $result['msg'] = $e->getMessage();
            }
        }
        return $result;
    }

    /**
     * Get default group attribute by attribute set
     */
    protected function _getAttributeGroupId($attribute_set_id){
        $attribute_group_id = false;
        $group_general = Mage::getModel('eav/entity_attribute_group')
            ->getCollection()
            ->addFieldToFilter('attribute_set_id', $attribute_set_id)
            ->addFieldToFilter('attribute_group_name', 'general')
            ->getFirstItem();
        if($group_general->getId()){
            $attribute_group_id = $group_general->getId();
        } else{
            $group_first = Mage::getModel('eav/entity_attribute_group')
                ->getCollection()
                ->addFieldToFilter('attribute_set_id', $attribute_set_id)
                ->getFirstItem();
            if($group_first->getId()){
                $attribute_group_id = $group_first->getId();
            } else {
                $data = array(
                    'attribute_group_name'  => 'General',
                    'attribute_set_id'      => $attribute_set_id
                );
                $attrSet = Mage::getModel('eav/entity_attribute_set');
                $attrGroup = Mage::getModel('eav/entity_attribute_group');
                $attrGroup->addData($data);
                try{
                    $attrGroup->save();
                    $attrSet->setGroups(array($attrGroup));
                    $attribute_group_id = $attrGroup->getId();
                } catch(LitExtension_CartMigration_Exception $e){
                    // do nothing
                } catch(Exception $e){
                    // do nothing
                }
            }
        }
        return $attribute_group_id;
    }

    /**
     * Create tax rule code with string
     */
    public function createTaxRuleCode($code){
        $i = 0;
        $new_code = $code;
        while($this->_checkTaxRuleCodeExists($new_code)){
            $i++;
            $new_code = $code.'-'.$i;
        }
        return $new_code;
    }

    /**
     * Check tax rule code exists
     */
    protected function _checkTaxRuleCodeExists($code){
        $taxRate = Mage::getModel('tax/calculation_rule')
            ->getCollection()
            ->addFieldToFilter('code', $code)
            ->getFirstItem();
        if($taxRate->getId()){
            return true;
        }
        return false;
    }

    /**
     * Create tax rate code with string
     */
    public function createTaxRateCode($code){
        $i = 0;
        $new_code = $code;
        while($this->_checkTaxRateCodeExist($new_code)){
            $i++;
            $new_code = $code.' - '.$i;
        }
        return $new_code;
    }

    /**
     * Check tax rate code exists
     */
    protected function _checkTaxRateCodeExist($code){
        $taxRate = Mage::getModel('tax/calculation_rate')
            ->getCollection()
            ->addFieldToFilter('code', $code)
            ->getFirstItem();
        if($taxRate->getId()){
            return true;
        }
        return false;
    }

    /**
     * Create product sku by string
     */
    public function createProductSku($sku, $store_ids){
        $i = 0;
        $new_sku = $sku;
        while($this->_checkProductSkuExists($new_sku, $store_ids)){
            $i++;
            $new_sku = $sku.'-'.$i;
        }
        return $new_sku;
    }

    /**
     * Check product sku exists
     */
    protected function _checkProductSkuExists($sku, $store_ids){
        $product = Mage::getModel("catalog/product")
            ->setStoreIds($store_ids)
            ->getCollection()
            ->addAttributeToSelect("sku")
            ->addFieldToFilter("sku", array('eq' => $sku))
            ->getFirstItem();
        if($product->getId()){
            return true;
        }
        return false;
    }

    /**
     * Get region id by name state and country iso code 2
     */
    public function getRegionId($name , $code){
        $result = null;
        $regions = Mage::getModel('directory/region')
            ->getCollection()
            ->addFieldToFilter('default_name', $name)
            ->addFieldToFilter('country_id', $code)
            ->getFirstItem();
        if($regions->getId()){
            $result = $regions->getId();
        } else{
            $result = 0;
        }
        return $result;
    }

    /**
     * Get order state by order status
     */
    public function getOrderStateByStatus($status){
        $result = false;
        $collection = Mage::getModel('sales/order_status')->getCollection()->joinStates();
        foreach($collection as $item){
            if($item['status'] == $status){
                $result = $item['state'];
                break ;
            }
        }
        return $result;
    }

    /**
     * TODO : Work with image
     */

    /**
     * Download image to media folder
     */
    public function downloadImage($url, $image_path, $type, $base_name = false, $return_path = false, $check_ext = true, $insert_ext = false){
        try{
            if($check_ext && !$this->_checkFileTypeImport($image_path)){
                return false;
            }
            $desc_location = Mage::getBaseDir() . '/media/' . $type . '/';
            if(!is_dir($desc_location)){
                @mkdir($desc_location, 0777, true);
            }
            $img_src = rtrim($url, '/') . '/';
            if($this->_isUrlEncode($image_path)){
                $img_src .= ltrim($image_path, '/');
            } else {
                $img_src .= ltrim($this->_getUrlRealPath($image_path), '/');
            }
            if(!$this->imageExists($img_src)){
                return false;
            }
            if(!$base_name){
                $path_save = $this->_createPathToSave(basename($image_path));
                $img_desc = $desc_location . $path_save;
            } else {
                $path_save = $this->_createPathToSave($image_path);
                $img_desc = $desc_location. $path_save;
                if(!is_dir(dirname($img_desc))){
                    @mkdir(dirname($img_desc), 0777, true);
                }
            }
            if($insert_ext){
                $extension = '';
                $img_src .= '?'.$insert_ext;
                $path_save .= $this->_createPathToSave($insert_ext);
                $header = @get_headers($img_src, 1);
                if($header){
                    $content_type = $header['Content-Type'];
                    $extension = $this->_getImageTypeByContentType($content_type);
                }
                $path_save .= $extension;
                $img_desc = $desc_location. $path_save;
            }
            $path = false;
            if ($image_path != '') {
                $fp = fopen($img_desc, 'w');
                $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0';
                $ch = curl_init($img_src);
                curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); //10s
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }
            if (file_exists($img_desc)) {
                if(!$return_path){
                    $path = $path_save;
                } else {
                    $path = $img_desc;
                }
            }
            return $path;
        }catch (Exception $e){
            return false;
        }
    }

    public function imageExists($url){
        $header = @get_headers($url, 1);
        if(!$header){
            return false;
        }
        $string = $header[0];
        if(strpos($string, "404")){
            return false;
        }
        return true;
    }

    protected function _isUrlEncode($path){
        $is_encoded = @preg_match('~%[0-9A-F]{2}~i', $path);
        return $is_encoded;
    }
    
    /**
     * Download image with url
     */
    public function downloadImageFromUrl($url,  $type, $base_name = false, $return_path = false, $check_ext = true){
        $insert_extension = false;
        $url = parse_url($url);
        if(isset($url['host'])){
            $host = $url['scheme'].'://'.$url['host'];
            $path = substr($url['path'],1);
            if(isset($url['query'])){
                $insert_extension = $url['query'];
            }
        } else {
            $host = $this->_cart_url;
            $path = $url['path'];
        }
        return $this->downloadImage($host, $path, $type, $base_name, $return_path, $check_ext, $insert_extension);
    }

    /**
     * Check image type for import
     */
    protected function _checkFileTypeImport($file_name){
        $result = false;
        $typesAllow = array('jpg', 'jpeg', 'gif', 'png');
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
        if(in_array(strtolower($file_type), $typesAllow)){
            $result = true;
        }
        return $result;
    }

    /**
     * Create url by encode special character
     */
    protected function _getUrlRealPath($path){
        $splits = explode('/', $path);
        $data = array();
        foreach($splits as $key => $split){
            $data[$key] = rawurlencode($split);
        }
        $path = implode('/', $data);
        return $path;
    }

    /**
     * Create path save by replace special character to -
     */
    protected function _createPathToSave($path){
        $splits = explode('/',$path);
        $data = array();
        foreach($splits as $key => $split){
            $split = preg_replace('/[^A-Za-z0-9._\-]/', '-', $split);
            $data[$key] = $split;
        }
        $path = implode('/',$data);
        return $path;
    }

    /**
     * Detect image extension with content type
     */
    protected function _getImageTypeByContentType($content_type){
        $result = '';
        $mineType =array(
            'image/jpeg'    => '.jpg',
            'image/png'     => '.png',
            'image/gif'     => '.gif',
            'image/pjpeg'   => '.jpeg',
            'image/x-icon'  => '.ico',
        );
        if($mineType[$content_type]){
            $result = $mineType[$content_type];
        }
        return $result;
    }

    /**
     * Download image and change image tag in text
     */
    public function changeImgSrcInText($html, $img_des){
        if(!$img_des){ return $html;}
        $links = array();
        preg_match_all('/<img[^>]+>/i', $html, $img_tags);
        foreach ($img_tags[0] as $img) {
            preg_match('/(src=["\'](.*?)["\'])/', $img, $src);
            if(!isset($src[0])){
                continue;
            }
            $split = preg_split('/["\']/', $src[0]);
            $links[] = $split[1];
        }
        $links = $this->_filterArrayValueDuplicate($links);
        foreach($links as $link){
            if($new_link = $this->_getImgDesUrlImport($link)){
                $html = str_replace($link, $new_link, $html);
            }
        }
        return $html;
    }

    /**
     * Download image and change image tag in array
     */
    public function changeImgSrcInList($list, $fields, $img_des){
        if(!$img_des){
            return $list;
        }
        if(is_string($fields)){
            $fields = array($fields);
        }
        $links = array();
        foreach($list as $row){
            foreach($fields as $field){
                if(!isset($row[$field])){
                    continue ;
                }
                $content = $row[$field];
                if(!$content){
                    continue ;
                }
                preg_match_all('/<img[^>]+>/i', $content, $img_tags);
                foreach ($img_tags[0] as $img) {
                    if(!$img){
                        continue;
                    }
                    preg_match('/(src=["\'](.*?)["\'])/', $img, $src);
                    if(!isset($src[0])){
                        continue;
                    }
                    $split = preg_split('/["\']/', $src[0]);
                    $links[] = $split[1];
                }
            }
        }
        $links = $this->_filterArrayValueDuplicate($links);
        $data = array();
        foreach($links as $link){
            $new_link = $this->_getImgDesUrlImport($link);
            if($new_link){
                $data[] = array(
                    'old' => $link,
                    'new' => $new_link
                );
            }
        }
        if(!$data){
            return $list;
        }
        foreach($list as $key => $row){
            foreach($fields as $field){
                if(!isset($row[$field])){
                    continue ;
                }
                $content = $row[$field];
                if(!$content){
                    continue ;
                }
                foreach($data as $link){
                    $pattern = array(
                        '/src="' . $link['old'] . '"/',
                        "/src='" . $link['old'] . "'/",
                    );
                    $replacement = array(
                        'src="' . $link['new'] . '"',
                        "src='" . $link['new'] . "'",
                    );
                    $content = preg_replace($pattern, $replacement, $content);
                }
                $list[$key][$field] = $content;
            }
        }
        return $list;
    }

    /**
     * Download image with url
     */
    protected function _getImgDesUrlImport($url){
        $result = false;
        $insert_extension = false;
        $url_tmp = parse_url($url);
        if(isset($url_tmp['host'])){
            $host = $url_tmp['scheme'].'://'.$url_tmp['host'];
            $path = substr($url_tmp['path'],1);
            if(isset($url_tmp['query'])){
                $insert_extension = $url_tmp['query'];
            }
        } else {
            if(substr($url_tmp['path'], 0, 2) == '//'){
                $real_url = 'http:' . $url;
                $url_tmp = parse_url($real_url);
                $host = $url_tmp['scheme'].'://'.$url_tmp['host'];
                $path = substr($url_tmp['path'],1);
                if(isset($url_tmp['query'])){
                    $insert_extension = $url_tmp['query'];
                }
            } else {
                $host = $this->_cart_url;
                $path = $url_tmp['path'];
            }
        }
        if($path_import = $this->downloadImage($host, $path, 'wysiwyg', false, false, false, $insert_extension)){
            $result = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'wysiwyg/' . $path_import;
        }
        return $result;
    }

    /**
     * TODO : Work with csv file
     */

    /**
     * Read file csv with limit csv line
     */
    public function readCsv($file_path, $start, $limit = 10, $total = false){
        if(!is_file($file_path)){
            return array(
                'result' => 'error',
                'msg' => 'Path not exists'
            );
        }
        try{
            $finish = false;
            $count = 0;
            $csv = fopen($file_path, 'r');
            $end = $start + $limit;
            $csv_title = "";
            $data = array();
            while (!feof($csv)){
                if($total && $count > $total){
                    $finish = true;
                    break ;
                }
                if($count > $end){
                    break ;
                }
                $line = fgetcsv($csv);
                if ($count == 0) {
                    $csv_title = $line;
                }
                if($start < $count && $count <= $end){
                    $data[] = array(
                        'title' => str_replace('-', '_', str_replace(' ', '_', $csv_title)),
                        'row' => $line
                    );
                }
                $count++;
            }
            fclose($csv);
            if(!$finish && ($count - 1) <$end){
                $finish = true;
            }            
            return array(
                'result' => 'success',
                'data' => $data,
                'count' => $end,
                'finish' => $finish
            );
        } catch (Exception $e){
            return array(
                'result' => 'error',
                'msg' => $e->getMessage()
            );
        }
    }

    /**
     * Add csv title to csv data
     */
    public function syncCsvTitleRow($csv_title, $csv_row){
        if(!$csv_row){
            return array();
        }
        $row_value = array_filter($csv_row);
        if(!$row_value || empty($row_value)){
            return array();
        }
        $data = array();
        foreach ($csv_title as $key => $title_name){
            $data[$title_name] = (isset($csv_row[$key]))? $csv_row[$key] : null;
        }
        return $data;
    }

    /**
     * TODO : Extend function
     */

    /**
     * Get list array from list by list field value
     */
    public function getListFromListByListField($list, $field, $values){
        if(!$list){
            return false;
        }
        if(!is_array($values)){
            $values = array($values);
        }
        $result = array();
        foreach($list as $row){
            if(in_array($row[$field], $values)){
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Get list array from list by field  value
     */
    public function getListFromListByField($list, $field, $value){
        if(!$list){
            return false;
        }
        $result = array();
        foreach($list as $row){
            if($row[$field] == $value){
                $result[] = $row;
            }
        }
        return $result;
    }

    public function filterArrayValueFalse($array){
        if(!$array){
            return $array;
        }
        foreach($array as $key => $value){
            if(!$value){
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Get one array from list array by field value
     */
    public function getRowFromListByField($list, $field, $value){
        if(!$list){
            return false;
        }
        $result = false;
        foreach($list as $row){
            if(isset($row[$field]) && $row[$field] == $value){
                $result = $row;
                break ;
            }
        }
        return $result;
    }

    /**
     * Get array value from list array by field value and key of field need
     */
    public function getRowValueFromListByField($list, $field, $value, $need){
        if(!$list){
            return false;
        }
        $row = $this->getRowFromListByField($list, $field, $value);
        if(!$row){
            return false;
        }
        return $row[$need];
    }

    /**
     * Get and unique array value by key
     */
    public function duplicateFieldValueFromList($list, $field){
        $result = array();
        if(!$list){
            return $result;
        }
        foreach ((array)$list as $item) {
            if (isset($item[$field])) {
                $result[] = $item[$field];
            }
        }
        $result = array_unique($result);
        return $result;
    }

    /**
     * Add folder and url to data before save to database
     */
    public function addConfigToArray($array){
        $array['domain'] = $this->_cart_url;
        $array['folder'] = $this->_folder;
        return $array;
    }

    /**
     * Create folder to upload csv
     */
    public function createFolderUpload($url){
        $code = $url . time();
        return md5($code);
    }

    /**
     * Get url of source cart with suffix
     */
    public function getUrlSuffix($suffix){
        $url = rtrim($this->_cart_url, '/') . '/' . ltrim($suffix, '/');
        return $url;
    }

    /**
     * Convert result of query get count to count
     */
    public function arrayToCount($array, $name = false){
        if(empty($array)){
            return 0;
        }
        $count = 0;
        if($name){
            $count = isset($array[0][$name])? $array[0][$name] : 0;
        } else {
            $count = isset($array[0][0])? $array[0][0] : 0;
        }
        return $count;
    }

    /**
     * Convert array to in condition in mysql query
     */
    public function arrayToInCondition($array){
        if(empty($array)){
            return "('null')";
        }
        $result = "('".implode("','", $array)."')";
        return $result;
    }

    /**
     * Convert array to set values condition in mysql query
     */
    public function arrayToSetCondition($array){
        if(empty($array)){
            return '';
        }
        $data = array();
        foreach($array as $key => $value){
            $data[] = "`{$key}` = '{$value}'";
        }
        $result = implode(',', $data);
        return $result;
    }

    /**
     * Add class success to text for show in console
     */
    public function consoleSuccess($msg){
        $result = '<p class="success"> - ' . $msg . '</p>';
        return $result;
    }

    /**
     * Add class warning to text for show in console
     */
    public function consoleWarning($msg){
        $result = '<p class="warning"> - ' . $msg . '</p>';
        return $result;
    }

    /**
     * Add class error to text for show in console
     */
    public function consoleError($msg){
        $result = '<p class="error"> - ' . $msg . '</p>';
        return $result;
    }

    /**
     * Message if not save info to magento database
     */
    public function errorDatabase($console = false){
        $msg = "Magento database isn't working!";
        if($console){
            $msg = $this->consoleError($msg);
        }
        return array(
            'result' => 'error',
            'msg' => $msg
        );
    }

    /**
     * Convert time to string show in console
     */
    public function createTimeToShow($time){
        $hour = gmdate('H', $time);
        $minute = gmdate('i', $time);
        $second = gmdate('s', $time);
        $result = '';
        if($hour && $hour > 0) $result .= $hour.' hours ';
        if($minute && $minute > 0) $result .= $minute. ' minutes ';
        if($second && $second >0 ) $result .= $second . ' seconds ';
        return $result;
    }

    /**
     * Create key by string
     */
    public function joinTextToKey($text, $length = false, $char = '-', $lower = true){
        $text .= " ";
        if($length){
            $length = (int) $length;
            $text = substr($text, 0, $length);
            if($end = strrpos($text, ' ')){
                $text = substr($text, 0, strrpos($text, ' '));
            }
        }
        $text = preg_replace('/[^A-Za-z0-9 ]/', '', $text);
        $text = preg_replace('/\s+/', ' ',$text);
        $text = str_replace(' ', $char, $text);
        $text = trim($text, $char);
        if($lower) $text = strtolower($text);
        return $text;
    }

    /**
     * Filter value of array 3D
     */
    protected function _filterArrayValueDuplicate($array){
        $result = array();
        if($array && !empty($array)){
            $array_values = array_values($array);
            foreach($array_values as $key => $value){
                foreach($array_values  as $key_filter => $value_filter){
                    if($key_filter < $key){
                        if($value == $value_filter){
                            unset($array_values[$key]);
                        }
                    }
                }
            }
            $result = array_values($array_values);
        }
        return $result;
    }

    /**
     * Check sync cart type select and cart type detect
     */
    protected function _checkCartSync($cms, $select) {
        $pos = strpos($select, $cms);
        if($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get percent by total and import
     */
    public function getPoint($total, $import, $finish = false){
        if(!$finish && $total == 0){
            return 0;
        }
        if($finish){
            return 100;
        }
        if ($total < $import) {
            $point = 100;
        } else {
            $percent = $import / $total;
            $point = number_format($percent, 2) * 100;
        }
        return $point;
    }

    /**
     * Get message for next entity import
     */
    public function getMsgStartImport($type){
        $result = '';
        if(!$type){
            $result .= $this->consoleSuccess("Finished migration!");
            return $result;
        }
        $types = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews');
        $type_key = array_search($type, $types);
        foreach ($types as $key => $value) {
            if ($type_key <= $key && $this->_notice['config']['import'][$value]) {
                $result .= $this->consoleSuccess('Importing ' . $value . ' ... ');
                break;
            }
        }
        return $result;
    }

    /**
     * Increment order price pass through magento order grand total not equal 0
     */
    public function incrementPriceToImport($price){
        if($price == 0){
            $price = 0.001;
        }
        return $price;
    }

    /**
     * Convert string of full name to first name and last name
     */
    public function getNameFromString($name){
        $result = array();
        $parts = explode(' ', $name);
        $result['lastname'] = array_pop($parts);
        $result['firstname'] = implode(" ", $parts);
        return $result;
    }

    /**
     * Delete folder and content of folder
     */
    public function deleteDir($path){
        if(!is_dir($path)){
            return array(
                'result' => 'error',
                'msg' => 'Path is not directory.'
            );
        }
        try{
            $path = rtrim($path, '/\\');
            $items = glob($path . '/*', GLOB_MARK);
            foreach ($items as $item) {
                if (is_dir($item)) {
                    $this->deleteDir($item);
                } else {
                    unlink($item);
                }
            }
            rmdir($path);
            return array(
                'result' => 'success'
            );
        }catch (Exception $e){
            return array(
                'result' => 'error',
                'msg' => $e->getMessage()
            );
        }
    }

    /**
     * Unset list key from array
     */
    public function unsetListArray($need, $haystack){
        if(!$need || !is_array($need) || !is_array($haystack)){
            return $haystack;
        }
        foreach($need as $key){
            if(isset($haystack[$key])){
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }

    public function getArrayValueByValueArray($value, $need = array(), $haystack = array()){
        $result = false;
        if(!is_array($need) || !is_array($haystack)){
            return $result;
        }
        $key = array_search($value, $need);
        if($key === false){
            return $result;
        }
        $result = isset($haystack[$key]) ? $haystack[$key] : false;
        return $result;
    }

    /**
     * Client request url
     */
    public function request($url, $method = Zend_Http_Client::GET, $params = array(), $config = array('timeout' => 60), $header = array()){
//        $result = false;
//        $valid = $this->_urlExists($url);
//        if(!$valid){
//            return $result;
//        }
        $client = new Zend_Http_Client($url, $config);
        if($params){
            switch ($method) {
                case Zend_Http_Client::GET :
                    $client->setParameterGet($params);
                    break;
                case Zend_Http_Client::POST :
                    $client->setParameterPost($params);
                    break;
                case Zend_Http_Client::PUT :
                    $client->setParameterPost($params);
                    break;
                case Zend_Http_Client::DELETE :
                    $client->setParameterGet($params);
                    break;
                default:
                    $client->setParameterPost($params);
                    break;
            }
        }
        if($header){
            $client->setHeaders($header);
        }
        $response = $client->request($method);
        $result = $response->getBody();
        sleep($this->_notice['setting']['delay']);
        return $result;
    }

    /**
     * Check url exists
     */
    protected function _urlExists($url){
        $header = @get_headers($url, 1);
        if(!$header){
            return false;
        }
        $string = $header[0];
        if(strpos($string, "200")){
            return true;
        }
        return false;
    }

    /**
     * TODO : Demo mode
     */

    /**
     * Setup limit to demo mode
     */
    protected function _limitDemoModel($type){
        if(LitExtension_CartImport_Model_Custom::DEMO_MODE){
            return isset($this->_demo_limit[$type])? $this->_demo_limit[$type] : false;
        }
        return false;
    }

    protected function _limit($counts){
        $limit = false;
        $license = trim(Mage::getStoreConfig('lecaip/general/license'));
        if($license){
            $check_license = $this->request(
                chr(104).chr(116).chr(116).chr(112).chr(58).chr(47).chr(47).chr(108).chr(105).chr(116).chr(101).chr(120).chr(116).chr(101).chr(110).chr(115).chr(105).chr(111).chr(110).chr(46).chr(99).chr(111).chr(109).chr(47).chr(108).chr(105).chr(99).chr(101).chr(110).chr(115).chr(101).chr(46).chr(112).chr(104).chr(112),
                Zend_Http_Client::GET,
                array(
                    'user' => "bGl0ZXg=",
                    'pass' => "YUExMjM0NTY=",
                    'action' => "Y2hlY2s=",
                    'license' => base64_encode($license),
                    'cart_type' => base64_encode($this->_notice['config']['cart_type']),
                    'url' => base64_encode($this->_cart_url),
                    'target_type' => base64_encode('magento1'),
                    'save' => true
                )
            );
            if($check_license){
                $check_license = unserialize(base64_decode($check_license));
                if($check_license['result'] == 'success'){
                    $limit = $check_license['data']['limit'];
                }
            }
        }
        $this->_notice['config']['limit'] = $limit ? $limit : 0;
        $data = array();
        if(!$limit){
            foreach($counts as $type => $count){
                $data[$type] = 0;
            }
            return $data;
        } else {
            $total = $limit;
            if($limit === 'unlimit'){
                $limit = 'unlimited';
                $this->_notice['config']['limit'] = 'unlimited';
            }
            if($limit !== 'unlimited'){
                foreach($counts as $type => $count){
                    $new_count = ($count < $total)? $count : $total;
                    $counts[$type] = $new_count;
                }
            }
        }
        if(LitExtension_CartImport_Model_Custom::DEMO_MODE){
            $data = array();
            foreach($counts as $type => $count){
                $data[$type] = ($count < $this->_demo_limit[$type])? $count : $this->_demo_limit[$type];
            }
            return $data;
        }
        return $counts;
    }

    public function updateApi(){
        $license = trim(Mage::getStoreConfig('lecaip/general/license'));
        if(!$license){
            return ;
        }
        if($license){
            $check_license = $this->request(
                chr(104).chr(116).chr(116).chr(112).chr(58).chr(47).chr(47).chr(108).chr(105).chr(116).chr(101).chr(120).chr(116).chr(101).chr(110).chr(115).chr(105).chr(111).chr(110).chr(46).chr(99).chr(111).chr(109).chr(47).chr(108).chr(105).chr(99).chr(101).chr(110).chr(115).chr(101).chr(46).chr(112).chr(104).chr(112),
                Zend_Http_Client::GET,
                array(
                    'user' => "bGl0ZXg=",
                    'pass' => "YUExMjM0NTY=",
                    'action' => "dXBkYXRl",
                    'license' => base64_encode($license),
                    'cart_type' => base64_encode($this->_notice['config']['cart_type']),
                    'url' => base64_encode($this->_cart_url),
                    'base' => Mage::getBaseUrl(),
                    'target_type' => base64_encode('magento1'),
                )
            );
            if($check_license){
            }
        }
    }

    /**
     * Import password for customer
     */
    protected function _importCustomerRawPass($customer_id, $pass){
        $entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
        $attrPass = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'password_hash');
        $attrPassId = $attrPass->getAttributeId();
        return $this->updateTable('customer_entity_varchar', array(
            'value' => $pass
        ), array(
            'entity_type_id' => $entityTypeId,
            'attribute_id' => $attrPassId,
            'entity_id' => $customer_id
        ));
    }

    public function checkUrlSame($domain, $url){
        $domain_none_http = $this->removeHttp($domain);
        $url_none_http = $this->removeHttp($url);
        return (strpos($url_none_http, $domain_none_http) === false) ? false : true;
    }

    public function removeHttp($url) {
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
            if(strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }
        return $url;
    }

    public function convertUrlToDownload($url, $domain)
    {
        if(!$this->checkUrlSame($domain, $url)){
            return null;
        }
        $domain_none_http = $this->removeHttp($domain);
        $url_none_http = $this->removeHttp($url);
        $url_path = str_replace($domain_none_http, '', $url_none_http);
        $url_host = str_replace($url_path, '', $url);
        return array(
            'domain' => rtrim($url_host, '/'),
            'path' => ltrim($url_path, '/'),
        );
    }
}