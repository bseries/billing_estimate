<?php
/**
 * Billing Estimate
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace billing_estimate\controllers;

use base_core\models\Users;
use billing_core\models\Currencies;
use billing_core\billing\TaxTypes;
use billing_estimate\models\Estimates;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

class EstimatesController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;
	use \base_core\controllers\DownloadTrait;

	public function admin_export_pdf() {
		extract(Message::aliases());

		$item = Estimates::find('first', [
			'conditions' => [
				'id' => $this->request->id
			]
		]);

		$this->_renderDownload(
			$stream = $item->exportAsPdf(),
			'application/pdf'
		);
		fclose($stream);
	}

	public function admin_duplicate() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);
		$result = $item->duplicate();

		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully duplicated.', ['scope' => 'billing_estimate']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to duplicate.', ['scope' => 'billing_estimate']), [
				'level' => 'error'
			]);
		}
		return $this->redirect(['action' => 'index']);
	}

	public function admin_convert_to_invoice() {
		extract(Message::aliases());

		Estimates::pdo()->beginTransaction();

		$item = Estimates::find('first', [
			'conditions' => [
				'id' => $this->request->id
			]
		]);
		if ($invoice = $item->convertToInvoice()) {
			Estimates::pdo()->commit();
			FlashMessage::write($t('Created invoice {:number}.', [
				'scope' => 'billing_estimate',
				'number' => $invoice->number
			]), [
				'level' => 'success'
			]);
		} else {
			Estimates::pdo()->rollback();
			FlashMessage::write($t('Failed to convert to invoice.', ['scope' => 'billing_estimate']), [
				'level' => 'error'
			]);
		}
		return $this->redirect(['action' => 'index']);
	}

	protected function _selects($item = null) {
		$statuses = Estimates::enum('status');
		$currencies = Currencies::find('list');
		$users = [null => '-'] + Users::find('list', ['order' => 'number']);

		if ($item) {
			$taxTypes = TaxTypes::enum();
		}
		return compact('currencies', 'statuses', 'users', 'taxTypes');
	}
}

?>
