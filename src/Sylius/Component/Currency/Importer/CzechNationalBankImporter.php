<?php

namespace Sylius\Component\Currency\Importer;


use DateTime;

class CzechNationalBankImporter extends AbstractImporter
{
	private $url = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.xml?date=';

	/**
	 * {@inheritdoc}
	 */
	public function configure(array $options = array())
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function import(array $managedCurrencies = array())
	{
		$date = new DateTime('now');
		$fullUrl = $this->url . $date->format('d.m.Y');

		$xml = @simplexml_load_file($fullUrl);

		if ($xml instanceof \SimpleXMLElement) {

			$data = $xml->xpath('/kurzy/tabulka/*');

			foreach ($data as $row) {

				$currency = (string)$row->attributes()->kod;

				$rate    = str_replace(',', '.', $row->attributes()->kurz);
				$ammount = (int)$row->attributes()->mnozstvi;
				$rateToCZK = floatval($ammount/$rate);

				echo ($ammount/$ammount) . ' ' . 'CZK' . ' == ' . $rateToCZK . ' ' . $currency  . "\n";

				$this->updateOrCreate($managedCurrencies, (string) $currency, $rateToCZK);
			}

			$this->manager->flush();
		}
	}
}
