<?php
class firmadoticket{
function FirmarTicket($Json_Fac,$Json_Emp)
{
//Autor: Fernando Mamani Blas
//Web: www.dsigperu.net
//correo: contacto@dsigperu.net
//Leemos el JSON

foreach ($Json_Emp as $cpe) {
   $ruc_Emisor = $cpe['rucEmisor'];
   $nom_cert = base64_decode($cpe['nomCertificado']);
   $clav_cert = base64_decode($cpe['clavCertificado']);

} 



   foreach ($Json_Fac as $cabezera) {

      $FechaReferencia= $cabezera['fecGeneracion'];
      $lote= $cabezera['numLote'];
      $fecharesultado= substr($FechaReferencia, 0,4).substr($FechaReferencia, 5,2).substr($FechaReferencia, 8,2);
   }

//========================================================================================================================================
//firmado del documento
require_once('../xmlseclibs-master/xmlseclibs.php');

$documento_xml=$ruc_Emisor.'-RA-'.$fecharesultado.'-'.$lote;

$file='../Xml/xml-firmados/'.$documento_xml.'.xml';

$xml_semilla = '../Xml/xml-no-firmados/'.$documento_xml.'.xml';
$ReferenceNodeName = 'ExtensionContent';
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
$objDSig->sign($objKey, $doc->getElementsByTagName($ReferenceNodeName)->item(1));
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
if (file_exists($file)) {
   $zip = new ZipArchive();
   $filename='../Xml/xml-firmados/'.$documento_xml.'.zip';

    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
       exit("cannot open <$filename>\n");
    }

    $zip->addFile('../Xml/xml-firmados/'.$documento_xml.'.xml', $documento_xml.".xml");
    $zip->close();


    $r[0]='Registrado';

} else {
    $r[0]='Error';
}


   return $r;

}
}
?>