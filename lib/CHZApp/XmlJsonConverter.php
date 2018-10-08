<?php
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
use \SimpleXMLElement;

class XmlJsonConverter extends Component
{
	/**
	 * Converts an json file to xml.
	 *
	 * @param string $jsonFile Full path to json file
	 *
	 * @return string the xml mounted
	 */
	final public function jsonFile2xml($jsonFile)
	{
		return $this->jsonString2xml(file_get_contents($jsonFile));
	}

	/**
	 * Converts an json string to xml
	 *
	 * @param string $jsonString
	 *
	 * @return string the xml mounted
	 */
	final public function jsonString2xml($jsonString)
	{
		return $this->json2xml(json_decode($jsonString));
	}

	/**
	 * Converts an json content into xml
	 *
	 * @param object $jsonData Json data to be converted
	 *
	 * @return string The xml mounted
	 */
	final public function json2xml($jsonData)
	{
		return $this->array2xml(json_decode(json_encode($jsonData), true));
	}

	/**
	 * Converts an array to xml output
	 *
	 * @param array $array
	 * @param SimpleXMLElement $xml
	 *
	 * @return string Xml as string
	 */
	final public function array2xml($array, SimpleXMLElement $xml = null)
	{
        if(is_null($xml))
        {
            $root = array_keys($array)[0];
            $xml = new SimpleXMLElement('<'.$root.'/>');
            $this->array2xml($array[$root], $xml);
        }
        else
        {
            foreach($array as $k => $v)
            {
                if(preg_match('/^\-/', $k))
                    $xml->addAttribute(substr($k, 1), $v);
                else if(is_array($v))
                    $this->array2xml($v, $xml->addChild($k));
                else
                {
                    $xml->addChild($k, $v);
                }
            }
        }
        return $xml->asXML();
	}
}
