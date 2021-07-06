<?php


namespace Sparket\Orders\Cfdi;


class Tools
{

    static public function parseXML(string $xml)
    {
        return self::fixXML(self::_parseXML($xml));
    }

    static protected function _parseXML($xml, $options = array(), $fix33 = true)
    {

        if (!$xml)
        {
            return $xml;
        }
        else if (is_string($xml))
        {

            $xml = str_replace("<tfd:", "<cfdi:", $xml);
            $xml = str_replace("<cfdi:", "<", $xml);
            $xml = str_replace("</cfdi:", "</", $xml);
            $xml = str_replace("<nomina12:", "<", $xml);
            $xml = str_replace("</nomina12:", "</", $xml);
            $xml = str_replace("<nomina11:", "<", $xml);
            $xml = str_replace("</nomina11:", "</", $xml);
            $xml = str_replace("<pago10:", "<", $xml);
            $xml = str_replace("</pago10:", "</", $xml);

            $xml = simplexml_load_string(\BN_Coders::utf8_encode($xml));

            if (!$xml)
            {
                return null;
            }

        }



        $defaults = array(
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix' => '',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(),   //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => '$',       //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       //optional search and replace on tag and attribute names
            'keyReplace' => false       //replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = self::_parseXML($childXml, $options, false);
                list($childTagName, $childProperties) = each($childArray);

                //replace characters in tag name
                if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                //add namespace prefix, if any
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        //get text content of node
        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        if ($fix33)
        {
            $propertiesArray = self::convert33($propertiesArray);
        }

        return array(
            $xml->getName() => $propertiesArray
        );

    }

    static private function fixXML($xml)
    {
        if ($xml['Comprobante']['Version'] == '3.3')
        {
            if ($xml['Comprobante']['Conceptos']['Concepto']['Cantidad'])
            {
                $xml['Comprobante']['Conceptos']['Concepto'] = [$xml['Comprobante']['Conceptos']['Concepto']];
            }

            if ($xml['Comprobante']['Impuestos']['Traslados']['Traslado']['Impuesto'])
            {
                $xml['Comprobante']['Impuestos']['Traslados']['Traslado'] = [$xml['Comprobante']['Impuestos']['Traslados']['Traslado']];
            }
        }
        else
        {
            if ($xml['Comprobante']['Conceptos']['Concepto']['Cantidad'])
            {
                $xml['Comprobante']['Conceptos']['Concepto'] = [$xml['Comprobante']['Conceptos']['Concepto']];
            }

            if ($xml['Comprobante']['Impuestos']['Traslados']['Traslado']['Impuesto'])
            {
                $xml['Comprobante']['Impuestos']['Traslados']['Traslado'] = [$xml['Comprobante']['Impuestos']['Traslados']['Traslado']];
            }
        }


        // Nomina


        if ($xml['Comprobante']['Complemento']['Nomina'])
        {
            if ($xml['Comprobante']['Complemento']['Nomina']['Percepciones']['Percepcion']['Concepto'])
            {
                $xml['Comprobante']['Complemento']['Nomina']['Percepciones']['Percepcion'] = [$xml['Comprobante']['Complemento']['Nomina']['Percepciones']['Percepcion']];
            }

            if ($xml['Comprobante']['Complemento']['Nomina']['Deducciones']['Deduccion']['Concepto'])
            {
                $xml['Comprobante']['Complemento']['Nomina']['Deducciones']['Deduccion'] = [$xml['Comprobante']['Complemento']['Nomina']['Deducciones']['Deduccion']];
            }
        }


        // Impuestos
        if ($xml['Comprobante']['Impuestos']['Traslados']['Traslado']['Impuesto'])
        {
            $xml['Comprobante']['Impuestos']['Traslados']['Traslado'] = [$xml['Comprobante']['Impuestos']['Traslados']['Traslado']];
        }

        // pago
        if ($xml['Comprobante']['Complemento']['Pagos'])
        {
            if ($xml['Comprobante']['Complemento']['Pagos']['Pago']['FechaPago'])
            {
                $xml['Comprobante']['Complemento']['Pagos']['Pago'] = [$xml['Comprobante']['Complemento']['Pagos']['Pago']];
            }

            foreach($xml['Comprobante']['Complemento']['Pagos']['Pago'] as $id_pago => $pago)
            {
                if ($pago['DoctoRelacionado']['IdDocumento'])
                {
                    $xml['Comprobante']['Complemento']['Pagos']['Pago'][$id_pago]['DoctoRelacionado'] = [$pago['DoctoRelacionado']];
                }

            }

        }


        return $xml;

    }

    static private function convert33($data)
    {
        if (!$data)
        {
            return false;
        }

        if ($data['Comprobante'])
        {
            $xml = $data['Comprobante'];
        }
        else
        {
            $xml = $data;
        }

        if (!$xml['Complemento'])
        {
            return $data;
        }



        if ($xml['Version'] != '3.3')
        {


            $changes_root = [
                'version' => 'Version',
                'serie' => 'Serie',
                'folio' => 'Folio',
                'fecha' => 'Fecha',
                'total' => 'Total',
                'subTotal' => 'SubTotal',
                'descuento' => 'Descuento',
                'noCertificado' => 'NoCertificado',
                'certificado' => 'Certificado',
                'sello' => 'Sello',
                'metodoDePago' => 'MetodoPago',
                'formaDePago' => 'FormaPago',
                'tipoDeComprobante' => 'TipoDeComprobante',
                'condicionesDePago' => 'CondicionesDePago',
            ];

            $changes_emisor = [
                'rfc' => 'Rfc',
                'nombre' => 'Nombre',
            ];

            $changes_receptor = [
                'rfc' => 'Rfc',
                'nombre' => 'Nombre',
            ];


            $changes_items = [
                'cantidad' => 'Cantidad',
                'unidad' => 'Unidad',
                'noIdentificacion' => 'NoIdentificacion',
                'descripcion' => 'Descripcion',
                'valorUnitario' => 'ValorUnitario',
                'importe' => 'Importe',
            ];


            $changes_taxes = [
                'totalImpuestosTrasladados' => 'TotalImpuestosTrasladados',
                'totalImpuestosRetenidos' => 'TotalImpuestosRetenidos',
            ];

            $changes_taxes_items = [
                'impuesto' => 'Impuesto',
                'tasa' => 'Tasa',
                'importe' => 'Importe',
            ];

            $changes_timbre = [
                'selloSAT' => 'SelloSAT',
                'noCertificadoSAT' => 'NoCertificadoSAT',
                'selloCFD' => 'SelloCFD',
                'version' => 'Version',
            ];

            foreach($changes_root as $ii => $ii_rep)
            {
                if (isset($xml[$ii]))
                {
                    $xml[$ii_rep] = $xml[$ii];
                    unset($xml[$ii]);
                }

            }

            foreach($changes_emisor as $ii => $ii_rep)
            {
                if (isset($xml['Emisor'][$ii])) {
                    $xml['Emisor'][$ii_rep] = $xml['Emisor'][$ii];
                    unset($xml['Emisor'][$ii]);
                }
            }

            foreach($changes_receptor as $ii => $ii_rep)
            {
                if (isset($xml['Receptor'][$ii])) {

                    $xml['Receptor'][$ii_rep] = $xml['Receptor'][$ii];
                    unset($xml['Receptor'][$ii]);
                }
            }


            // items
            if ($xml['Conceptos']['Concepto']['cantidad'])
            {

                foreach($changes_items as $ii => $ii_rep)
                {
                    if (isset($xml['Conceptos']['Concepto'][$ii])) {
                        $xml['Conceptos']['Concepto'][$ii_rep] = $xml['Conceptos']['Concepto'][$ii];
                        unset($xml['Conceptos']['Concepto'][$ii]);
                    }

                }


            }
            else
            {

                foreach($xml['Conceptos']['Concepto'] as $item_id => $item)
                {


                    foreach($changes_items as $ii => $ii_rep)
                    {
                        if (isset($xml['Conceptos']['Concepto'][$item_id][$ii])) {

                            $xml['Conceptos']['Concepto'][$item_id][$ii_rep] = $xml['Conceptos']['Concepto'][$item_id][$ii];
                            unset($xml['Conceptos']['Concepto'][$item_id][$ii]);

                        }

                    }

                }

            }


            // taxes
            foreach($changes_taxes as $ii => $ii_rep)
            {
                if (isset($xml['Impuestos'][$ii]))
                {
                    $xml['Impuestos'][$ii_rep] = $xml['Impuestos'][$ii];
                    unset($xml['Impuestos'][$ii]);
                }

            }

            $tax_item_group['Traslados'] = 'Traslado';
            $tax_item_group['Retenciones'] = 'Retencion';

            foreach(["Traslados", "Retenciones"] as $tax_group_root)
            {
                $TaxInfo = $xml['Impuestos'][$tax_group_root];
                $tax_group = $tax_item_group[$tax_group_root];


                if ($TaxInfo[$tax_group]['impuesto'])
                {
                    foreach($changes_taxes_items as $ii => $ii_rep)
                    {
                        if (isset($TaxInfo[$tax_group][$ii])) {
                            $TaxInfo[$tax_group][$ii_rep] = $TaxInfo[$tax_group][$ii];
                            unset($TaxInfo[$tax_group][$ii]);
                        }

                    }

                }
                else
                {

                    foreach($TaxInfo[$tax_group] as $item_id => $item)
                    {



                        foreach($changes_taxes_items as $ii => $ii_rep)
                        {
                            if (isset($TaxInfo[$tax_group][$item_id][$ii])) {

                                $TaxInfo[$tax_group][$item_id][$ii_rep] = $TaxInfo[$tax_group][$item_id][$ii];
                                unset($TaxInfo[$tax_group][$item_id][$ii]);

                            }

                        }

                    }

                }

                $xml['Impuestos'][$tax_group_root] = $TaxInfo;

            }

            // timbre
            foreach($changes_timbre as $ii => $ii_rep)
            {
                if (isset($xml['Complemento']['TimbreFiscalDigital'][$ii]))
                {
                    $xml['Complemento']['TimbreFiscalDigital'][$ii_rep] = $xml['Complemento']['TimbreFiscalDigital'][$ii];
                    unset($xml['Complemento']['TimbreFiscalDigital'][$ii]);
                }

            }


            // type
            if ($xml['TipoDeComprobante'] == 'ingreso')
            {
                $xml['TipoDeComprobante'] = 'I';
            }
            else if ($xml['TipoDeComprobante'] == 'egreso')
            {
                if($xml['Complemento']['Nomina'])
                {
                    $xml['TipoDeComprobante'] = 'N';
                }
                else{
                    $xml['TipoDeComprobante'] = 'E';
                }

            }
            else if ($xml['TipoDeComprobante'] == 'nomina')
            {
                $xml['TipoDeComprobante'] = 'N';
            }


            // end
            if ($data['Comprobante'])
            {
                $data['Comprobante'] = $xml;
            }
            else
            {
                $data = $xml;
            }

        }
        return $data;

    }

}