/**
 * Copyright (c) 2026 BluePrint3D Ltd. All rights reserved.
 *
 * This software is provided free of charge for personal or commercial use.
 * Resale, redistribution, or sublicensing of this source code, modified or
 * unmodified, for direct financial gain is strictly prohibited.
 *
 * @author    BluePrint3D Ltd <support@blueprint3d.dev>
 * @copyright 2026 BluePrint3D Ltd (Company No. 13473806)
 * @license   Custom Proprietary EULA (See LICENSE.txt)
 */
define([
  'jquery',
  'Magento_Ui/js/modal/modal',
  'Magento_Ui/js/modal/alert',
  'mage/translate'
], function ($, modal, uiAlert, $t) {
  'use strict';

  /**
   * Show an error in the admin's own alert dialog rather than the browser's
   * native alert(), which freezes the page. The message can echo the email
   * the admin typed, and the dialog renders HTML, so escape it first.
   *
   * @param {String} message
   */
  var showError = function (message) {
    uiAlert({
      title: $t('Change order email'),
      content: $('<div>').text(message).html()
    });
  };

  return function (config) {
    var $editModalEl = $('#change-order-email-modal');
    var $confirmModalEl = $('#confirm-move-order-modal');

    var editModal = modal({
      title: $t('Edit order email'),
      buttons: [{
        text: $t('Save'),
        class: 'action-primary',
        click: function () {
          var newEmail = $('#change_order_email_new').val();
          var confirmEmail = $('#change_order_email_confirm').val();

          $.post(config.saveUrl, {
            form_key: window.FORM_KEY,
            new_email: newEmail,
            confirm_email: confirmEmail
          }).done(function (res) {
            if (res && res.needs_confirm) {
              $('#confirm-move-order-text').text(res.message || '');
              confirmModal.openModal();
              return;
            }

            if (res && res.success) {
              location.reload();
              return;
            }

            showError((res && res.message) ? res.message : $t('Unable to update email.'));
          }).fail(function () {
            showError($t('Request failed.'));
          });
        }
      }, {
        text: $t('Cancel'),
        class: 'action-secondary',
        click: function () {
          this.closeModal();
        }
      }]
    }, $editModalEl);

    var confirmModal = modal({
      title: $t('Email belongs to another customer'),
      buttons: [{
        text: $t('Confirm same customer (move order to that customer)'),
        class: 'action-primary',
        click: function () {
          $.post(config.confirmUrl, { form_key: window.FORM_KEY }).done(function (res) {
            if (res && res.success) {
              location.reload();
              return;
            }
            showError((res && res.message) ? res.message : $t('Unable to move order.'));
          }).fail(function () {
            showError($t('Request failed.'));
          });
        }
      }, {
        text: $t('Unsure — abort'),
        class: 'action-secondary',
        click: function () {
          this.closeModal();
        }
      }]
    }, $confirmModalEl);

    // Open modal from the top-right button (plugin triggers this)
    $(document).on('changeOrderEmail:open', function () {
      editModal.openModal();
    });
  };
});
