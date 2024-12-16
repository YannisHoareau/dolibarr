<?php
/**
 * Trait CommonSubtotal
 * add subtotals lines
 */
trait CommonSubtotal
{
	/**
	 * Special ode for subtotals module lines
	 */
	public static $SPECIAL_CODE = 81;

	/**
	 * Type for subtotals module lines
	 */
	public static $PRODUCT_TYPE = 9;

	public function getSubtotalSpecialCode(): int
	{
		return self::$SPECIAL_CODE;
	}

	/**
	 * Adds a subtotal or a title line to a document
	 */
	public function addSubtotalLine($desc, $depth)
	{
		$current_module = $this->element;
		// Ensure the object is one of the supported types
		$allowed_types = array('propal', 'commande', 'facture');
		if (!in_array($current_module, $allowed_types)) {
			return false; // Unsupported type
		}

		// Add the line calling the right module
		if ($current_module == 'facture') {
			$result = $this->addline(
				$desc,					// Description
				0,						// Unit price
				$depth,					// Quantity
				0,						// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				null,					// FK product
				0,						// Discount percentage
				'',						// Date start
				'',						// Date end
				0,						// FK code ventilation
				0,						// Info bits
				0,						// FK remise except
				'',						// Price base type
				0,						// PU ttc
				self::$PRODUCT_TYPE,	// Type
				-1,						// Rang
				self::$SPECIAL_CODE		// Special code
			);
		} elseif ($current_module== 'propal') {
			$result = $this->addline(
				$desc,					// Description
				0,						// Unit price
				$depth,					// Quantity
				0,						// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				null,					// FK product
				0,						// Discount percentage
				'',						// Price base type
				0,						// PU ttc
				0,						// Info bits
				self::$PRODUCT_TYPE,	// Type
				-1,						// Rang
				self::$SPECIAL_CODE		// Special code
			);
		} elseif ($current_module== 'commande') {
			$result = $this->addline(
				$desc,					// Description
				0,						// Unit price
				$depth,					// Quantity
				0,						// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				null,					// FK product
				0,						// Discount percentage
				0,						// Info bits
				0,						// FK remise except
				'',						// Price base type
				0,						// PU ttc
				'',						// Date start
				'',						// Date end
				self::$PRODUCT_TYPE,	// Type
				-1,						// Rang
				self::$SPECIAL_CODE		// Special code
			);
		}

		return $result >= 0 ? $result : -1; // Return line ID or false
	}

	/**
	 * Updates a subtotals line to a document
	 */
	public function updateSubtotalLine($lineid, $desc, $depth)
	{

		$current_module = $this->element;
		// Ensure the object is one of the supported types
		$allowed_types = array('propal', 'commande', 'facture');
		if (!in_array($current_module, $allowed_types)) {
			return false; // Unsupported type
		}

		// Update the line calling the right module
		if ($current_module == 'facture') {
			$result = $this->updateline(
				$lineid, 				// ID of line to change
				$desc,					// Description
				0,						// Unit price
				$depth,					// Quantity
				0,						// Discount percentage
				'',						// Date start
				'',						// Date end
				0,						// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				'',						// Price base type
				0, 						// Info bits
				self::$PRODUCT_TYPE,	// Type
				0,						// FK parent line
				0,						// Skip update total
				null,					// FK fournprice
				0,						// PA ht
				'',						// Label
				self::$SPECIAL_CODE		// Special code
			);
		} elseif ($current_module== 'propal') {
			$result = $this->updateline(
				$lineid, 				// ID of line to change
				0,						// Unit price
				$depth,					// Quantity
				0,						// Discount percentage
				0,						// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				$desc,					// Description
				'',						// Price base type
				0,						// Info bits
				self::$SPECIAL_CODE, 	// Special code
				0, 						// FK parent line
				0, 						// Skip update total
				0, 						// FK fournprice
				0, 						// PA ht
				'',						// Label
				self::$PRODUCT_TYPE		// Type
			);
		} elseif ($current_module== 'commande') {
			$result = $this->updateline(
				$lineid, 				// ID of line to change
				$desc,					// Description
				0,						// Unit price
				$depth,					// Quantity
				0,						// Discount percentage
				0,						// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				'',						// Price base type
				0,						// Info bits
				'',						// Date start
				'',						// Date end
				self::$PRODUCT_TYPE,	// Type
				0, 						// FK parent line
				0, 						// Skip update total
				0, 						// FK fournprice
				0, 						// PA ht
				'',						// Label
				self::$SPECIAL_CODE 	// Special code
			);
		}

		return $result >= 0 ? $result : -1; // Return line ID or false
	}

	/**
	 * Return the total_ht of lines that are above the current line (excluded) and that are not a subtotal line
	 * until a title line of the same level is found
	 *
	 * @param object	$line
	 * @return int		$total_ht
	 */
	public function getSubtotalLineAmount($line)
	{
		$final_amount = 0;
		for ($i = $line->rang-1; $i > 0; $i--) {
			if ($this->lines[$i-1]->special_code == self::$SPECIAL_CODE) {
				if ($this->lines[$i-1]->qty <= $line->qty*-1) {
					return $final_amount;
				}
			} else {
				$final_amount += $this->lines[$i-1]->total_ht;
			}
		}
		return $final_amount;
	}

	/**
	 * Return the multicurrency_total_ht of lines that are above the current line (excluded) and that are not a subtotal line
	 * until a title line of the same level is found
	 *
	 * @param object	$line
	 * @return int		$total_ht
	 */
	public function getSubtotalLineMulticurrencyAmount($line)
	{
		$final_amount = 0;
		for ($i = $line->rang-1; $i > 0; $i--) {
			if ($this->lines[$i-1]->special_code == self::$SPECIAL_CODE) {
				if ($this->lines[$i-1]->qty <= $line->qty*-1) {
					return $final_amount;
				}
			} else {
				$final_amount += $this->lines[$i-1]->multicurrency_total_ht;
			}
		}
		return $final_amount;
	}

	/**
	 * Returns a form array to add a subtotal or title line
	 *
	 * @param Form $form
	 * @param Translate $langs
	 * @param string $type 'title' or 'subtotal'
	 * @return string $formconfirm
	 */
	public function getSubtotalForm($form, $langs, $type)
	{
		$tpl = dol_buildpath('/core/tpl/subtotal_create.tpl.php');

		if (empty($conf->file->strict_mode)) {
			$res = @include $tpl;
		} else {
			$res = include $tpl; // for debug
		}

		return $res;
	}

	/**
	 * Retrieve the background color associated with a specific subtotal level.
	 *
	 * @param int $level The level of the subtotal for which the color is requested.
	 * @return string|null The background color in hexadecimal format or null if not set.
	 */
	public function getSubtotalColors($level) {
		return getDolGlobalString('SUBTOTAL_BACK_COLOR_LEVEL_'.abs($level));
	}
}
