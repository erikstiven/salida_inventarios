<?php
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$oCnx = new Dbo();
$oCnx->DSN = $DSN;
$oCnx->Conectar();

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$idempresa = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;
$minv_cod = isset($_GET['serial']) ? (int)$_GET['serial'] : 0;
$minv_secu = isset($_GET['secu']) ? $_GET['secu'] : '';
$idsucursal = isset($_GET['sucu']) ? (int)$_GET['sucu'] : 0;

$sql = "select bode_cod_bode, bode_nom_bode from saebode where bode_cod_empr = $idempresa";
unset($array_bode);
$array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

$sql = "select u.usuario_id, concat(u.usuario_nombre, ' ', u.usuario_apellido) as nom from usuario u";
unset($array_user);
$array_user = array_dato($oCnx, $sql, 'usuario_id', 'nom');

$div = '';
if ($minv_cod > 0) {
    $div .= '<div style="width: 210mm; height: 100mm; font-family: Arial; font-size: 10px; margin: 0px; padding: 0px;">';
    $sql_det = "select minv_num_sec, minv_fmov, minv_hor_minv, minv_user_web,
                        dmov_cod_dmov, dmov_cod_bode, dmov_cod_prod, dmov_bod_envi,
                        dmov_can_dmov, dmov_cod_unid, unid_nom_unid, prod_nom_prod,
                        dmov_cod_lote, dmov_cad_lote, dmov_ela_lote
                        from saeminv, saedmov, saeunid, saeprod where
                        prod_cod_sucu = dmov_cod_sucu and
                        prod_cod_prod = dmov_cod_prod and
                        prod_cod_empr = $idempresa and
                        unid_cod_unid = dmov_cod_unid and
                        unid_cod_empr = $idempresa and
                        minv_num_comp = dmov_num_comp and
                        minv_cod_empr = $idempresa and
                        minv_cod_sucu = $idsucursal and
                        minv_num_comp = $minv_cod";
    if ($oIfx->Query($sql_det)) {
        if ($oIfx->NumFilas() > 0) {
            $fecha = fecha_mysql_Ymd($oIfx->f('minv_fmov'));
            $hora = $oIfx->f('minv_hor_minv');
            $user = $array_user[$oIfx->f('minv_user_web')];

            $div .= '<div style="padding:2px; text-align:center; width:98%;">';
            $div .= '<table align="center" cellpadding="2" cellspacing="1" width="99%" style="border: #999999  1px solid;">';
            $div .= '<tr>';
            $div .= '<th align="center" height="20" style="border:#999999 1px solid ;" colspan="8">';
            $div .= '<table align="center">
                        <tr>
                            <td style="font-size: 13px;" align="center">N.- EGRESO INVENTARIO:</td>
                            <td style="font-size: 13px;" align="center">' . $minv_secu . '</td>
                        </tr>
                     </table>
                     </th>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<th align="center" height="20" style="border:#999999 1px solid ;" colspan="8">';
            $div .= '<table align="left">
                        <tr>
                            <td style="font-size: 13px;" align="left">FECHA:</td>
                            <td style="font-size: 13px;" align="left">' . $fecha . ' ' . $hora . '</td>
                        </tr>
                     </table>
                     </th>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<th align="center" height="20" style="border:#999999 1px solid ;" colspan="8">';
            $div .= '<table align="left">
                        <tr>
                            <td style="font-size: 13px;" align="left">USUARIO:</td>
                            <td style="font-size: 13px;" align="left">' . $user . '</td>
                        </tr>
                     </table>
                     </th>';
            $div .= '</tr>';
            $div .= '<tr>';
            $div .= '<th align="center" height="10" style="border:#999999 1px solid ;" colspan="8">';
            $div .= '<table align="left">
                        <tr>
                            <td style="font-size: 13px;" align="left"></td>
                            <td style="font-size: 13px;" align="left"></td>
                        </tr>
                     </table>
                     </th>';
            $div .= '</tr>';

            $div .= '<tr height="25">';
            $div .= '<th style="border:#999999 1px solid ;">N.-</th>';
            $div .= '<th style="border:#999999 1px solid ;">BODEGA</th>';
            $div .= '<th style="border:#999999 1px solid ;">CODIGO PRODUCTO</th>';
            $div .= '<th style="border:#999999 1px solid ;">PRODUCTO</th>';
            $div .= '<th style="border:#999999 1px solid ;">UNIDAD</th>';
            $div .= '<th style="border:#999999 1px solid ;">CANTIDAD</th>';
            $div .= '<th style="border:#999999 1px solid ;">LOTE</th>';
            $div .= '<th style="border:#999999 1px solid ;">CADUCIDAD</th>';
            $div .= '</tr>';

            $i = 1;
            do {
                $bode_orig = $array_bode[$oIfx->f('dmov_cod_bode')];
                $prod_cod = $oIfx->f('dmov_cod_prod');
                $prod_nom = $oIfx->f('prod_nom_prod');
                $unid_nom = $oIfx->f('unid_nom_unid');
                $cant = $oIfx->f('dmov_can_dmov');
                $dmov_cod_lote = $oIfx->f('dmov_cod_lote');
                $dmov_cad_lote = fecha_mysql_Ymd($oIfx->f('dmov_cad_lote'));

                $div .= '<tr>';
                $div .= '<td style="font-size: 11px; border:#999999 1px solid ;" align="right">' . $i . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">' . $bode_orig . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">' . $prod_cod . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">' . htmlentities($prod_nom) . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">' . $unid_nom . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;" align="right">' . $cant . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">' . $dmov_cod_lote . '</td>';
                $div .= '<td align="left" style="font-size: 11px; border:#999999 1px solid ;">' . $dmov_cad_lote . '</td>';
                $div .= '</tr>';
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $div .= '</table>';
            $div .= '</div>';
        }
    }
    $div .= '</div>';
} else {
    $div = '<div>No existe Transferencia...</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>EGRESO INVENTARIO</title>
    <style type="text/css">
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
    <script type="text/javascript">
        function imprimir() {
            window.print();
        }
    </script>
</head>
<body onload="imprimir();">
    <?php echo $div; ?>
</body>
</html>
