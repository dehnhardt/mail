<?php

declare(strict_types=1);

/**
 * @author Holger Dehnhardt <holger@dehnhardt.org>
 *
 * Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Sieve;

class SieveStructure {

	/** @var $installedExtensions */
	public $installedExtensions = [];

	/** @var $sieveActions */
	public $sieveActions = [];

	/** @var $sieveTestSubjects */
	public $sieveTestSubjects = [];

	/** @var $sieveOperators  */
	public $sieveListOperators = ['allof', 'anyof'];

	/** @var $sieveControls  */
	public $sieveControls = [
		'require', 'if', 'else', 'elseif'
	];

	/** @var $sieveComparators */
	public $sieveComparators = [];

	/** @var $headers */
	public $headers = [
		'From',
		'To',
		'CC',
		'BCC',
		'Envelope-to',
		'Date',
		'Reply-To',
		'List-ID',
		'Subject',
		'...'
	];

	public function __construct() {
		$this->createSieveTestSubjects();
		$this->createSieveActions();
		$this->createSieveComparators();
	}

	/**
	 *
	 * @param array $sieveExtensions
	 *
	 * @return array;
	 */
	public function getSupportedStructure(array $sieveExtensions) : array {
		$this->installedExtensions = $sieveExtensions;
		$supportedStructure = [];
		$supportedActions = [];
		$supportedActions = array_filter($this->sieveActions, [ $this, "filterByExtension"]);
		$supportedTestSubjects = array_filter($this->sieveTestSubjects, [$this, "filterByExtension"]);
		$supportedComparators = array_filter($this->sieveComparators, [$this, "filterByExtension"]);
		$supportedStructure['sieveListOperators'] = $this->sieveListOperators;
		$supportedStructure['supportedTestSubjects'] = $supportedTestSubjects;
		$supportedStructure['supportedAction'] = $supportedActions;
		$supportedStructure['supportedComparators'] = $supportedComparators;
		$supportedStructure['headers'] = $this->headers;
		return $supportedStructure;
	}

	/**
	 *
	 * @param object $sieveVerbObject
	 *
	 */
	public function filterByExtension($var) {
		return $var->extension === "" || in_array(strtoupper($var->extension),  $this->installedExtensions);
	}

	private function createSieveTestSubjects() {
		$this->sieveTestSubjects = [
			'address' => new SieveTestSubject('address', '', '%comparator* %addresspart %matchtype %header %keylist*'),
			'envelope' => new SieveTestSubject('envelope', 'envelope', ''),
			'header' => new SieveTestSubject('header', '', '%comparator %headers* %keylist*'),
			'size' => new SieveTestSubject('size', '', '%comparator %size'),
		];
	}

	private function createSieveComparators() {
		$this->sieveComparators = [
			':contains' => new SieveComparator(':contains', '', ['header']),
			':is' => new SieveComparator(':is', 'envelope', ['address', 'envelope', 'header']),
			':all' => new SieveComparator(':all', '', ['address', 'envelope', 'header']),
			':matches' => new SieveComparator(':matches', '', ['header', 'envelope']),
			':over' => new SieveComparator(':over', '', ['size']),
			':under' => new SieveComparator(':under', '', ['size']),
		];
	}

	private function createSieveActions() {
		$this->sieveActions =[
			'keep' => new SieveAction('keep'),
			'fileinto' => new SieveAction('fileinto', 'fileinto', '%folder'),
			'redirect' => new SieveAction('redirect', '', '%folder'),
			'discard' =>  new SieveAction('discard', '', ''),
			'notify' => new SieveAction('notify', 'notify', '%folder'),
			'addheader' => new SieveAction('addheader', 'editheader', '%folder'),
			'deleteheader' => new SieveAction('deleteheader', 'editheader', '%folder'),
			'setflag' => new SieveAction('setflag', '', '%folder'),
			'deleteflag' => new SieveAction('deleteflag', '', '%folder'),
			'removeflag' => new SieveAction('removeflag', '', '%folder')
		];
	}
}
