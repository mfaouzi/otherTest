<?php

namespace Aliznet\WCSBundle\Resources\Constant;

/**
 * Class that contains language identifiers.
 *
 * Allow to bind akeneo's language to webSphere commerce's
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class Constants
{
    /*
     * File for Jobs   
     */
    const wcs_product_export = 'CatalogEntries.csv';
    const wcs_items_export = 'CatalogEntries.csv';
    const wcs_product_price_export = 'CatalogEntries2.csv';
    const wcs_items_prices_export = 'CatalogEntries2.csv';
    const wcs_attribute_export = 'AttributeDictionaryAttribute_one_language.csv';
    const wcs_attribute_values_export_language = 'AttributeDictionaryAttributeAllowedValues_one_language.csv';
    const wcs_attribute_values_export = 'AttributeDictionaryAttributeAndAllowedValues.csv';
    const wcs_category_export = 'CatalogGroups.csv';
    const wcs_category_translation_export = 'CatalogGroupWithTwoLanguageDescriptions.csv';
    const wcs_item_def_attribute_values_export = 'ProductDefiningAttributeValue.csv';
    const wcs_item_def_trans_attribute_values_export = 'ProductDefiningAttributeAndAllowedValues.csv';
    const wcs_item_desc_attribute_values_export = 'CatalogEntryDescriptiveAttributeAndValue.csv';
    const wcs_item_desc_trans_attribute_values_export = 'CatalogEntryDescriptiveAttributeAndValueTrans.csv';

    /*
     * Code for languages
     */
    const en_US = '-1';
    const fr_FR = '-2';
    const de_DE = '-3';
}
