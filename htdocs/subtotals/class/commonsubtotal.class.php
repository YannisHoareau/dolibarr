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

	public function isSubtotalLine($line) {
		if ($line->special_code == self::$SPECIAL_CODE) {
			return true;
		}
		return false;
	}

	/**
	 * Adds a subtotal or a title line to a document
	 */
	public function addSubtotalLine($desc, $depth, $vatrate = 0, $remisepercent = 0)
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
				$vatrate,				// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				null,					// FK product
				$remisepercent,			// Discount percentage
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
				$vatrate,				// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				null,					// FK product
				$remisepercent,			// Discount percentage
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
				$vatrate,				// VAT rate
				0,						// Local tax 1
				0,						// Local tax 2
				null,					// FK product
				$remisepercent,			// Discount percentage
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
	public function updateSubtotalLine($lineid, $desc, $depth, $vatrate = 0, $remisepercent = 0)
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
				$remisepercent,			// Discount percentage
				'',						// Date start
				'',						// Date end
				$vatrate,				// VAT rate
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
				$remisepercent,			// Discount percentage
				$vatrate,				// VAT rate
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
				$remisepercent,			// Discount percentage
				$vatrate,				// VAT rate
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
	 * Updates a block of subtotals line of a document
	 */
	public function updateSubtotalLineBlockLines($linerang, $mode)
	{

		$linerang -= 1;
		$remise_percent = $this->lines[$linerang]->remise_percent;
		$tvatx = $this->lines[$linerang]->tva_tx;

		for ($i = $linerang+1; $i < count($this->lines)+1; $i++) {
			if ($this->lines[$i]->special_code == self::$SPECIAL_CODE) {
				if (abs($this->lines[$i]->qty) <= (int)$this->lines[$linerang]->qty) {
					return 1;
				}
			} else {
				if ($this->element == 'facture') {
					$result = $this->updateline(
						$this->lines[$i]->id,
						$this->lines[$i]->desc,
						$this->lines[$i]->subprice,
						$this->lines[$i]->qty,
						$mode == 'discount' ? $remise_percent : $this->lines[$i]->remise_percent,
						$this->lines[$i]->date_start,
						$this->lines[$i]->date_end,
						$mode == 'tva' ? $tvatx : $this->lines[$i]->tvatx,
						$this->lines[$i]->localtax1_tx,
						$this->lines[$i]->localtax2_tx,
						'HT',
						$this->lines[$i]->info_bits,
						$this->lines[$i]->product_type,
						$this->lines[$i]->fk_parent_line, 0,
						$this->lines[$i]->fk_fournprice,
						$this->lines[$i]->pa_ht,
						$this->lines[$i]->label,
						$this->lines[$i]->special_code,
						$this->lines[$i]->array_options,
						$this->lines[$i]->situation_percent,
						$this->lines[$i]->fk_unit,
						$this->lines[$i]->multicurrency_subprice);
				} elseif ($this->element == 'commande') {
					$result = $this->updateline(
						$this->lines[$i]->id,
						$this->lines[$i]->desc,
						$this->lines[$i]->subprice,
						$this->lines[$i]->qty,
						$mode == 'discount' ? $remise_percent : $this->lines[$i]->remise_percent,
						$mode == 'tva' ? $tvatx : $this->lines[$i]->tvatx,
						$this->lines[$i]->localtax1_rate,
						$this->lines[$i]->localtax2_rate,
						'HT',
						$this->lines[$i]->info_bits,
						$this->lines[$i]->date_start,
						$this->lines[$i]->date_end,
						$this->lines[$i]->product_type,
						$this->lines[$i]->fk_parent_line, 0,
						$this->lines[$i]->fk_fournprice,
						$this->lines[$i]->pa_ht,
						$this->lines[$i]->label,
						$this->lines[$i]->special_code,
						$this->lines[$i]->array_options,
						$this->lines[$i]->fk_unit,
						$this->lines[$i]->multicurrency_subprice);
				} elseif ($this->element == 'propal') {
					$result = $this->updateline(
						$this->lines[$i]->id,
						$this->lines[$i]->subprice,
						$this->lines[$i]->qty,
						$mode == 'discount' ? $remise_percent : $this->lines[$i]->remise_percent,
						$mode == 'tva' ? $tvatx : $this->lines[$i]->tvatx,
						$this->lines[$i]->localtax1_rate,
						$this->lines[$i]->localtax2_rate,
						$this->lines[$i]->desc,
						'HT',
						$this->lines[$i]->info_bits,
						$this->lines[$i]->special_code,
						$this->lines[$i]->fk_parent_line, 0,
						$this->lines[$i]->fk_fournprice,
						$this->lines[$i]->pa_ht,
						$this->lines[$i]->label,
						$this->lines[$i]->product_type,
						$this->lines[$i]->date_start,
						$this->lines[$i]->date_end,
						$this->lines[$i]->array_options,
						$this->lines[$i]->fk_unit,
						$this->lines[$i]->multicurrency_subprice);
				}
			}
		}

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
			if ($this->lines[$i-1]->special_code == self::$SPECIAL_CODE && $this->lines[$i-1]->qty>0) {
				if ($this->lines[$i-1]->qty <= abs($line->qty)) {
					return price($final_amount);
				}
			} else {
				$final_amount += $this->lines[$i-1]->total_ht;
			}
		}
		return price($final_amount);
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
			if ($this->lines[$i-1]->special_code == self::$SPECIAL_CODE && $this->lines[$i-1]->qty>0) {
				if ($this->lines[$i-1]->qty <= abs($line->qty)) {
					return price($final_amount);
				}
			} else {
				$final_amount += $this->lines[$i-1]->multicurrency_total_ht;
			}
		}
		return price($final_amount);
	}

	/**
	 * Returns a form array to add a subtotal or title line
	 *
	 * @param Form $form
	 * @param Translate $langs
	 * @param string $type 'title' or 'subtotal'
	 * @return string $formconfirm
	 */
	public function getSubtotalForm($form, $langs, $type, $seller)
	{
		$langs->load('subtotals');

		if ($type == 'subtotal') {
			$titles = $this->getPossibleTitles();
		}

		var_dump($titles);

		$depth_array = $this->getPossibleLevels($langs);

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

	/**
	 * Retrieve current object possible titles to choose from
	 *
	 * @return array The set of titles, empty if no title line set.
	 */
	public function getPossibleTitles() {
		$titles = array();
		foreach ($this->lines as $line) {
			if ($line->special_code == self::$SPECIAL_CODE && $line->qty > 0) {
				$titles[$line->desc] = $line->desc;
			}
		}
		return $titles;
	}

	/**
	 * Retrieve the current object possible levels (defined in admin page)
	 *
	 * @param Translate $langs
	 * @return array The set of possible levels, empty if not defined correctly.
	 */
	public function getPossibleLevels($langs) {
		$depth_array = array();
		for ($i = 0; $i < getDolGlobalString('SUBTOTAL_'.strtoupper($this->element).'_MAX_DEPTH', 2); $i++) {
			$depth_array[$i + 1] = $langs->trans("Level", $i + 1);
		}
		return $depth_array;
	}
}
