<?php


 
      $ruc_Emisor ="20601733022";
      $nom_cert =base64_decode("TVBTMjAxNzA0MTI1Mjk3NjIucGZ4");
      $clav_cert = base64_decode("RmVybkExOTg5RG9CbGFz");



      $TipoComprobante ="01";
      $serieComp ="F001";
      $numeroComp ="2";      
  

   $documento_xml = $ruc_Emisor."-".$TipoComprobante."-".$serieComp."-".$numeroComp;
//========================================================================================================================================
//firmado del documento
require_once('../xmlseclibs-master/xmlseclibs.php');
$file='../Xml/xml-firmados/'.$documento_xml.'.xml';

$xml_semilla = '../Xml/xml-no-firmados/'.$documento_xml.'.xml';
$ReferenceNodeName = 'ExtensionContent';


echo $xml_semilla;

// Firmar Digitalmente XML de la semilla
$doc =new DOMDocument('1.0', 'ISO-8859-1');
$doc->formatOutput = FALSE; 
$doc->preserveWhiteSpace = TRUE;
$doc->load($xml_semilla);
$objDSig = new XMLSecurityDSig(TRUE);
$objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);
$options['prefix'] = '';
$options['prefix_ns'] = '';
$options['force_uri'] = TRUE;
$options['id_name'] = 'ID';
$objDSig->addReference($doc, XMLSecurityDSig::SHA1, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), $options);
$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
$pfx = file_get_contents("../Certificado/".$nom_cert);
openssl_pkcs12_read($pfx, $key, $clav_cert);
$objKey->loadKey($key["pkey"]);
$objDSig->add509Cert($key["cert"]);
$objDSig->sign($objKey, $doc->getElementsByTagName($ReferenceNodeName)->item(0));
//Guardamos el Documento
$doc->save($file);//$objDSig->sign($objKey, $doc->documentElement);
//========================================================================================================================================

//==================================================================================
$xml = $file;
$doc =new DOMDocument();
$doc->load($xml);
/*Modificar el Nodo ya creado solo para agregar la etiqueta*/
$oldChild = $doc->getElementsByTagName("Signature")->item(0);
$oldChild->parentNode->replaceChild($oldChild, $oldChild);
$oldChild->setAttribute("Id", "SignSUNAT");
//Guardamos el Documento

$doc->save($file);
//==================================================================================


//========================== Comprimimos el archivos ===============================
if (file_exists($file)) {
   $zip = new ZipArchive();
   $filename = '../Xml/xml-firmados/'.$documento_xml.'.zip';

    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
       exit("cannot open <$filename>\n");
    }

    $zip->addFile('../Xml/xml-firmados/'.$documento_xml.'.xml', $documento_xml.".xml");
    $zip->close();

    //==========Aqui Creamos el Json con el XML Firmado============
    $lista = array();
    $lista[] = array('Xml'=>  base64_encode(file_get_contents('../Xml/xml-firmados/'.$documento_xml.'.xml')),);
    $json_string = json_encode($lista, JSON_UNESCAPED_UNICODE);
    $file = '../Upload/'.$documento_xml.'.json';
    file_put_contents($file, $json_string);



} else {

}




?>