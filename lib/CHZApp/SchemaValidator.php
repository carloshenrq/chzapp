<?
/**
 * BSD 3-Clause License
 * 
 * Copyright (c) 2017, Carlos Henrique
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 ** Redistributions of source code must retain the above copyright notice, this
 *  list of conditions and the following disclaimer.
 * 
 ** Redistributions in binary form must reproduce the above copyright notice,
 *  this list of conditions and the following disclaimer in the documentation
 *  and/or other materials provided with the distribution.
 * 
 ** Neither the name of the copyright holder nor the names of its
 *  contributors may be used to endorse or promote products derived from
 *  this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace CHZApp;

use \DOMDocument;

class SchemaValidator extends Component
{
	/**
	 * Validates an xml file against an xsd file.
	 *
	 * @param string $sFileXml Full path to xml file.
	 * @param string $sFileXsd Full path to xsd file
	 *
	 * @return boolean True is a valid xml valid
	 */
	public function validateXmlFile($sFileXml, $sFileXsd)
	{
		if(!file_exists($sFileXml))
			throw new SchemaValidatorException('File in \'' . $sFileXml . '\' not found.');

		return $this->validateXml(file_get_contents($sFileXml), $sFileXsd);
	}

	/**
	 * Validates an xml content against an xsd file.
	 *
	 * @param string $sXmlContent Xml content to be validate
	 * @param string $sFileXsd Full path to xsd file
	 *
	 * @return boolean True is a valid xml valid
	 */
	public function validateXml($sXmlContent, $sFileXsd)
	{
		libxml_use_internal_errors(true);
		$xml = new DOMDocument;

		if(@$xml->loadXML($sXmlContent) == false)
			throw new SchemaValidatorException('The xml content is not a valid xml.');

		// Validates the content loaded agains the xsd file.
		return @$xml->schemaValidate($sFileXsd);
	}

}
