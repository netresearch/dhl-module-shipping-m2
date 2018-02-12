/**
 * Dhl Shipping
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * PHP version 5
 *
 * @category  Dhl
 * @package   Dhl\Shipping
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
define(["prototype", "Magento_Shipping/order/packaging"], function () {

    window.Packaging = Class.create(Packaging, {
        dhlShipping: {"items": [], "params": []},
        sendCreateLabelRequest: function () {
            var package = this;
            if (!this.validate()) {
                this.messages.show().update(this.validationErrorMsg);
                return;
            } else {
                this.messages.hide().update();
            }
            if (this.createLabelUrl) {
                var weight, length, width, height = null;
                var packagesParams = [];
                this.packagesContent.childElements().each(function (pack) {
                    var packageId = this.getPackageId(pack);
                    weight = parseFloat(pack.select('input[name="container_weight"]')[0].value);
                    length = parseFloat(pack.select('input[name="container_length"]')[0].value);
                    width = parseFloat(pack.select('input[name="container_width"]')[0].value);
                    height = parseFloat(pack.select('input[name="container_height"]')[0].value);
                    packagesParams[packageId] = {
                        container: pack.select('select[name="package_container"]')[0].value,
                        customs_value: parseFloat(pack.select('input[name="package_customs_value"]')[0].value, 10),
                        weight: isNaN(weight) ? '' : weight,
                        length: isNaN(length) ? '' : length,
                        width: isNaN(width) ? '' : width,
                        height: isNaN(height) ? '' : height,
                        weight_units: pack.select('select[name="container_weight_units"]')[0].value,
                        dimension_units: pack.select('select[name="container_dimension_units"]')[0].value

                    };

                    // ******** package params are added here**************

                    this.dhlShipping.params[packageId] = {};
                    pack.select('div[data-name="dhl_shipping_package_info"] [data-module^=dhl_shipping]').each(function (element) {
                        var fieldName = element.dataset.name;
                        // add service information to our shipping params
                        if (fieldName.match('service')) {
                            if (element.type.match('checkbox') && element.checked) {
                                this.dhlShipping.params[packageId][fieldName] = element.checked;
                            }

                            if (element.type.match('select') && Object.keys(this.dhlShipping.params[packageId]).indexOf(fieldName) != -1) {
                                this.dhlShipping.params[packageId][fieldName] = element.options[element.selectedIndex].value
                            }
                        }

                        if (fieldName.match('dhl_customs')) {
                            if (element.type.match('checkbox') && element.checked) {
                                this.dhlShipping.params[packageId][fieldName] = element.checked;
                            }

                            if (element.type.match('select')) {
                                this.dhlShipping.params[packageId][fieldName] = element.options[element.selectedIndex].value
                            }

                            if (element.type.match('text')) {
                                this.dhlShipping.params[packageId][fieldName] = element.value
                            }
                        }

                    }.bind(this));

                    // ***** add package params end

                    if (isNaN(packagesParams[packageId]['customs_value'])) {
                        packagesParams[packageId]['customs_value'] = 0;
                    }
                    if ('undefined' != typeof pack.select('select[name="package_size"]')[0]) {
                        if ('' != pack.select('select[name="package_size"]')[0].value) {
                            packagesParams[packageId]['size'] = pack.select('select[name="package_size"]')[0].value;
                        }
                    }
                    if ('undefined' != typeof pack.select('input[name="container_girth"]')[0]) {
                        if ('' != pack.select('input[name="container_girth"]')[0].value) {
                            packagesParams[packageId]['girth'] = pack.select('input[name="container_girth"]')[0].value;
                            packagesParams[packageId]['girth_dimension_units'] = pack.select('select[name="container_girth_dimension_units"]')[0].value;
                        }
                    }
                    if ('undefined' != typeof pack.select('select[name="content_type"]')[0] && 'undefined' != typeof pack.select('input[name="content_type_other"]')[0]) {
                        packagesParams[packageId]['content_type'] = pack.select('select[name="content_type"]')[0].value;
                        packagesParams[packageId]['content_type_other'] = pack.select('input[name="content_type_other"]')[0].value;
                    } else {
                        packagesParams[packageId]['content_type'] = '';
                        packagesParams[packageId]['content_type_other'] = '';
                    }
                    var deliveryConfirmation = pack.select('select[name="delivery_confirmation_types"]');
                    if (deliveryConfirmation.length) {
                        packagesParams[packageId]['delivery_confirmation'] = deliveryConfirmation[0].value
                    }
                }.bind(this));
                for (var packageId in this.packages) {
                    if (!isNaN(packageId)) {
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[container]'] = packagesParams[packageId]['container'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[weight]'] = packagesParams[packageId]['weight'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[customs_value]'] = packagesParams[packageId]['customs_value'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[length]'] = packagesParams[packageId]['length'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[width]'] = packagesParams[packageId]['width'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[height]'] = packagesParams[packageId]['height'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[weight_units]'] = packagesParams[packageId]['weight_units'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[dimension_units]'] = packagesParams[packageId]['dimension_units'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[content_type]'] = packagesParams[packageId]['content_type'];
                        this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[content_type_other]'] = packagesParams[packageId]['content_type_other'];

                        // **** our params ********

                        _.forEach(this.dhlShipping.params[packageId], function (value, key) {
                            if (key.match('service')) {
                                index = key.replace("service_", "");
                                this.paramsCreateLabelRequest['packages[' + packageId + '][params][services][' + index + ']'] = value;
                            }


                            if (key.match('dhl_customs')) {
                                index = key.replace("dhl_customs_", "");
                                this.paramsCreateLabelRequest['packages[' + packageId + '][params][customs][' + index + ']'] = value;
                            }
                        }.bind(this));

                        // **** our params end ********

                        if ('undefined' != typeof packagesParams[packageId]['size']) {
                            this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[size]'] = packagesParams[packageId]['size'];
                        }

                        if ('undefined' != typeof packagesParams[packageId]['girth']) {
                            this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[girth]'] = packagesParams[packageId]['girth'];
                            this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[girth_dimension_units]'] = packagesParams[packageId]['girth_dimension_units'];
                        }

                        if ('undefined' != typeof packagesParams[packageId]['delivery_confirmation']) {
                            this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[params]' + '[delivery_confirmation]'] = packagesParams[packageId]['delivery_confirmation'];
                        }
                        for (var packedItemId in this.packages[packageId]['items']) {
                            if (!isNaN(packedItemId)) {
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][qty]'] = this.packages[packageId]['items'][packedItemId]['qty'];
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][customs_value]'] = this.packages[packageId]['items'][packedItemId]['customs_value'];
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][price]'] = package.defaultItemsPrice[packedItemId];
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][name]'] = package.defaultItemsName[packedItemId];
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][weight]'] = package.defaultItemsWeight[packedItemId];
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][product_id]'] = package.defaultItemsProductId[packedItemId];
                                this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + '][order_item_id]'] = package.defaultItemsOrderItemId[packedItemId];

                                // ******** customs item params are added here**************

                                _.forEach(this.dhlShipping.items[packageId][packedItemId], function (value, key) {
                                    this.paramsCreateLabelRequest['packages[' + packageId + ']' + '[items]' + '[' + packedItemId + ']['+key+']'] = value;
                                }.bind(this));

                                //******** customs item params end **************
                            }
                        }
                    }
                }

                new Ajax.Request(this.createLabelUrl, {
                    parameters: this.paramsCreateLabelRequest,
                    onSuccess: function (transport) {
                        var response = transport.responseText;
                        if (response.isJSON()) {
                            response = response.evalJSON();
                            if (response.error) {
                                this.messages.show().innerHTML = response.message;
                            } else if (response.ok && Object.isFunction(this.labelCreatedCallback)) {
                                this.labelCreatedCallback(response);
                            }
                        }
                    }.bind(this)
                });
                if (this.paramsCreateLabelRequest['code']
                    && this.paramsCreateLabelRequest['carrier_title']
                    && this.paramsCreateLabelRequest['method_title']
                    && this.paramsCreateLabelRequest['price']
                ) {
                    var a = this.paramsCreateLabelRequest['code'];
                    var b = this.paramsCreateLabelRequest['carrier_title'];
                    var c = this.paramsCreateLabelRequest['method_title'];
                    var d = this.paramsCreateLabelRequest['price'];

                    this.paramsCreateLabelRequest = {};
                    this.paramsCreateLabelRequest['code'] = a;
                    this.paramsCreateLabelRequest['carrier_title'] = b;
                    this.paramsCreateLabelRequest['method_title'] = c;
                    this.paramsCreateLabelRequest['price'] = d;
                } else {
                    this.paramsCreateLabelRequest = {};
                }
            }
        },

        packItems: function (obj) {
            var anySelected = false;
            var packageBlock = $(obj).up('[id^="package_block"]');
            var packageId = this.getPackageId(packageBlock);
            var packagePrepare = packageBlock.select('[data-role=package-items]')[0];
            var packagePrepareGrid = packagePrepare.select('.grid_prepare')[0];

            // check for exceeds the total shipped quantity
            var checkExceedsQty = false;
            this.messages.hide().update();
            packagePrepareGrid.select('.grid tbody tr').each(function (item) {
                var checkbox = item.select('[type="checkbox"]')[0];
                var itemId = checkbox.value;
                var qtyValue = this._parseQty(item.select('[name="qty"]')[0]);
                item.select('[name="qty"]')[0].value = qtyValue;
                if (checkbox.checked && this._checkExceedsQty(itemId, qtyValue)) {
                    this.messages.show().update(this.errorQtyOverLimit);
                    checkExceedsQty = true;
                }
            }.bind(this));
            if (checkExceedsQty) {
                return;
            }

            if (!this.validateCustomsValue()) {
                return;
            }

            // perform validation on checked items
            var errorsFound = false;
            packagePrepareGrid.select('.grid tbody tr').each(function (item) {
                if (item.select('[type="checkbox"]')[0].checked) {
                    var tariffInput = item.select('input').each(function (input) {
                        if (!this.validateElement(input)) {
                            errorsFound = true;
                        }
                    }.bind(this));
                }
            }.bind(this));
            if (errorsFound) {
                this.messages.show().update(this.validationErrorMsg);
                return;
            } else {
                this.messages.hide().update();
            }

            // prepare items for packing
            packagePrepareGrid.select('.grid tbody tr').each(function (item) {
                var checkbox = item.select('[type="checkbox"]')[0];
                if (checkbox.checked) {
                    var qty = item.select('[name="qty"]')[0];
                    var qtyValue = this._parseQty(qty);
                    item.select('[name="qty"]')[0].value = qtyValue;
                    anySelected = true;
                    qty.disabled = 'disabled';
                    checkbox.disabled = 'disabled';
                    packagePrepareGrid.select('.grid th [type="checkbox"]')[0].up('th label').hide();
                    item.select('[data-action=package-delete-item]')[0].show();
                } else {
                    item.remove();
                }
            }.bind(this));

            this.updateExportDescription(obj);

            // packing items
            if (anySelected) {
                var packItems = packageBlock.select('.package_items')[0];
                if (!packItems) {
                    packagePrepare.insert(new Element('div').addClassName('grid_prepare'));
                    packagePrepare.insert({after: packagePrepareGrid});
                    packItems = packagePrepareGrid.removeClassName('grid_prepare').addClassName('package_items');
                    this.dhlShipping.items[packageId] = {};
                    packItems.select('.grid tbody tr').each(function (item) {
                        var itemId = item.select('[type="checkbox"]')[0].value;
                        var qtyValue = parseFloat(item.select('[name="qty"]')[0].value);
                        qtyValue = (qtyValue <= 0) ? 1 : qtyValue;


                        //************************ item params declaration end ************************


                        if ('undefined' == typeof this.packages[packageId]) {
                            this.packages[packageId] = {'items': [], 'params': {}};
                        }
                        if ('undefined' == typeof this.packages[packageId]['items'][itemId]) {
                            this.packages[packageId]['items'][itemId] = {};
                            this.packages[packageId]['items'][itemId]['qty'] = qtyValue;

                            // ************ add our item params to package items****************

                            this.dhlShipping.items[packageId][itemId] = {};
                            item.select('[data-module^=dhl_shipping]').each(function (element) {
                                var fieldName = element.dataset.name;
                                if (element.tagName === 'INPUT') {
                                    this.dhlShipping.items[packageId][itemId][fieldName] = element.value;
                                }

                                if (element.tagName === 'SELECT') {
                                    this.dhlShipping.items[packageId][itemId][fieldName] = element.options[element.selectedIndex].value;
                                }
                                if (element.dataset.updatepackage && element.innerText.length) {
                                    packageBlock.select('[data-module="dhl_shipping"][data-name='+element.dataset.name+']').first().value = element.innerText;
                                }
                                element.disabled ='disabled';
                            }.bind(this));

                            // ************  END ****************
                        } else {
                            this.packages[packageId]['items'][itemId]['qty'] += qtyValue;
                        }
                    }.bind(this));
                } else {
                    packagePrepareGrid.select('.grid tbody tr').each(function (item) {
                        var itemId = item.select('[type="checkbox"]')[0].value;
                        var qtyValue = parseFloat(item.select('[name="qty"]')[0].value);
                        qtyValue = (qtyValue <= 0) ? 1 : qtyValue;

                        if ('undefined' == typeof this.packages[packageId]['items'][itemId]) {
                            this.packages[packageId]['items'][itemId] = {};
                            this.packages[packageId]['items'][itemId]['qty'] = qtyValue;
                            packItems.select('.grid tbody')[0].insert(item);
                        } else {
                            this.packages[packageId]['items'][itemId]['qty'] += qtyValue;
                            var packItem = packItems.select('[type="checkbox"][value="' + itemId + '"]')[0].up('tr').select('[name="qty"]')[0];
                            packItem.value = this.packages[packageId]['items'][itemId]['qty'];
                        }
                        item.select('[data-module^=dhl_shipping]').each(function (element) {
                            element.disabled ='disabled';
                        });
                    }.bind(this));
                    packagePrepareGrid.update();
                }
                $(packItems).show();
                this._recalcContainerWeightAndCustomsValue(packItems);
            } else {
                packagePrepareGrid.update();
            }



            // show/hide disable/enable
            packagePrepare.hide();
            packageBlock.select('[data-action=package-save-items]')[0].hide();
            packageBlock.select('[data-action=package-add-items]')[0].show();
            this._setAllItemsPackedState()
        },

        /**
        * Search items in package for exportDescriptions.
        * Update the Export Description textarea of the package block with values.
        *
        * @param HTMLElement obj
        */
        updateExportDescription: function(obj) {
            var itemSeparator = ' ';
            var packageBlock = $(obj).up('[id^="package_block"]');
            var packagePrepare = packageBlock.select('[data-role=package-items]')[0];
            var packagePrepareGrid = packagePrepare.select('.grid_prepare')[0];
            var descriptionTextarea = packageBlock.select('[data-name=dhl_customs_export_description]')[0];
            var descriptions = [];


            /**
             * Skip if no description textarea is present in the grid.
             */
            if (!descriptionTextarea) {
                return;
            }

            if (descriptionTextarea.value) {
                descriptions.push(descriptionTextarea.value);
            }

            packagePrepareGrid.select('.grid tbody tr').each(function (item) {
                var itemExportDescription = item.select('[data-name=export_description]')[0].value;
                if (itemExportDescription) {
                    descriptions.push(itemExportDescription);
              }
            });
            var textAreaValue = descriptions.join(itemSeparator).substring(0,50);

            descriptionTextarea.setValue(textAreaValue);
        }
    });
});
