<?php

require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

session_start();

function generarCodigo()
{
    try{
        if ($_POST['tk3'] == $_SESSION['token']) {
            if (!empty(trim($_POST['crecuperar']))) {
    
                $correo = $_POST['crecuperar'];
                $con = conectar();
                $sql = "SELECT id, nombre, apellidos FROM usuarios WHERE correo = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('s', $correo);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $nombre, $apellidos);
    
                //generar codigo de 5 digitos con letras y numeros
                $codigo = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 5);
                //fceha peru
                date_default_timezone_set('America/Lima');
                $fecha = date('d-m-Y');
    
                if ($stmt->num_rows > 0) {
                    $stmt->fetch();
                    $stmt->close();
                    //si existe un token se envia un 1
                    $sql2 = "SELECT * from tokens where idusuario = '$id' and estado = 1";
                    $result = $con->query($sql2);
                    if ($result->num_rows > 0) {
                        mysqli_close($con);
                        echo 1;
                        exit();
                    } else {
                        //si no existe se inserta el token
                        $sql3 = "INSERT INTO tokens (idusuario, token, fecha, estado) VALUES (?, ?, ?, 1)";
                        $stmt2 = $con->prepare($sql3);
                        $stmt2->bind_param('iss', $id, $codigo, $fecha);
                        $stmt2->execute();
                        mysqli_close($con);
    
                        //enviar correo
                        $transport = (new Swift_SmtpTransport('smtp.zoho.com', 587, 'tls'))
                            ->setUsername('deliverybonito@zohomail.com') // Credencial de correo de pruebas
                            ->setPassword('Mireyra135#'); // contraseña de correo de pruebas
    
                        $mailer = new Swift_Mailer($transport);
                        $message = (new Swift_Message('Restablecer Contraseña'))
                            ->setFrom(['deliverybonito@zohomail.com' => 'Integricode']) // Dirección de correo de pruebas
                            ->setTo([$correo => 'Nombre del Destinatario']) // destinatario
                            ->setBody("<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html dir='ltr' xmlns='http://www.w3.org/1999/xhtml' xmlns:o='urn:schemas-microsoft-com:office:office' lang='es' style='padding:0;Margin:0'><head><meta charset='UTF-8'><meta content='width=device-width, initial-scale=1' name='viewport'><meta name='x-apple-disable-message-reformatting'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta content='telephone=no' name='format-detection'><title>New Template</title> <!--[if (mso 16)]><style type='text/css'>     a {text-decoration: none;}     </style><![endif]--> <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--> <!--[if gte mso 9]><noscript> <xml> <o:OfficeDocumentSettings> <o:AllowPNG></o:AllowPNG> <o:PixelsPerInch>96</o:PixelsPerInch> </o:OfficeDocumentSettings> </xml> </noscript>
                            <![endif]--><style type='text/css'>#outlook a { padding:0;}.ExternalClass { width:100%;}.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div { line-height:100%;}.es-button { mso-style-priority:100!important; text-decoration:none!important;}a[x-apple-data-detectors] { color:inherit!important; text-decoration:none!important; font-size:inherit!important; font-family:inherit!important; font-weight:inherit!important; line-height:inherit!important;}.es-desk-hidden { display:none; float:left; overflow:hidden; width:0; max-height:0; line-height:0; mso-hide:all;}.es-button-border:hover a.es-button, .es-button-border:hover button.es-button { background:#ffffff!important;} .es-button-border:hover { background:#ffffff!important; border-style:solid solid solid solid!important; border-color:#3d5ca3 #3d5ca3 #3d5ca3 #3d5ca3!important;}
                            @media only screen and (max-width:600px) {p, ul li, ol li, a { line-height:150%!important } h1, h2, h3, h1 a, h2 a, h3 a { line-height:120%!important } h1 { font-size:20px!important; text-align:center } h2 { font-size:16px!important; text-align:left } h3 { font-size:20px!important; text-align:center } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:20px!important } h2 a { text-align:left } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:16px!important } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:20px!important } .es-menu td a { font-size:14px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:10px!important } .es-content-body p, .es-content-body ul li, .es-content-body ol li, .es-content-body a { font-size:16px!important }
                            .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:12px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class='gmail-fix'] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button, button.es-button { font-size:14px!important; display:block!important; border-left-width:0px!important; border-right-width:0px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important }
                            .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important }
                            table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; max-height:inherit!important } }@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }</style>
                            </head> <body style='width:100%;font-family:helvetica, arial, verdana, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0'><div dir='ltr' class='es-wrapper-color' lang='es' style='background-color:#FAFAFA'> <!--[if gte mso 9]><v:background xmlns:v='urn:schemas-microsoft-com:vml' fill='t'> <v:fill type='tile' color='#fafafa'></v:fill> </v:background><![endif]--><table cellpadding='0' cellspacing='0' class='es-header' align='center' role='none' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top'><tr style='border-collapse:collapse'>
                            <td class='es-adaptive' align='center' style='padding:0;Margin:0'><table class='es-header-body' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#3d5ca3;width:600px' cellspacing='0' cellpadding='0' bgcolor='#3d5ca3' align='center' role='none'><tr style='border-collapse:collapse'><td style='padding:5px;Margin:0;background-color:#efefef' bgcolor='#efefef' align='left'><table cellspacing='0' cellpadding='0' width='100%' role='none' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px'><tr style='border-collapse:collapse'><td align='left' style='padding:0;Margin:0;width:590px'><table width='100%' cellspacing='0' cellpadding='0' role='presentation' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px'><tr style='border-collapse:collapse'>
                            <td class='es-m-p0l es-m-txt-c' align='center' style='padding:0;Margin:0;font-size:0px'><img src='https://i.imgur.com/A9rW8vY.png' alt='Integricode' style='display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;font-size:12px' width='218' title='Integricode' height='78'></td> </tr></table></td></tr></table></td></tr></table></td></tr></table> <table class='es-content' cellspacing='0' cellpadding='0' align='center' role='none' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%'><tr style='border-collapse:collapse'>
                            <td style='padding:0;Margin:0;background-color:#fafafa' bgcolor='#fafafa' align='center'><table class='es-content-body' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#ffffff;width:600px' cellspacing='0' cellpadding='0' bgcolor='#ffffff' align='center' role='none'><tr style='border-collapse:collapse'><td style='padding:0;Margin:0;padding-left:20px;padding-right:20px;padding-top:40px;background-color:transparent' bgcolor='transparent' align='left'><table width='100%' cellspacing='0' cellpadding='0' role='none' style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px'><tr style='border-collapse:collapse'>
                            <td valign='top' align='center' style='padding:0;Margin:0;width:560px'><table style='mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-position:left top' width='100%' cellspacing='0' cellpadding='0' role='presentation'><tr style='border-collapse:collapse'><td align='center' style='padding:0;Margin:0;padding-top:5px;padding-bottom:5px;font-size:0px'><img src='https://xnhzoe.stripocdn.email/content/guids/CABINET_dd354a98a803b60e2f0411e893c82f56/images/23891556799905703.png' alt style='display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic' width='100' height='119'></td> </tr><tr style='border-collapse:collapse'>
                            <td align='center' style='padding:0;Margin:0;padding-top:15px;padding-bottom:15px'><h1 style='Margin:0;line-height:24px;mso-line-height-rule:exactly;font-family:arial, helvetica, sans-serif;font-size:20px;font-style:normal;font-weight:normal;color:#333333'><strong style='background-color:transparent'>¿RECUPERAR</strong><br></h1><h1 style='Margin:0;line-height:24px;mso-line-height-rule:exactly;font-family:arial, helvetica, sans-serif;font-size:20px;font-style:normal;font-weight:normal;color:#333333'><strong style='background-color:transparent'>TU CONTRASEÑA?</strong></h1></td></tr> <tr style='border-collapse:collapse'>
                            <td align='center' style='padding:0;Margin:0;padding-left:40px;padding-right:40px'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, arial, verdana, sans-serif;line-height:24px;color:#666666;font-size:16px'>HOLA, $nombre $apellidos</p></td></tr><tr style='border-collapse:collapse'><td align='center' style='padding:0;Margin:0;padding-right:35px;padding-left:40px'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, arial, verdana, sans-serif;line-height:24px;color:#666666;font-size:16px'>Has solicitado un codigo para recuperar tu contraseña</p></td></tr> <tr style='border-collapse:collapse'>
                            <td align='center' style='padding:0;Margin:0;padding-top:25px;padding-left:40px;padding-right:40px'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, arial, verdana, sans-serif;line-height:19.2px;color:#666666;font-size:16px'>Si no realizó esta solicitud, simplemente ignore este correo electrónico. De lo contrario, le asignamos un codigo de recuperación</p></td></tr><tr style='border-collapse:collapse'><td align='center' style='Margin:0;padding-top:25px;padding-bottom:30px;padding-left:40px;padding-right:40px'><p style='Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, arial, verdana, sans-serif;line-height:54px;color:#666666;font-size:36px'><strong>$codigo</strong></p></td></tr> <tr style='border-collapse:collapse'>
                            <td class='es-m-txt-c' align='center' style='padding:0;Margin:0;padding-top:20px;padding-bottom:20px;font-size:0px'><img src='https://i.imgur.com/A9rW8vY.png' alt='Integricode' style='display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;font-size:12px' title='Integricode' height='73' width='204'></td></tr></table></td></tr></table></td></tr></table></td></tr></table></div></body></html>", 'text/html');
    
                        $result = $mailer->send($message);
                        if ($result) {
                            echo 1;
                            exit();
                        } else {
                            echo 3;
                            exit();
                        }
                    }
                } else {
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    echo 4;
                    exit();
                }
            } else {
                echo 2;
                exit();
            }
        } else {
            echo 0;
            exit();
        }
    }catch (Exception $e) {
        echo $e->getMessage();
    }
}

