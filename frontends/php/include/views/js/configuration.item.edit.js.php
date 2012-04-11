<script type="text/javascript">
	function removeDelayFlex(index) {
		jQuery('#delayFlex_' + index).remove();
		jQuery('#delay_flex_' + index + '_delay').remove();
		jQuery('#delay_flex_' + index + '_period').remove();

		displayNewDeleyFlexInterval();
	}

	function displayNewDeleyFlexInterval() {
		// delay_flex_visible is in massupdate, no delay_flex_visible in items
		if ((jQuery('#delay_flex_visible').length == 0 || jQuery('#delay_flex_visible').is(':checked'))
				&& jQuery('#delayFlexTable tr').length <= 7) {
			jQuery('#row_new_delay_flex').css('display', 'block');
		}
		else {
			jQuery('#row_new_delay_flex').css('display', 'none');
		}
	}

	function itemTypeInterface(type) {
		var result = null;
		var types = <?php echo CJs::encodeJson(itemTypeInterface()); ?>;
		jQuery.each(types, function(itemType, interfaceType) {
			if (type == itemType) {
				result = interfaceType;
				return interfaceType;
			}
		});
		return result;
	}

	function organizeInterfaces(interfaceType) {
		var selectedInterfaceId = jQuery('#selectedInterfaceId').val();
		var isSelected = false;
		var interfaceExists = false;

		if (jQuery('#interface_visible').data('multipleInterfaceTypes') && !jQuery('#type_visible').is(':checked')) {
			jQuery('#interface_not_defined').html('<?php echo _('To set a host interface select a single item type for all items') ?>').show();
			jQuery('#interfaceid').hide();
		}
		else {
			if (interfaceType > 0) {
				jQuery('#interface_row option').each(function() {
					if (jQuery(this).data('interfacetype') == interfaceType) {
						interfaceExists = true;
					}
				});

				if (interfaceExists) {
					jQuery('#interfaceid').show();
					jQuery('#interface_not_defined').hide();

					jQuery('#interface_row option').each(function() {
						if (jQuery(this).is('[selected]')) {
							jQuery(this).removeAttr('selected');
						}
						if (jQuery(this).data('empty') == 1) {
							jQuery(this).remove();
						}
					});

					jQuery('#interface_row option').each(function() {
						if (jQuery(this).data('interfacetype') == interfaceType) {
							jQuery(this).prop('disabled', false);
							if (!isSelected) {
								if (jQuery(this).val() == selectedInterfaceId) {
									jQuery(this).attr('selected', 'selected');
									isSelected = true;
								}
							}
						}
						else {
							jQuery(this).prop('disabled', true);
						}
					});

					// select first available option if we previously don't selected it by interfaceid
					if (!isSelected) {
						jQuery('#interface_row option').each(function() {
							if (jQuery(this).data('interfacetype') == interfaceType) {
								if (!isSelected) {
									jQuery(this).attr('selected', 'selected');
									isSelected = true;
								}
							}
						});
					}
				}
				else {
					// hide combobox and display warning text
					jQuery('#interfaceid').hide();
					jQuery('#interface_row option').each(function() {
						if (jQuery(this).is('[selected]')) {
							jQuery(this).removeAttr('selected');
						}
					});
					jQuery('#interfaceid').append('<option value="0" selected="selected" data-empty="1"></option>');
					jQuery('#interfaceid').val(0);
					jQuery('#interface_not_defined').html('<?php echo _('No interface found') ?>').show();
				}
			}
			else {
				// display all interfaces for ANY
				jQuery('#interfaceid').show();
				jQuery('#interface_not_defined').hide();

				jQuery('#interface_row option').each(function() {
					if (jQuery(this).data('empty') == 1) {
						jQuery(this).remove();
					}
					else {
						jQuery(this).prop('disabled', false);
						if (!isSelected) {
							jQuery(this).attr('selected', 'selected');
							isSelected = true;
						}
					}
				});
			}
		}
	}

	/*
	 * ITEM_TYPE_ZABBIX: 0
	 * ITEM_TYPE_SNMPTRAP: 17
	 * ITEM_TYPE_SIMPLE: 3
	 */
	function displayKeyButton() {
		var type = parseInt(jQuery('#type').val());

		if (type == 0 || type == 7 || type == 3 || type == 5 || type == 8 || type == 17) {
			jQuery('#keyButton').prop('disabled', false);
		}
		else {
			jQuery('#keyButton').prop('disabled', true);
		}
	}

	function setAuthTypeLabel() {
		if (jQuery('#authtype').val() == 1) {
			jQuery('#row_password label').html('<?php echo _('Key passphrase'); ?>');
		}
		else {
			jQuery('#row_password label').html('<?php echo _('Password'); ?>');
		}
	}

	jQuery(document).ready(function() {
		<?php
		if (!empty($this->data['valueTypeVisibility'])) { ?>
			var valueTypeSwitcher = new CViewSwitcher('value_type', 'change',
				<?php echo zbx_jsvalue($this->data['valueTypeVisibility'], true); ?>);
		<?php }
		if (!empty($this->data['authTypeVisibility'])) { ?>
			var authTypeSwitcher = new CViewSwitcher('authtype', 'change',
				<?php echo zbx_jsvalue($this->data['authTypeVisibility'], true); ?>);
		<?php }
		if (!empty($this->data['typeVisibility'])) { ?>
			var typeSwitcher = new CViewSwitcher('type', 'change',
				<?php echo zbx_jsvalue($this->data['typeVisibility'], true); ?>);
		<?php }
		if (!empty($this->data['securityLevelVisibility'])) { ?>
			var securityLevelSwitcher = new CViewSwitcher('snmpv3_securitylevel', 'change',
				<?php echo zbx_jsvalue($this->data['securityLevelVisibility'], true); ?>);
		<?php }
		if (!empty($this->data['dataTypeVisibility'])) { ?>
			var dataTypeSwitcher = new CViewSwitcher('data_type', 'change',
				<?php echo zbx_jsvalue($this->data['dataTypeVisibility'], true); ?>);
		<?php } ?>

		var multpStat = document.getElementById('multiplier');
		if (multpStat && multpStat.onclick) {
			multpStat.onclick();
		}

		var maxReached = <?php echo $this->data['maxReached'] ? 'true' : 'false'; ?>;
		if (maxReached) {
			jQuery('#row_new_delay_flex').css('display', 'none');
		}

		jQuery('#type').bind('change', function() {
			organizeInterfaces(itemTypeInterface(parseInt(jQuery('#type').val())));
			displayKeyButton();
		});
		jQuery('#interface_visible, #type_visible').click(function() {
			organizeInterfaces(itemTypeInterface(parseInt(jQuery('#type').val())));
		});
		var initialInterfaceType = <?php echo CJs::encodeJson($data['initial_interface_type']) ?>;
		organizeInterfaces(initialInterfaceType || itemTypeInterface(parseInt(jQuery('#type').val())));
		displayKeyButton();

		jQuery('#authtype').bind('change', function() {
			setAuthTypeLabel();
		});
		setAuthTypeLabel();

		// mass update page
		if (jQuery('#delay_flex_visible').length != 0) {
			displayNewDeleyFlexInterval();

			jQuery('#delay_flex_visible').click(function() {
				displayNewDeleyFlexInterval();
			});
		}
	});
</script>
