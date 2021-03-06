<?php

/**
 * @project     PromotionBanner
 * @package	LitExtension_PromotionBanner
 * @author      LitExtension
 * @email       litextension@gmail.com
 */
class LitExtension_PromotionBanner_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_blockGroup = 'promotionbanner';
        $this->_controller = 'adminhtml_banner';
        $this->_updateButton('save', 'label', Mage::helper('promotionbanner')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('promotionbanner')->__('Delete Banner'));
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('promotionbanner')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);
        $this->_formScripts[] = "
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
    }

    public function getHeaderText() {
        if (Mage::registry('banner_data') && Mage::registry('banner_data')->getId()) {
            return Mage::helper('promotionbanner')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getTitle()));
        } else {
            return Mage::helper('promotionbanner')->__('Add Banner');
        }
    }

}