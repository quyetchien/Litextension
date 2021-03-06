<?php

/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */
class LitExtension_CartMigration_Model_Cart_Loadedcommercev7 extends LitExtension_CartMigration_Model_Cart {

    public function __construct() {
        parent::__construct();
    }

    public function checkRecent()
    {
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'taxes' => "SELECT COUNT(1) FROM _DBPRF_tax_rates WHERE tax_rates_id > {$this->_notice['taxes']['id_src']}",
                'manufacturers' => "SELECT COUNT(1) FROM _DBPRF_manufacturers WHERE manufacturers_id > {$this->_notice['manufacturers']['id_src']}",
                'categories' => "SELECT COUNT(1) FROM _DBPRF_categories WHERE categories_mode = 'category' AND categories_id > {$this->_notice['categories']['id_src']}",
                'products' => "SELECT COUNT(1) FROM _DBPRF_products WHERE products_id > {$this->_notice['products']['id_src']}",
                'customers' => "SELECT COUNT(1) FROM _DBPRF_customers WHERE customers_id > {$this->_notice['customers']['id_src']}",
                'orders' => "SELECT COUNT(1) FROM _DBPRF_orders WHERE orders_id > {$this->_notice['orders']['id_src']}",
                'reviews' => "SELECT COUNT(1) FROM _DBPRF_reviews WHERE reviews_id > {$this->_notice['reviews']['id_src']}"
            ))
        ));
        if(!$data || $data['result'] != 'success'){
            return $this;
        }
        foreach($data['object'] as $type => $row){
            $count = $this->arrayToCount($row);
            $this->_notice[$type]['new'] = $count;
        }
        return $this;
    }

    /**
     * Process and get data use for config display
     *
     * @return array : Response as success or error with msg
     */
    public function displayConfig() {
        $response = array();
        $default_cfg = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                "languages" => "SELECT cfg.*, lg.* FROM _DBPRF_configuration AS cfg LEFT JOIN _DBPRF_languages AS lg ON lg.code = cfg.configuration_value WHERE cfg.configuration_key = 'DEFAULT_LANGUAGE'",
                "currencies" => "SELECT cfg.*, cur.* FROM _DBPRF_configuration AS cfg LEFT JOIN _DBPRF_currencies AS cur ON cur.code = cfg.configuration_value WHERE cfg.configuration_key = 'DEFAULT_CURRENCY'"
            ))
        ));
        if (!$default_cfg || $default_cfg['result'] != 'success') {
            return $this->errorConnector();
        }
        $object = $default_cfg['object'];
        if ($object && $object['languages'] && $object['currencies']) {
            $this->_notice['config']['default_lang'] = isset($object['languages']['0']['languages_id']) ? $object['languages']['0']['languages_id'] : 1;
            $this->_notice['config']['default_currency'] = isset($object['currencies']['0']['currencies_id']) ? $object['currencies']['0']['currencies_id'] : 1;
        }
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            "serialize" => true,
            "query" => serialize(array(
                "languages" => "SELECT * FROM _DBPRF_languages",
                "currencies" => "SELECT * FROM _DBPRF_currencies",
                "orders_status" => "SELECT * FROM _DBPRF_orders_status WHERE language_id = '{$this->_notice['config']['default_lang']}'",
                "customers_groups" => "SELECT * FROM _DBPRF_customers_groups WHERE language_id = '{$this->_notice['config']['default_lang']}'"
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector();
        }
        $obj = $data['object'];
        $language_data = $currency_data = $order_status_data = $category_data = $attribute_data = $customer_group_data = array();
        $category_data = array("Default Root Category");
        $attribute_data = array("Default Attribute Set");
        foreach ($obj['languages'] as $language_row) {
            $lang_id = $language_row['languages_id'];
            $lang_name = $language_row['name'] . "(" . $language_row['code'] . ")";
            $language_data[$lang_id] = $lang_name;
        }
        foreach ($obj['currencies'] as $currency_row) {
            $currency_id = $currency_row['currencies_id'];
            $currency_name = $currency_row['title'];
            $currency_data[$currency_id] = $currency_name;
        }
        foreach ($obj['orders_status'] as $order_status_row) {
            $order_status_id = $order_status_row['orders_status_id'];
            $order_status_name = $order_status_row['orders_status_name'];
            $order_status_data[$order_status_id] = $order_status_name;
        }
        foreach($obj['customers_groups'] as $cus_status_row){
            $cus_status_id = $cus_status_row['customers_group_id'];
            $cus_status_name = $cus_status_row['customers_group_name'];
            $customer_group_data[$cus_status_id] = $cus_status_name;
        }
        $this->_notice['config']['category_data'] = $category_data;
        $this->_notice['config']['attribute_data'] = $attribute_data;
        $this->_notice['config']['languages_data'] = $language_data;
        $this->_notice['config']['currencies_data'] = $currency_data;
        $this->_notice['config']['order_status_data'] = $order_status_data;
        $this->_notice['config']['customer_group_data'] = $customer_group_data;
        $response['result'] = 'success';
        return $response;
    }

    /**
     * Save config of use in config step to notice
     */
    public function displayConfirm($params) {
        parent::displayConfirm($params);
        return array(
            'result' => 'success'
        );
    }

    /**
     * Get data for import display
     *
     * @return array : Response as success or error with msg
     */
    public function displayImport() {
        $recent = $this->getRecentNotice();
        if ($recent) {
            $types = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews');
            foreach ($types as $type) {
                if ($this->_notice['config']['add_option']['add_new'] || !$this->_notice['config']['import'][$type]) {
                    $this->_notice[$type]['id_src'] = $recent[$type]['id_src'];
                    $this->_notice[$type]['imported'] = 0;
                    $this->_notice[$type]['error'] = 0;
                    $this->_notice[$type]['point'] = 0;
                    $this->_notice[$type]['finish'] = 0;
                }
            }
        }
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'taxes' => "SELECT COUNT(1) FROM _DBPRF_tax_rates WHERE tax_rates_id > {$this->_notice['taxes']['id_src']}",
                'manufacturers' => "SELECT COUNT(1) FROM _DBPRF_manufacturers WHERE manufacturers_id > {$this->_notice['manufacturers']['id_src']}",
                'categories' => "SELECT COUNT(1) FROM _DBPRF_categories WHERE categories_mode = 'category' AND categories_id > {$this->_notice['categories']['id_src']}",
                'products' => "SELECT COUNT(1) FROM _DBPRF_products WHERE products_id > {$this->_notice['products']['id_src']}",
                'customers' => "SELECT COUNT(1) FROM _DBPRF_customers WHERE customers_id > {$this->_notice['customers']['id_src']}",
                'orders' => "SELECT COUNT(1) FROM _DBPRF_orders WHERE orders_id > {$this->_notice['orders']['id_src']}",
                'reviews' => "SELECT COUNT(1) FROM _DBPRF_reviews WHERE reviews_id > {$this->_notice['reviews']['id_src']}"
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector();
        }
        $totals = array();
        foreach ($data['object'] as $type => $row) {
            $count = $this->arrayToCount($row);
            $totals[$type] = $count;
        }
        $iTotal = $this->_limitDemoModel($totals);
        foreach ($iTotal as $type => $total) {
            $this->_notice[$type]['total'] = $total;
        }
        $this->_notice['taxes']['time_start'] = time();
        if (!$this->_notice['config']['add_option']['add_new']) {
            $delete = $this->_deleteLeCaMgImport($this->_notice['config']['cart_url']);
            if (!$delete) {
                return $this->errorDatabase(true);
            }
        }
        return array(
            'result' => 'success'
        );
    }

    /**
     * Config currency
     */
    public function configCurrency() {
        parent::configCurrency();
        $allowCur = $this->_notice['config']['currencies'];
        $allow_cur = implode(',', $allowCur);
        $this->_process->currencyAllow($allow_cur);
        $default_cur = $this->_notice['config']['currencies'][$this->_notice['config']['default_currency']];
        $this->_process->currencyDefault($default_cur);
        $currencies = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => "SELECT * FROM _DBPRF_currencies"
        ));
        if ($currencies && $currencies['result'] == 'success') {
            $data = array();
            foreach ($currencies['object'] as $currency) {
                $currency_id = $currency['currencies_id'];
                $currency_value = $currency['value'];
                $currency_mage = $this->_notice['config']['currencies'][$currency_id];
                $data[$currency_mage] = $currency_value;
            }
            $this->_process->currencyRate(array(
                $default_cur => $data
            ));
        }
        return;
    }

    /**
     * Process before import taxes
     */
    public function prepareImportTaxes() {
        parent::prepareImportTaxes();
        $tax_cus = $this->getTaxCustomerDefault();
        if ($tax_cus['result'] == 'success') {
            $this->taxCustomerSuccess(1, $tax_cus['mage_id']);
        }
    }

    /**
     * Query for get data of table convert to tax rule
     *
     * @return string
     */
    protected function _getTaxesMainQuery() {
        $id_src = $this->_notice['taxes']['id_src'];
        $limit = $this->_notice['setting']['taxes'];
        $query = "SELECT * FROM _DBPRF_tax_rates WHERE tax_rates_id > {$id_src} ORDER BY tax_rates_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import tax rule
     *
     * @param array $taxes : Data of connector return for query in function getTaxesMainQuery
     * @return array
     */
    protected function _getTaxesExtQuery($taxes) {
        $taxClassIds = $this->duplicateFieldValueFromList($taxes['object'], 'tax_class_id');
        $tax_class_id_con = $this->arrayToInCondition($taxClassIds);
        $taxZoneIds = $this->duplicateFieldValueFromList($taxes['object'], 'tax_zone_id');
        $tax_zone_query = $this->arrayToInCondition($taxZoneIds);
        $ext_query = array(
            'tax_class' => "SELECT * FROM _DBPRF_tax_class WHERE tax_class_id IN {$tax_class_id_con}",
            'zones_to_geo_zones' => "SELECT gz.*, ztgz.*, z.zone_name, c.*
                                              FROM _DBPRF_geo_zones AS gz
                                                  LEFT JOIN _DBPRF_zones_to_geo_zones AS ztgz ON ztgz.geo_zone_id = gz.geo_zone_id
                                                  LEFT JOIN _DBPRF_zones AS z ON z.zone_id = ztgz.zone_id
                                                  LEFT JOIN _DBPRF_countries AS c ON c.countries_id = ztgz.zone_country_id
                                              WHERE gz.geo_zone_id IN {$tax_zone_query}"
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import tax rule
     *
     * @param array $taxes : Data of connector return for query in function getTaxesMainQuery
     * @param array $taxesExt : Data of connector return for query in function getTaxesExtQuery
     * @return array
     */
    protected function _getTaxesExtRelQuery($taxes, $taxesExt) {
        return array();
    }

    /**
     * Get primary key of main tax table
     *
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return int
     */
    public function getTaxId($tax, $taxesExt) {
        return $tax['tax_rates_id'];
    }

    /**
     * Convert source data to data for import
     *
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return array
     */
    public function convertTax($tax, $taxesExt) {
        if (LitExtension_CartMigration_Model_Custom::TAX_CONVERT) {
            return $this->_custom->convertTaxCustom($this, $tax, $taxesExt);
        }
        $tax_cus_ids = $tax_pro_ids = $tax_rate_ids = array();
        if ($tax_cus_default = $this->getMageIdTaxCustomer(1)) {
            $tax_cus_ids[] = $tax_cus_default;
        }
        $taxClass = $this->getRowFromListByField($taxesExt['object']['tax_class'], 'tax_class_id', $tax['tax_class_id']);
        $tax_pro_data = array(
            'class_name' => $taxClass['tax_class_title']
        );
        $tax_pro_ipt = $this->_process->taxProduct($tax_pro_data);
        if ($tax_pro_ipt['result'] == 'success') {
            $tax_pro_ids[] = $tax_pro_ipt['mage_id'];
            $this->taxProductSuccess($taxClass['tax_class_id'], $tax_pro_ipt['mage_id']);
        }
        $taxZone = $this->getListFromListByField($taxesExt['object']['zones_to_geo_zones'], 'geo_zone_id', $tax['tax_zone_id']);
        foreach ($taxZone as $tax_zone) {
            if (!$tax_zone['countries_iso_code_2']) {
                continue;
            }
            $tax_rate_data = array();
            $zone = $tax_zone['zone_name'];
            if ($tax_zone['zone_id'] == 0) {
                $zone = 'All States';
            }
            $code = $tax_zone['countries_iso_code_2'] . "-" . $zone;
            $tax_rate_data['code'] = $this->createTaxRateCode($code);
            $tax_rate_data['tax_country_id'] = $tax_zone['countries_iso_code_2'];
            if ($tax_zone['zone_id'] == 0) {
                $tax_rate_data['tax_region_id'] = 0;
            } else {
                $tax_rate_data['tax_region_id'] = $this->getRegionId($tax_zone['zone_name'], $tax_zone['countries_iso_code_2']);
            }
            $tax_rate_data['zip_is_range'] = 0;
            $tax_rate_data['tax_postcode'] = "*";
            $tax_rate_data['rate'] = $tax['tax_rate'];
            $tax_rate_ipt = $this->_process->taxRate($tax_rate_data);
            if ($tax_rate_ipt['result'] == 'success') {
                $tax_rate_ids[] = $tax_rate_ipt['mage_id'];
            }
        }
        $tax_rule_data = array();
        $tax_rule_data['code'] = $this->createTaxRuleCode($taxClass['tax_class_title'] . " - " . $tax['tax_description']);
        $tax_rule_data['tax_customer_class'] = $tax_cus_ids;
        $tax_rule_data['tax_product_class'] = $tax_pro_ids;
        $tax_rule_data['tax_rate'] = $tax_rate_ids;
        $tax_rule_data['priority'] = 0;
        $tax_rule_data['position'] = 0;
        $custom = $this->_custom->convertTaxCustom($this, $tax, $taxesExt);
        if ($custom) {
            $tax_rule_data = array_merge($tax_rule_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $tax_rule_data
        );
    }

    /**
     * Process before import manufacturers
     */
    public function prepareImportManufacturers() {
        parent::prepareImportManufacturers();
        $man_attr = $this->getManufacturerAttributeId($this->_notice['config']['attribute_set_id']);
        if ($man_attr['result'] == 'success') {
            $this->manAttrSuccess(1, $man_attr['mage_id']);
        }
    }

    /**
     * Query for get data for convert to manufacturer option
     *
     * @return string
     */
    protected function _getManufacturersMainQuery() {
        $id_src = $this->_notice['manufacturers']['id_src'];
        $limit = $this->_notice['setting']['manufacturers'];
        $query = "SELECT * FROM _DBPRF_manufacturers WHERE manufacturers_id > {$id_src} ORDER BY manufacturers_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Get data relation use for import manufacturer
     *
     * @param array $manufacturers : Data of connector return for query function getManufacturersMainQuery
     * @return array
     */
    protected function _getManufacturersExtQuery($manufacturers) {
        return array();
    }

    /**
     * Get data relation use for import manufacturer
     *
     * @param array $manufacturers : Data of connector return for query function getManufacturersMainQuery
     * @param array $manufacturersExt : Data of connector return for query function getManufacturersExtQuery
     * @return array
     */
    protected function _getManufacturersExtRelQuery($manufacturers, $manufacturersExt) {
        return array();
    }

    /**
     * Get primary key of source manufacturer
     *
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return int
     */
    public function getManufacturerId($manufacturer, $manufacturersExt) {
        return $manufacturer['manufacturers_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return array
     */
    public function convertManufacturer($manufacturer, $manufacturersExt) {
        if (LitExtension_CartMigration_Model_Custom::MANUFACTURER_CONVERT) {
            return $this->_custom->convertManufacturerCustom($this, $manufacturer, $manufacturersExt);
        }
        $man_attr_id = $this->getMageIdManAttr(1);
        if (!$man_attr_id) {
            return array(
                'result' => 'error',
                'msg' => $this->consoleError("Could not create manufacturer attribute!")
            );
        }
        $manufacturer_data = array(
            'attribute_id' => $man_attr_id
        );
        $manufacturer_data['value']['option'] = array(
            0 => $manufacturer['manufacturers_name']
        );
        foreach ($this->_notice['config']['languages'] as $store_id) {
            $manufacturer['value']['option'][$store_id] = $manufacturer['manufacturers_name'];
        }
        $custom = $this->_custom->convertManufacturerCustom($this, $manufacturer, $manufacturersExt);
        if ($custom) {
            $manufacturer_data = array_merge($manufacturer_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $manufacturer_data
        );
    }

    /**
     * Query for get data of main table use import category
     *
     * @return string
     */
    protected function _getCategoriesMainQuery() {
        $id_src = $this->_notice['categories']['id_src'];
        $limit = $this->_notice['setting']['categories'];
        $query = "SELECT * FROM _DBPRF_categories WHERE categories_id > {$id_src} AND categories_mode = 'category' ORDER BY categories_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import categories
     *
     * @param array $categories : Data of connector return for query function getCategoriesMainQuery
     * @return array
     */
    protected function _getCategoriesExtQuery($categories) {
        $categoryIds = $this->duplicateFieldValueFromList($categories['object'], 'categories_id');
        $cat_id_con = $this->arrayToInCondition($categoryIds);
        $ext_query = array(
            'categories_description' => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$cat_id_con}"
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import categories
     *
     * @param array $categories : Data of connector return for query function getCategoriesMainQuery
     * @param array $categoriesExt : Data of connector return for query function getCategoriesExtQuery
     * @return array
     */
    protected function _getCategoriesExtRelQuery($categories, $categoriesExt) {
        return array();
    }

    /**
     * Get primary key of source category
     *
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return int
     */
    public function getCategoryId($category, $categoriesExt) {
        return $category['categories_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return array
     */
    public function convertCategory($category, $categoriesExt) {
        if (LitExtension_CartMigration_Model_Custom::CATEGORY_CONVERT) {
            return $this->_custom->convertCategoryCustom($this, $category, $categoriesExt);
        }
        if ($category['parent_id'] == 0 || $category['parent_id'] == $category['categories_id']) {
            $cat_parent_id = $this->_notice['config']['root_category_id'];
        } else {
            $cat_parent_id = $this->getMageIdCategory($category['parent_id']);
            if (!$cat_parent_id) {
                $parent_ipt = $this->_importCategoryParent($category['parent_id']);
                if ($parent_ipt['result'] == 'error') {
                    return $parent_ipt;
                } else if ($parent_ipt['result'] == 'warning') {
                    return array(
                        'result' => 'warning',
                        'msg' => $this->consoleWarning("Category Id = {$category['categories_id']} import failed. Error: Could not import parent category id = {$category['parent_id']}")
                    );
                } else {
                    $cat_parent_id = $parent_ipt['mage_id'];
                }
            }
        }
        $cat_data = array();
        $catDesc = $this->getListFromListByField($categoriesExt['object']['categories_description'], 'categories_id', $category['categories_id']);
        $cat_lang = $this->getRowFromListByField($catDesc, 'language_id', $this->_notice['config']['default_lang']);
        $cat_data['name'] = $cat_lang['categories_name'] ? $cat_lang['categories_name'] : " ";
        $cat_data['description'] = $cat_lang['categories_description'];
        $cat_data['meta_keywords'] = $cat_lang['categories_keyword'];
        if ($category['categories_image'] && $img_path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_category']), $category['categories_image'], 'catalog/category')) {
            $cat_data['image'] = $img_path;
        }
        $pCat = Mage::getModel('catalog/category')->load($cat_parent_id);
        $cat_data['path'] = $pCat->getPath();
        $cat_data['is_active'] = $category['categories_status'];
        $cat_data['is_anchor'] = 0;
        $cat_data['include_in_menu'] = 1;
        $cat_data['display_mode'] = Mage_Catalog_Model_Category::DM_PRODUCT;
        $multi_store = array();
        foreach ($this->_notice['config']['languages'] as $lang_id => $store_id) {
            $store_data = array();
            $store_lang = $this->getRowFromListByField($catDesc, 'language_id', $lang_id);
            if ($lang_id != $this->_notice['config']['default_lang'] && $store_lang) {
                $store_data['store_id'] = $store_id;
                $store_data['name'] = $store_lang['categories_name'];
                $store_data['description'] = $store_lang['categories_description'];
                $store_data['meta_keywords'] = $store_lang['categories_keyword'];
                $multi_store[] = $store_data;
            }
        }
        $cat_data['multi_store'] = $multi_store;
        if ($this->_seo) {
            $seo = $this->_seo->convertCategorySeo($this, $category, $categoriesExt);
            if ($seo) {
                $cat_data['seo_url'] = $seo;
            }
        }
        $custom = $this->_custom->convertCategoryCustom($this, $category, $categoriesExt);
        if ($custom) {
            $cat_data = array_merge($cat_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $cat_data
        );
    }

    /**
     * Process before import products
     */
    public function prepareImportProducts() {
        parent::prepareImportProducts();
        $this->_notice['extend']['website_ids'] = $this->getWebsiteIdsByStoreIds($this->_notice['config']['languages']);
    }

    /**
     * Query for get data of main table use for import product
     *
     * @return string
     */
    protected function _getProductsMainQuery() {
        $id_src = $this->_notice['products']['id_src'];
        $limit = $this->_notice['setting']['products'];
        $query = "SELECT * FROM _DBPRF_products WHERE products_id > {$id_src} ORDER BY products_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import product
     *
     * @param array $products : Data of connector return for query function getProductsMainQuery
     * @return array
     */
    protected function _getProductsExtQuery($products) {
        $productIds = $this->duplicateFieldValueFromList($products['object'], 'products_id');
        $pro_ids_query = $this->arrayToInCondition($productIds);
        $ext_query = array(
            'products_description' => "SELECT * FROM _DBPRF_products_description WHERE products_id IN {$pro_ids_query}",
            'products_simple_options' => "SELECT * FROM _DBPRF_products_simple_options WHERE products_id IN {$pro_ids_query}",
            'products_to_categories' => "SELECT * FROM _DBPRF_products_to_categories WHERE products_id IN {$pro_ids_query}",
            'products_images' => "SELECT * FROM _DBPRF_products_images WHERE products_id IN {$pro_ids_query}",
            'specials' => "SELECT * FROM _DBPRF_specials WHERE product_ids IN {$pro_ids_query} AND status = 1",
            'products_simple_options_values' => "SELECT * FROM _DBPRF_products_simple_options_values WHERE products_id IN {$pro_ids_query} AND customers_group_id = 1",
            'product_attributes' => "SELECT * FROM _DBPRF_product_attributes WHERE products_id IN {$pro_ids_query} AND id = 2 AND languages_id = {$this->_notice['config']['default_lang']}",
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import product
     *
     * @param array $products : Data of connector return for query function getProductsMainQuery
     * @param array $productsExt : Data of connector return for query function getProductsExtQuery
     * @return array
     */
    protected function _getProductsExtRelQuery($products, $productsExt) {
        $productOptionIds = $this->duplicateFieldValueFromList($productsExt['object']['products_simple_options_values'], 'options_id');
        $productOptionValueIds = $this->duplicateFieldValueFromList($productsExt['object']['products_simple_options_values'], 'values_id');
        $product_option_ids_query = $this->arrayToInCondition($productOptionIds);
        $product_option_value_ids_query = $this->arrayToInCondition($productOptionValueIds);
        $ext_rel_query = array(
            'products_variants_groups' => "SELECT * FROM _DBPRF_products_variants_groups WHERE id IN {$product_option_ids_query}",
            'products_variants_values' => "SELECT * FROM _DBPRF_products_variants_values WHERE id IN {$product_option_value_ids_query}",
        );
        return $ext_rel_query;
    }

    /**
     * Get primary key of source product main
     *
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsMain
     * @return int
     */
    public function getProductId($product, $productsExt) {
        return $product['products_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsMain
     * @return array
     */
    public function convertProduct($product, $productsExt) {
        if (LitExtension_CartMigration_Model_Custom::PRODUCT_CONVERT) {
            return $this->_custom->convertProductCustom($this, $product, $productsExt);
        }
        $pro_data = array();
        $categories = array();
        $proCat = $this->getListFromListByField($productsExt['object']['products_to_categories'], 'products_id', $product['products_id']);
        if ($proCat) {
            foreach ($proCat as $pro_cat) {
                $cat_id = $this->getMageIdCategory($pro_cat['categories_id']);
                if ($cat_id) {
                    $categories[] = $cat_id;
                }
            }
        }
        $proDesc = $this->getListFromListByField($productsExt['object']['products_description'], 'products_id', $product['products_id']);
        $pro_desc_def = $this->getRowFromListByField($proDesc, 'language_id', $this->_notice['config']['default_lang']);
        $pro_data['type_id'] = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
        $pro_data['website_ids'] = $this->_notice['extend']['website_ids'];
        $pro_data['store_ids'] = array_values($this->_notice['config']['languages']);
        $pro_data['attribute_set_id'] = $this->_notice['config']['attribute_set_id'];
        $pro_data['category_ids'] = $categories;
        if($product['products_sku']) {
            $pro_data['sku'] = $this->createProductSku($product['products_sku'], $this->_notice['config']['languages']);
        } else {
            $pro_data['sku'] = $this->createProductSku($product['products_model'], $this->_notice['config']['languages']);
        }
        $pro_data['name'] = $pro_desc_def['products_name'];
        $pro_data['description'] = $this->changeImgSrcInText($pro_desc_def['products_description'], $this->_notice['config']['add_option']['img_des']);
        $pro_data['short_description'] = $this->changeImgSrcInText($pro_desc_def['products_description'], $this->_notice['config']['add_option']['img_des']);
        $pro_data['tags'] = $pro_desc_def['products_tags'];
        $pro_data['price'] = $product['products_price'] ? $product['products_price'] : 0;
        $proSpecial = $this->getRowFromListByField($productsExt['object']['specials'], 'products_id', $product['products_id']);
        if ($proSpecial) {
            $pro_data['special_price'] = $proSpecial['specials_new_products_price'];
            $pro_data['special_from_date'] = $proSpecial['start_date'];
            $pro_data['special_to_date'] = $proSpecial['expires_date'];
        }
        $pro_data['weight'] = $product['products_weight'] ? $product['products_weight'] : 0;
        $pro_data['status'] = ($product['products_status'] == 1) ? 1 : 2;
        if ($product['products_tax_class_id'] != 0 && $tax_pro_id = $this->getMageIdTaxProduct($product['products_tax_class_id'])) {
            $pro_data['tax_class_id'] = $tax_pro_id;
        } else {
            $pro_data['tax_class_id'] = 0;
        }
        $pro_data['created_at'] = $product['products_date_added'];
        $pro_data['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
        $pro_data['stock_data'] = array(
            'is_in_stock' => 1,
            'manage_stock' => ($this->_notice['config']['add_option']['stock'] && $product['products_quantity'] < 1) ? 0 : 1,
            'use_config_manage_stock' => ($this->_notice['config']['add_option']['stock'] && $product['products_quantity'] < 1) ? 0 : 1,
            'qty' => $product['products_quantity']
        );
        $manu_id = $this->getRowValueFromListByField($productsExt['object']['product_attributes'], 'products_id', $product['products_id'], 'value');
        if ($manu_id && $manufacture_mage_id = $this->getMageIdManufacturer($manu_id)) {
            $pro_data[self::MANUFACTURER_CODE] = $manufacture_mage_id;
        }
        $proImg = $this->getListFromListByField($productsExt['object']['products_images'], 'products_id', $product['products_id']);
        $default_image = $this->getRowValueFromListByField($proImg, 'default_flag', '1', 'image');
        if ($default_image && $image_path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $default_image, 'catalog/product', false, true)) {
            $pro_data['image_import_path'] = array('path' => $image_path, 'label' => '');
        }
        if ($proImg) {
            foreach ($proImg as $gallery) {
                if ($gallery['default_flag'] == '1') {
                    continue;
                }
                if ($gallery_path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $gallery['image'], 'catalog/product', false, true)) {
                    $pro_data['image_gallery'][] = array('path' => $gallery_path, 'label' => '');
                }
            }
        }
        $pro_data['meta_title'] = $pro_desc_def['products_meta_title'] ? $pro_desc_def['products_meta_title'] : ' ';
        $pro_data['meta_keyword'] = $pro_desc_def['products_meta_keywords'] ? $pro_desc_def['products_meta_keywords'] : ' ';
        $pro_data['meta_description'] = $pro_desc_def['products_meta_description'] ? $pro_desc_def['products_meta_description'] : ' ';
        $multi_store = array();
        foreach ($this->_notice['config']['languages'] as $lang_id => $store_id) {
            if ($lang_id != $this->_notice['config']['default_lang'] && $store_data_change = $this->getRowFromListByField($proDesc, 'language_id', $lang_id)) {
                $store_data = array();
                $store_data['name'] = $store_data_change['products_name'];
                $store_data['description'] = $this->changeImgSrcInText($store_data_change['products_description'], $this->_notice['config']['add_option']['img_des']);
                $store_data['short_description'] = $this->changeImgSrcInText($store_data_change['products_description'], $this->_notice['config']['add_option']['img_des']);
                $store_data['meta_title'] = $store_data_change['products_meta_title'];
                $store_data['meta_keyword'] = $store_data_change['products_meta_keywords'];
                $store_data['meta_description'] = $store_data_change['products_meta_description'];
                $store_data['store_id'] = $store_id;
                $multi_store[] = $store_data;
            }
        }
        $pro_data['multi_store'] = $multi_store;
        if ($this->_seo) {
            $seo = $this->_seo->convertProductSeo($this, $product, $productsExt);
            if ($seo) {
                $pro_data['seo_url'] = $seo;
            }
        }
        $custom = $this->_custom->convertProductCustom($this, $product, $productsExt);
        if ($custom) {
            $pro_data = array_merge($pro_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $pro_data
        );
    }

    /**
     * Process after one product import successful
     *
     * @param int $product_mage_id : Id of product save successful to magento
     * @param array $data : Data of function convertProduct
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsExt
     * @return boolean
     */
    public function afterSaveProduct($product_mage_id, $data, $product, $productsExt) {
        if (parent::afterSaveProduct($product_mage_id, $data, $product, $productsExt)) {
            return;
        }
        $proAttr = $this->getListFromListByField($productsExt['object']['products_simple_options'], 'products_id', $product['products_id']);
        $proOption = $this->getListFromListByField($productsExt['object']['products_simple_options_values'], 'products_id', $product['products_id']);
        if ($proAttr) {
            $opt_data = array();
            $opt_data_store = array();
            $proOptId = $this->duplicateFieldValueFromList($proAttr, 'options_id');
            foreach ($proOptId as $pro_opt_id) {
                $proOpt = $this->getListFromListByField($productsExt['object']['products_variants_groups'], 'id', $pro_opt_id);
                $proOptVal = $this->getListFromListByField($proOption, 'options_id', $pro_opt_id);
                if (!$proOpt) {
                    continue;
                }
                $option = array(
                    'previous_group' => Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT,
                    'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN,
                    'is_require' => 1,
                    'title' => $this->getRowValueFromListByField($proOpt, 'languages_id', $this->_notice['config']['default_lang'], 'title')
                );
                foreach ($this->_notice['config']['languages_data'] as $lang_id => $lang_name) {
                    if ($lang_id == $this->_notice['config']['default_lang']) {
                        continue;
                    }
                    $option_store[$lang_id] = array(
                        'previous_group' => Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT,
                        'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN,
                        'is_require' => 1,
                        'title' => $this->getRowValueFromListByField($proOpt, 'languages_id', $lang_id, 'title')
                    );
                }
                $values = array();
                $value_stores = array();
                if($proOptVal) {
                    foreach ($proOptVal as $pro_opt_val) {
                        $proVal = $this->getListFromListByField($productsExt['object']['products_variants_values'], 'id', $pro_opt_val['values_id']);
                        $value = array(
                            'option_type_id' => -1,
                            'title' => $this->getRowValueFromListByField($proVal, 'languages_id', $this->_notice['config']['default_lang'], 'title'),
                            'price' => $pro_opt_val['price_modifier'],
                            'price_type' => 'fixed',
                        );
                        $values[] = $value;
                        foreach ($this->_notice['config']['languages_data'] as $lang_id => $lang_name) {
                            if ($lang_id == $this->_notice['config']['default_lang']) {
                                continue;
                            }
                            $value_store = array(
                                'option_type_id' => -1,
                                'title' => $this->getRowValueFromListByField($proVal, 'languages_id', $lang_id, 'title'),
                                'price' => $pro_opt_val['price_modifier'],
                                'price_type' => 'fixed',
                            );
                            $value_stores[$lang_id][] = $value_store;
                        }
                    }
                }
                foreach ($this->_notice['config']['languages_data'] as $lang_id => $lang_name) {
                    if ($lang_id == $this->_notice['config']['default_lang']) {
                        continue;
                    }
                    $option_store[$lang_id]['values'] = (isset($value_stores[$lang_id])) ? $value_stores[$lang_id] : '';
                    $opt_data_store[$lang_id][] = $option_store[$lang_id];
                }
                $option['values'] = (isset($values)) ? $values : '';
                $opt_data[] = $option;
            }
            $this->importProductOption($product_mage_id, $opt_data);
            if (count($this->_notice['config']['languages']) > 1) {
                foreach ($this->_notice['config']['languages'] as $key => $val) {
                    if($key == $this->_notice['config']['default_lang']) {continue;}
                                $this->_updateProductOptionStoreView($product_mage_id, $opt_data_store[$key], $opt_data, $val);
                }
            }
        }
    }

    /**
     * Query for get data of main table use for import customer
     *
     * @return string
     */
    protected function _getCustomersMainQuery() {
        $id_src = $this->_notice['customers']['id_src'];
        $limit = $this->_notice['setting']['customers'];
        $query = "SELECT * FROM _DBPRF_customers WHERE customers_id > {$id_src} ORDER BY customers_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import customer
     *
     * @param array $customers : Data of connector return for query function getCustomersMainQuery
     * @return array
     */
    protected function _getCustomersExtQuery($customers) {
        $customerIds = $this->duplicateFieldValueFromList($customers['object'], 'customers_id');
        $customer_ids_query = $this->arrayToInCondition($customerIds);
        $ext_query = array(
            'address' => "SELECT a.*, c.*, z.*
                                            FROM _DBPRF_address_book AS a
                                                LEFT JOIN _DBPRF_countries AS c ON a.entry_country_id = c.countries_id
                                                LEFT JOIN _DBPRF_zones AS z ON a.entry_zone_id = z.zone_id
                                            WHERE a.customers_id IN {$customer_ids_query}"
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import customer
     *
     * @param array $customers : Data of connector return for query function getCustomersMainQuery
     * @param array $customersExt : Data of connector return for query function getCustomersExtQuery
     * @return array
     */
    protected function _getCustomersExtRelQuery($customers, $customersExt) {
        return array();
    }

    /**
     * Get primary key of source customer main
     *
     * @param array $customer : One row of object in function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return int
     */
    public function getCustomerId($customer, $customersExt) {
        return $customer['customers_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $customer : One row of object in function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return array
     */
    public function convertCustomer($customer, $customersExt) {
        if (LitExtension_CartMigration_Model_Custom::CUSTOMER_CONVERT) {
            return $this->_custom->convertCustomerCustom($this, $customer, $customersExt);
        }
        $cus_data = array();
        if ($this->_notice['config']['add_option']['pre_cus']) {
            $cus_data['id'] = $customer['customers_id'];
        }
        $cus_data['website_id'] = $this->_notice['config']['website_id'];
        $cus_data['email'] = $customer['customers_email_address'];
        $cus_data['firstname'] = $customer['customers_firstname'];
        $cus_data['lastname'] = $customer['customers_lastname'];
        $cus_data['created_at'] = $customer['date_account_created'];
        $cus_data['is_subscribed'] = $customer['customers_newsletter'];
        $cus_data['group_id'] = isset($this->_notice['config']['customer_group'][$customer['customers_group_id']]) ? $this->_notice['config']['customer_group'][$customer['customers_group_id']] : 1;
        $custom = $this->_custom->convertCustomerCustom($this, $customer, $customersExt);
        if ($custom) {
            $cus_data = array_merge($cus_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $cus_data
        );
    }

    /**
     * Process after one customer import successful
     *
     * @param int $customer_mage_id : Id of customer import to magento
     * @param array $data : Data of function convertCustomer
     * @param array $customer : One row of object function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return boolean
     */
    public function afterSaveCustomer($customer_mage_id, $data, $customer, $customersExt) {
        if (parent::afterSaveCustomer($customer_mage_id, $data, $customer, $customersExt)) {
            return;
        }
        $this->_importCustomerRawPass($customer_mage_id, $customer['customers_password']);
        $cusAdd = $this->getListFromListByField($customersExt['object']['address'], 'customers_id', $customer['customers_id']);
        if ($cusAdd) {
            foreach ($cusAdd as $cus_add) {
                $address = array();
                $address['firstname'] = $cus_add['entry_firstname'];
                $address['lastname'] = $cus_add['entry_lastname'];
                $address['country_id'] = $cus_add['countries_iso_code_2'];
                $address['street'] = $cus_add['entry_street_address'];
                $address['postcode'] = $cus_add['entry_postcode'];
                $address['city'] = $cus_add['entry_city'];
                $address['telephone'] = $cus_add['entry_telephone'];
                $address['company'] = $cus_add['entry_company'];
                $address['fax'] = $cus_add['entry_fax'];
                if ($cus_add['zone_id'] != 0) {
                    $region_id = $this->getRegionId($cus_add['zone_name'], $cus_add['countries_iso_code_2']);
                    if ($region_id) {
                        $address['region_id'] = $region_id;
                    }
                    $address['region'] = $cus_add['zone_name'];
                } else {
                    $address['region'] = $cus_add['entry_street_address'];
                }
                $address_ipt = $this->_process->address($address, $customer_mage_id);
                if ($address_ipt['result'] == 'success' && $cus_add['address_book_id'] == $customer['customers_default_address_id']) {
                    try {
                        $cus = Mage::getModel('customer/customer')->load($customer_mage_id);
                        $cus->setDefaultBilling($address_ipt['mage_id']);
                        $cus->setDefaultShipping($address_ipt['mage_id']);
                        $cus->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }

    /**
     * Get data use for import order
     *
     * @return array : Response of connector
     */
    protected function _getOrdersMainQuery() {
        $id_src = $this->_notice['orders']['id_src'];
        $limit = $this->_notice['setting']['orders'];
        $query = "SELECT * FROM _DBPRF_orders WHERE orders_id > {$id_src} ORDER BY orders_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Get data relation use for import order
     *
     * @param array $orders : Data of function getOrdersMain
     * @return array : Response of connector
     */
    protected function _getOrdersExtQuery($orders) {
        $orderIds = $this->duplicateFieldValueFromList($orders['object'], 'orders_id');
        $order_ids_query = $this->arrayToInCondition($orderIds);
        $ext_query = array(
            'orders_products' => "SELECT * FROM _DBPRF_orders_products WHERE orders_id IN {$order_ids_query}",
            'orders_status_history' => "SELECT *  FROM _DBPRF_orders_status_history WHERE orders_id IN {$order_ids_query} ORDER BY orders_status_history_id DESC",
            'orders_total' => "SELECT * FROM _DBPRF_orders_total WHERE orders_id IN {$order_ids_query}",
        );
        return $ext_query;
    }
    
    /**
     * Query for get data relation use for import order
     *
     * @param array $orders : Data of connector return for query function getOrdersMainQuery
     * @param array $ordersExt : Data of connector return for query function getOrdersExtQuery
     * @return array
     */
    protected function _getOrdersExtRelQuery($orders, $ordersExt){
        return array();
    }

    /**
     * Get primary key of source order main
     *
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return int
     */
    public function getOrderId($order, $ordersExt) {
        return $order['orders_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return array
     */
    public function convertOrder($order, $ordersExt) {
        if (LitExtension_CartMigration_Model_Custom::ORDER_CONVERT) {
            return $this->_custom->convertOrderCustom($this, $order, $ordersExt);
        }
        $data = array();
        
        $billing_name = $this->_getPartName($order['billing_name']);
        $address_billing['firstname'] = $billing_name['firstname'];
        $address_billing['lastname'] = $billing_name['lastname'];
        $address_billing['company'] = $order['billing_company'];
        $address_billing['street'] = $order['billing_street_address'];
        $address_billing['city'] = $order['billing_city'];
        $address_billing['postcode'] = $order['billing_postcode'];
        $address_billing['country_id'] = $order['billing_country_iso2'];
        $billing_region_id = $this->getRegionId($order['billing_state'], $address_billing['country_id']);
        if ($billing_region_id) {
            $address_billing['region_id'] = $billing_region_id;
        } else {
            $address_billing['region'] = $order['billing_state'];
        }
        
        $shipping_name = $this->_getPartName($order['delivery_name']);
        $address_shipping['firstname'] = $shipping_name['firstname'];
        $address_shipping['lastname'] = $shipping_name['lastname'];
        $address_shipping['company'] = $order['delivery_company'];
        $address_shipping['street'] = $order['delivery_street_address'];
        $address_shipping['city'] = $order['delivery_city'];
        $address_shipping['postcode'] = $order['delivery_postcode'];
        $address_shipping['country_id'] = $order['delivery_country_iso2'];
        $shipping_region_id = $this->getRegionId($order['delivery_state'], $address_shipping['country_id']);
        if ($shipping_region_id) {
            $address_shipping['region_id'] = $shipping_region_id;
        } else {
            $address_shipping['region'] = $order['delivery_state'];
        }

        $orderPro = $this->getListFromListByField($ordersExt['object']['orders_products'], 'orders_id', $order['orders_id']);
        $carts = array();
        $orderTotal = $this->getListFromListByField($ordersExt['object']['orders_total'], 'orders_id', $order['orders_id']);
        $ot_coupon = $this->getRowValueFromListByField($orderTotal, 'class', 'coupon', 'value');
        $discount_amount = 0;
        if ($ot_coupon) {
            $discount_amount -= $ot_coupon;
        }
        $discount = abs($discount_amount);
        foreach ($orderPro as $order_pro) {
            $cart = array();
            $product_id = $this->getMageIdProduct($order_pro['products_id']);
            if ($product_id) {
                $cart['product_id'] = $product_id;
            }
            $cart['type_id'] = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
            $cart['name'] = $order_pro['products_name'];
            $cart['sku'] = $order_pro['products_sku'];
            $cart['price'] = $order_pro['products_price'];
            $cart['original_price'] = $order_pro['products_price'];
            $cart['tax_amount'] = $order_pro['products_price'] * $order_pro['products_quantity'] * $order_pro['products_tax'] / 100;
            $cart['tax_percent'] = $order_pro['products_tax'];
            $cart['discount_amount'] = '';
            $cart['qty_ordered'] = $order_pro['products_quantity'];
            $cart['row_total'] = $order_pro['products_price'] * $order_pro['products_quantity'];
            $listOpt = unserialize($order_pro['products_simple_options_meta_data']);
            if ($listOpt) {
                $product_opt = array();
                $options = array();
                foreach ($listOpt as $list_opt) {
                    $option = array(
                        'label' => $list_opt['group_title'],
                        'value' => $list_opt['value_title'],
                        'print_value' => $list_opt['value_title'],
                        'option_id' => 'option_' . $list_opt['value_id'],
                        'option_type' => 'drop_down',
                        'option_value' => 0,
                        'custom_view' => false
                    );
                    $options[] = $option;
                }
                $product_opt = array('options' => $options);
                $cart['product_options'] = serialize($product_opt);
            }
            $carts[] = $cart;
        }

        $customer_id = $this->getMageIdCustomer($order['customers_id']);
        $orderStatus = $this->getListFromListByField($ordersExt['object']['orders_status_history'], 'orders_id', $order['orders_id']);
        $order_status = $orderStatus[0];
        $order_status_id = $order_status['orders_status_id'];
        $ot_shipping = $this->getRowValueFromListByField($orderTotal, 'class', 'shipping', 'value');
        $ot_shipping_desc = $this->getRowValueFromListByField($orderTotal, 'class', 'shipping', 'title');
        $ot_subtotal = $this->getRowValueFromListByField($orderTotal, 'class', 'sub_total', 'value');
        $ot_total = $this->getRowValueFromListByField($orderTotal, 'class', 'total', 'value');
        $ot_tax = $this->_getOrderTaxValueFromListByCode($orderTotal, 'tax');
        $store_id = $this->_notice['config']['languages'][$this->_notice['config']['default_lang']];
        $store_currency = $this->getStoreCurrencyCode($store_id);

        $order_data = array();
        $order_data['store_id'] = $store_id;
        if ($customer_id) {
            $order_data['customer_id'] = $customer_id;
            $order_data['customer_is_guest'] = false;
        } else {
            $order_data['customer_is_guest'] = true;
        }
        $customer_name = $this->_getPartName($order['customers_name']);
        $order_data['customer_email'] = $order['customers_email_address'];
        $order_data['customer_firstname'] = $customer_name['firstname'];
        $order_data['customer_lastname'] = $customer_name['lastname'];
        $order_data['customer_group_id'] = 1;
        if ($this->_notice['config']['order_status']) {
            $order_data['status'] = $this->_notice['config']['order_status'][$order_status_id];
            $order_data['state'] = $this->getOrderStateByStatus($order_data['status']);
        }
        $order_data['subtotal'] = $this->incrementPriceToImport($ot_subtotal);
        $order_data['base_subtotal'] = $order_data['subtotal'];
        $order_data['shipping_amount'] = $ot_shipping;
        $order_data['base_shipping_amount'] = $ot_shipping;
        $order_data['base_shipping_invoiced'] = $ot_shipping;
        $order_data['shipping_description'] = $ot_shipping_desc;
        if ($ot_tax) {
            $order_data['tax_amount'] = $ot_tax;
            $order_data['base_tax_amount'] = $ot_tax;
        }
        $order_data['discount_amount'] = $discount_amount;
        $order_data['base_discount_amount'] = $discount_amount;
        $order_data['grand_total'] = $this->incrementPriceToImport($ot_total);
        $order_data['base_grand_total'] = $order_data['grand_total'];
        $order_data['base_total_invoiced'] = $order_data['grand_total'];
        $order_data['total_paid'] = $order_data['grand_total'];
        $order_data['base_total_paid'] = $order_data['grand_total'];
        $order_data['base_to_global_rate'] = true;
        $order_data['base_to_order_rate'] = true;
        $order_data['store_to_base_rate'] = true;
        $order_data['store_to_order_rate'] = true;
        $order_data['base_currency_code'] = $store_currency['base'];
        $order_data['global_currency_code'] = $store_currency['base'];
        $order_data['store_currency_code'] = $store_currency['base'];
        $order_data['order_currency_code'] = $store_currency['base'];
        $order_data['created_at'] = $order['date_purchased'];

        $data['address_billing'] = $address_billing;
        $data['address_shipping'] = $address_shipping;
        $data['order'] = $order_data;
        $data['carts'] = $carts;
        $data['order_src_id'] = $order['orders_id'];
        $custom = $this->_custom->convertOrderCustom($this, $order, $ordersExt);
        if($custom){
            $data = array_merge($data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $data
        );
    }

    /**
     * Process after one order save successful
     *
     * @param int $order_mage_id : Id of order import to magento
     * @param array $data : Data of function convertOrder
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return boolean
     */
    public function afterSaveOrder($order_mage_id, $data, $order, $ordersExt) {
        if (parent::afterSaveOrder($order_mage_id, $data, $order, $ordersExt)) {
            return;
        }
        $orderStatus = $this->getListFromListByField($ordersExt['object']['orders_status_history'], 'orders_id', $order['orders_id']);
        $orderStatus = array_reverse($orderStatus);
        foreach ($orderStatus as $key => $order_status) {
            $order_status_data = array();
            $order_status_id = $order_status['orders_status_id'];
            $order_status_data['status'] = $this->_notice['config']['order_status'][$order_status_id];
            if ($order_status_data['status']) {
                $order_status_data['state'] = $this->getOrderStateByStatus($order_status_data['status']);
            }
            if ($key == 0) {
                $order_status_data['comment'] = "<b>Reference order #" . $order['orders_id'] . "</b><br /><b>Payment method: </b>" . $order['payment_method'] . "<br /><b>Shipping method: </b> " . $data['order']['shipping_description'] . "<br /><br />" . $order_status['comments'];
            } else {
                $order_status_data['comment'] = $order_status['comments'];
            }
            $order_status_data['is_customer_notified'] = ($order_status['administrators_id']) ? 0 : 1;
            $order_status_data['updated_at'] = $order_status['date_added'];
            $order_status_data['created_at'] = $order_status['date_added'];
            $this->_process->ordersComment($order_mage_id, $order_status_data);
        }
    }

    /**
     * Query for get main data use for import review
     *
     * @return string
     */
    protected function _getReviewsMainQuery() {
        $id_src = $this->_notice['reviews']['id_src'];
        $limit = $this->_notice['setting']['reviews'];
        $query = "SELECT * FROM _DBPRF_reviews WHERE reviews_id > {$id_src} ORDER BY reviews_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get relation data use for import reviews
     *
     * @param array $reviews : Data of connector return for query function getReviewsMainQuery
     * @return array
     */
    protected function _getReviewsExtQuery($reviews) {
        return array();
    }

    /**
     * Query for get relation data use for import reviews
     *
     * @param array $reviews : Data of connector return for query function getReviewsMainQuery
     * @param array $reviewsExt : Data of connector return for query function getReviewsExtQuery
     * @return array
     */
    protected function _getReviewsExtRelQuery($reviews, $reviewsExt) {
        return array();
    }

    /**
     * Get primary key of source review main
     *
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return int
     */
    public function getReviewId($review, $reviewsExt) {
        return $review['reviews_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return array
     */
    public function convertReview($review, $reviewsExt) {
        if (LitExtension_CartMigration_Model_Custom::REVIEW_CONVERT) {
            return $this->_custom->convertReviewCustom($this, $review, $reviewsExt);
        }
        $product_mage_id = $this->getMageIdProduct($review['products_id']);
        if (!$product_mage_id) {
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning("Review Id = {$review['reviews_id']} import failed. Error: Product Id = {$review['products_id']} not imported!")
            );
        }

        $store_id = $this->_notice['config']['languages'][$review['languages_id']];
        $data = array();
        $data['entity_pk_value'] = $product_mage_id;
        $data['status_id'] = ($review['reviews_status'] == 0) ? 3 : 1;
        $data['title'] = " ";
        $data['detail'] = $review['reviews_text'];
        $data['entity_id'] = 1;
        $data['stores'] = array($store_id);
        $data['customer_id'] = ($this->getMageIdCustomer($review['customers_id'])) ? $this->getMageIdCustomer($review['customers_id']) : null;
        $data['nickname'] = $review['customers_name'];
        $data['rating'] = $review['reviews_rating'];
        $data['created_at'] = $review['date_added'];
        $data['review_id_import'] = $review['reviews_id'];
        $custom = $this->_custom->convertReviewCustom($this, $review, $reviewsExt);
        if($custom){
            $data = array_merge($data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $data
        );
    }

############################################################ Extend function ##################################

    /**
     * Import parent category if not exists by id
     */
    protected function _importCategoryParent($parent_id){
        $categories = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => "SELECT * FROM _DBPRF_categories WHERE categories_id = {$parent_id}"
        ));
        if(!$categories || $categories['result'] != 'success'){
            return $this->errorConnector(true);
        }
        $categoriesExt = $this->getCategoriesExt($categories);
        if($categoriesExt['result'] != 'success'){
            return $categoriesExt;
        }
        $category = $categories['object'][0];
        $convert = $this->convertCategory($category, $categoriesExt);
        if($convert['result'] != 'success'){
            return array(
                'result' => 'warning',
            );
        }
        $data = $convert['data'];
        $category_ipt = $this->_process->category($data);
        if($category_ipt['result'] == 'success'){
            $this->categorySuccess($parent_id, $category_ipt['mage_id']);
            $this->afterSaveCategory($category_ipt['mage_id'], $data, $category, $categoriesExt);
        } else {
            $category_ipt['result'] = 'warning';
        }
        return $category_ipt;
    }

    /**
     * Get array value is number in array 2D
     */
    protected function _flitArrayNum($array) {
        $data = array();
        foreach ($array as $value) {
            if (is_numeric($value)) {
                $data[] = $value;
            }
        }
        return $data;
    }

    protected function _updateProductOptionStoreView($product_id, $options, $pre_options, $store_id) {
        $mage_product = Mage::getModel('catalog/product')->load($product_id);
        $mage_option = $mage_product->getProductOptionsCollection();
        if (isset($mage_option)) {
            foreach ($mage_option as $o) {
                $title = $o->getTitle();
                $cos = array();
                $co = array();
                foreach ($pre_options as $key => $pre_option) {
                    if ($title == $pre_option['title']) {
                        $o->setProduct($mage_product);
                        $o->setTitle($options[$key]['title']);
                        $o->setType($pre_option['type']);
                        $o->setIsRequire($pre_option['is_require']);
                        if($options[$key]['values']) {
                            $option_value = $o->getValuesCollection();
                            foreach ($option_value as $v) {
                                $value_title = $v->getTitle();
                                foreach ($pre_option['values'] as $k => $pre_value) {
                                        if ($value_title == $pre_value['title']) {
                                            $v->setTitle($options[$key]['values'][$k]['title']);
                                            $v->setStoreId($store_id);
                                            $v->setOption($o)->save();
                                            $cos[] = $v->toArray($co);
                                        }
                                }
                            }
                        }
                    }
                }
                $o->setData("values", $cos)
                              ->setStoreId($store_id)
                              ->save();
            }
        }
    }

    protected function _getOrderTaxValueFromListByCode($list, $code) {
        $result = 0;
        if ($list) {
            foreach ($list as $row) {
                if ($row['class'] == $code) {
                    $result += $row['value'];
                }
            }
        }
        return $result;
    }
    
    protected function _getPartName($name) {
        $fullname = array();
        $parts = explode(" ", $name);
        $fullname['lastname'] = array_pop($parts);
        $fullname['firstname'] = implode(" ", $parts);
        return $fullname;
    }

    /**
     * TODO: CRON
     */

    public function getAllTaxes()
    {
        if(!$this->_notice['config']['import']['taxes']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_tax_rates ORDER BY tax_rates_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllManufacturers()
    {
        if(!$this->_notice['config']['import']['manufacturers']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_manufacturers ORDER BY manufacturers_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllCategories()
    {
        if(!$this->_notice['config']['import']['categories']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_categories WHERE categories_mode = 'category' ORDER BY categories_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllProducts()
    {
        if(!$this->_notice['config']['import']['products']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_products ORDER BY products_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllCustomers()
    {
        if(!$this->_notice['config']['import']['customers']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_customers ORDER BY customers_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllOrders()
    {
        if(!$this->_notice['config']['import']['orders']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_orders ORDER BY orders_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllReviews()
    {
        if(!$this->_notice['config']['import']['reviews']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_reviews ORDER BY reviews_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

}