function recuperarClave()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {

            if (!empty(trim($_POST['codigo'])) && !empty(trim($_POST['clave']))) {
    
                $codigo = $_POST['codigo'];
                $clave = $_POST['clave'];
                $hash = password_hash($clave, PASSWORD_BCRYPT);
    
                $con = conectar();
                $sql = "SELECT idusuario FROM tokens WHERE token = ? AND estado = 1";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('s', $codigo);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id);
    
                if ($stmt->num_rows > 0) {
                    $stmt->fetch();
                    $stmt->close();
    
                    $sql2 = "UPDATE usuarios SET clave = ? WHERE id = ?";
                    $stmt2 = $con->prepare($sql2);
                    $stmt2->bind_param('si', $hash, $id);
                    $stmt2->execute();
                    $stmt2->close();
    
                    $sql3 = "UPDATE tokens SET estado = 0 WHERE idusuario = ?";
                    $stmt3 = $con->prepare($sql3);
                    $stmt3->bind_param('i', $id);
                    $stmt3->execute();
                    $stmt3->close();
                    mysqli_close($con);
                    echo 1;
                    exit();
                } else {
                    echo 3;
                    exit();
                }
            } else {
                echo 2;
                exit();
            }
        } else {
            echo 0;
            exit();
        }
    }catch (Exception $e) {
        echo $e->getMessage();
    }
}

if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
