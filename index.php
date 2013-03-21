<?php session_start();
include('communs.php');
include('config.php');

unMagicQuote();

global $rootUri;
global $ALLOWED_EXTENSIONS;
global $DATA_ROOT;
global $SECURE_KEY;
global $IMAGE_EXTENSIONS;
global $TEXT_EXTENSIONS;
global $NATIVE_EXTENSIONS;
global $JSON_ENCODING;
global $GZ_INFLATINGG;

if(!is_dir($DATA_ROOT)){
    $mkdirDataRoot = mkdir($DATA_ROOT);
    if(false === $mkdirDataRoot){
        echo 'erreur';
        return;
    }
}

/*
 * Root redirection
 */
if(empty($_GET)){
	header('Location:' . $DATA_ROOT);
}

$rootUri = getRootUri($DATA_ROOT);
$nomFichierSecure = '@secure';

$displayTextarea = false;
$displayImportDocButton = true;

$isNouveauDocument=false;
$isNouveauRepertoire=false;
$isEdite=false;
$isEnregistrer = false;
$contenuFichier = null;
if((isset($_POST['action']) && $_POST['action'] == 'Enregistrer')
  || (isset($_GET['action']) && $_GET['action'] == 'Enregistrer')){
    $isEnregistrer=true;
}

if((isset($_POST['action']) && $_POST['action'] == 'Creation')){
    $isNouveauRepertoire=true;
}

$isDossierEdite = false;

$messageErreur = '';

$documentAConsulter = null;

$documentALire = null;

$repertoireReel = $DATA_ROOT;

$displayRenameDirButton = false;
$displayEditDocButton = false;
$displaySaveButton = false;
$displayRemButton = false;
$displayNewDocButton = false;
$displayNewDirButton = false;
/*
 * Lecture du dossier
 */
$displayInputDir = false;
if(!empty($_GET['kNdossier'])){
    $dossierEnCours = explode('/', urldecode($_GET['kNdossier']));
    $dossierEnCours = end($dossierEnCours);
    $repertoireReel = sprintf('%s/%s', $DATA_ROOT, $_GET['kNdossier']);
    $actionFormPath = '.';

    $displayNewDirButton = true;
    $displayNewDocButton = true;
    $displayRenameDirButton = true;

    /*
     * Mode édition du nom du dossier
     */
    if(!is_dir($repertoireReel) || (isset($_GET['action']) && $_GET['action'] == 'renameDir')){
        $isDossierEdite=true;
        $displayNewDirButton = false;
        $displayNewDocButton = false;
        $displayRenameDirButton = false;
        $displaySaveButton = true;
        $displayInputDir = true;
    }
}

/*
 * Lecture d'un document
 */
$isDocEdite = false;
$displayDropButton=false;
if(!empty($_GET['kNdocument'])){
    $documentAConsulter = sprintf('%s/%s', $DATA_ROOT, urldecode($_GET['kNdocument']));
    $repertoireReel = explode('/', $documentAConsulter);
    $documentALire = array_pop($repertoireReel);
    $extension =  getExtension($documentALire, $ALLOWED_EXTENSIONS);
    $dossierEnCours = end($repertoireReel);
    $repertoireReel = implode('/', $repertoireReel);
    $actionFormPath = urlencode($documentALire);

    $displayNewDirButton = true;
    $displayNewDocButton = true;
    $displayEditDocButton = true;
    $displayInputDoc = false;
    $displayDropButton=true;
    /*
     * Mode edition : actions lors de l'appuie sur le bouton EDIT DOC
     */
    if((isset($_GET['action']) && $_GET['action'] == 'editer')){
        $displayEditDocButton = false;
        $displayNewDocButton = false;
        $displayNewDirButton = false;
        $displaySaveButton = true;
        $displayInputDoc = true;
        $displayTextarea = true;
        $displayDropButton=false;
        $isDocEdite = true;
    }

    /*
     * Mode sauvegarde : actions lors de l'appuie sur le bouton SAVE
    */
    if(isset($_POST['titreDocument'])){
        $documentALire = $_POST['titreDocument'];
        $newFileExtension = getExtension($documentALire);
        /*
         * Changement de nom
         */
        if($documentALire != $_SESSION['documentActuel']){
            rename( $repertoireReel .'/'. $_SESSION['documentActuel'], $repertoireReel .'/'. $documentALire);
        }
        if(null != $documentALire){
            if(!isValidName($documentALire)){
                $messageErreur = 'Erreur nom de fichier';
            }
            else
            {
                $nomNouveauFichier = sprintf('%s/%s',$repertoireReel, $documentALire);
                if(isset($_POST['contenuDocument']) && in_array($newFileExtension, $TEXT_EXTENSIONS)){
                    $retourEnregistrement = aes_store($nomNouveauFichier, $_POST['contenuDocument'], $SECURE_KEY, $JSON_ENCODING, $GZ_INFLATING);
                    header('Location:'.$rootUri.$nomNouveauFichier);
                    return;
                }
            }
        }
    }

    /*
     * Mode création d'un nouveau fichier : action lors de l'appuie sur le bouton NEW DOC
     */
    $pathFichier = sprintf('%s/%s',$repertoireReel, $documentALire);
    if(!is_file($pathFichier)){
        $retourEnregistrement = aes_store($pathFichier, '', $SECURE_KEY, $JSON_ENCODING, $GZ_INFLATING);
        $displayEditDocButton = false;
        $displayNewDocButton = false;
        $displayNewDirButton = false;
        $displaySaveButton = true;
        $displayInputDoc = true;
        $displayTextarea = true;
        $isDocEdite = true;
    }
}

/*
 * Verification du dossier protégé
*/
if(!checkisDir($repertoireReel)){
	//Erreur
	return;
}
if(null !== $SECURE_KEY){
	$secure = verifieDossierProtege($repertoireReel);
	if(false === $secure){
	    header('Location:400.html');
	    return;
	}
}

/*
 * Suppression document/dossier
 */
if(isset($_POST['action']) && 'Remove' === $_POST['action']){
	if(is_array($_POST['fileToRemove'])){
		foreach($_POST['fileToRemove'] as $file){
		    removeDocument($repertoireReel, urldecode($file));
		}
	}
    header('Location:'.$rootUri.$repertoireReel . '/');
    return;
}

$_SESSION['documentActuel'] = $documentALire;

/*
 * Renommage dossier
 */
if(isset($_POST['titreDossier']) && $_POST['titreDossier'] != $DATA_ROOT && $_POST['titreDossier'] != $dossierEnCours){
    $repertoireReelSansNomDossier = explode('/', $repertoireReel);
    array_pop($repertoireReelSansNomDossier);
    $repertoireReelSansNomDossier = implode('/', $repertoireReelSansNomDossier);
    $folderName = purgeFolderName($_POST['titreDossier']);
    $nouveauRepertoire = $repertoireReelSansNomDossier . '/' . $folderName;
    rename ( $repertoireReel , $nouveauRepertoire);
    header('Location:'.$rootUri.$nouveauRepertoire . '/');
    return;
}

$contenuFichier = readDocument($documentAConsulter, $SECURE_KEY, $JSON_ENCODING, $GZ_INFLATING);

$listeFichiers = openDossier($repertoireReel);

if(!(true === $isDossierEdite || true === $isDocEdite) 
	&& is_array($listeFichiers)
	&& ((isset($listeFichiers['dossiers']) && count($listeFichiers['dossiers']) > 1)
	  ||(isset($listeFichiers['fichiers']) && count($listeFichiers['fichiers']) > 0)
	   )
	){
	$displayRemButton = true;
}

?><!DOCTYPE html>
   <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, user-scalable=yes">
        <title>PiCloud : As Simple and Stupid than a cloud</title>
        <link type="text/css" rel="stylesheet" href="<?php echo $rootUri;?>css/style.css" />
        <script type="text/javascript">
        	var rootDirectory = <?php echo $rootUri?>; 
        </script>
        <script type="text/javascript" src="<?php echo $rootUri;?>scripts/keepnote.js"></script>
    </head>
    <body>
        <form method="POST" action="<?php echo $actionFormPath;?>" id="formulaire">
            <div id="menu"><?php
                if(true === $displayDropButton){
                    ?><a href=".">Upload doc</a><?php
                }
                if(true === $displayRemButton){
                	?><input type="submit" name="action" value="Remove"/><?php
                } 
                if(true === $displaySaveButton){
                    ?><input type="submit" name="action" value="Enregistrer"/><?php
                }
                if(true === $displayRenameDirButton){
                    ?><a href="?action=renameDir">Rename dir</a><?php
                }
                if(true === $displayNewDirButton){
                    ?><a href="<?php echo urlencode('Nouveau repertoire');?>/">New dir</a><?php
                }
                if(true === $displayNewDocButton){
                    ?><a href="Nouveau%20document?action=editer">New doc</a><?php
                }
                if(true === $displayEditDocButton){
                    ?><a href="?action=editer">Edit doc</a><?php
                }
                ?>
            </div>
            <div id="main">
                <section id="sidebar">
                    <ul>
                        <?php
                        $tabulation = '';
                        $arborescence = explode('/', $repertoireReel);

                        $chemin = $rootUri;
                        $mycloud=false;
                        if(count($arborescence) > 1){
                            array_pop($arborescence);
                        }
                        foreach($arborescence as $repertoire){
                            $urlDocument = protectUrlDocument($chemin . str_replace('/./', '/',  $repertoire));

                            if(($repertoire == '.' || $repertoire == $DATA_ROOT) && $mycloud==false){
                                ?><li class="dossier"><a href="<?php echo $urlDocument;?>/">Mon cloud</a></li><?php
                                $mycloud = true;
                            }else{
                                ?><li class="dossier"><a href="<?php echo $urlDocument;?>/"><?php echo $tabulation . $repertoire;?></a></li><?php
                            }
                            $chemin = $urlDocument . '/' ;
                            $tabulation .= '&nbsp;';
                        }
                        if($dossierEnCours != '.' && $dossierEnCours != $DATA_ROOT){
                            if(true === $displayInputDir){
                                ?><li class="dossierPrincipal"><?php echo $tabulation;?><input name="titreDossier" type="text" value="<?php echo $dossierEnCours;?>"/></li><?php
                            }else{
                                ?><li class="dossierPrincipal"><?php echo $tabulation;?><a href="./"><?php echo $dossierEnCours;?></a></li><?php
                            }
                        }

                        $tabulation .= '&nbsp;';
                        if(isset($listeFichiers['dossiers'])){
                            sort($listeFichiers['dossiers']);
                            foreach($listeFichiers['dossiers'] as $file){
                                $urlDocument = protectUrlDocument(str_replace('/./', '/', $rootUri . $repertoireReel.'/'.$file));
                                if($file !== '..'){
                                    ?><li class="dossier"><?php echo $tabulation;?>
                                    <input type="checkbox" name="fileToRemove[]" value="<?php echo urlencode($file);?>"/>
                                    <a href="<?php echo $urlDocument;?>/"><?php echo $file;?></a></li><?php
                                }
                            }
                        }
                        if(isset($listeFichiers['fichiers'])){
                            sort($listeFichiers['fichiers']);
                            // Navigation dans le dossier
                            foreach($listeFichiers['fichiers'] as $file){
								?><li>
								<?php echo $tabulation;?>
								<input type="checkbox" name="fileToRemove[]" value="<?php echo urlencode($file);?>"/>
								<?php
                                $extensionTmp = getExtension($file);
                                $urlDocument = protectUrlDocument($rootUri .str_replace('/./', '/',  $repertoireReel.'/'.$file));
                                if($file == $documentALire){
                                    if(true === $displayInputDoc){
                                        ?><input name="titreDocument" type="text" value="<?php echo $file;?>"/><?php
                                    }else{
                                        ?><input name="titreDocument" type="hidden" value="<?php echo $documentALire;?>"/>
                                          <a id="fichierSelectionne" href="<?php echo $urlDocument;?>"><?php echo $file;?></a><?php
                                    }
                                }else{
                                    ?><a href="<?php echo $urlDocument;?>"><?php echo $file;?></a><?php
                                }
                                ?></li><?php 
                            }
                        }
                        ?>
                    </ul>
                </section>

                <?php if($contenuFichier !== null){?>
                <section id="midle">
                    <?php
                        /*
                         * Mode edition
                         */
                        $realExtension = getRealExtension($documentALire);
                        if(in_array($realExtension, $ALLOWED_PICLOUD_EXTENSIONS)){
                            if(true === $displayTextarea){
                                if(!in_array($realExtension, $TEXT_EXTENSIONS)){
                                    $urlDocument = protectUrlDocument($rootUri . 'image' . str_replace($DATA_ROOT, '', str_replace('/./', '/',  $repertoireReel.'/'.$file)));
                                    ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . str_replace($DATA_ROOT, 'image', $repertoireReel) . '/' . $documentALire; ?>" alt=""/></a><?php
                                }else{
                                    ?><textarea id="contenuDocument" name="contenuDocument"><?php echo utf8_encode($contenuFichier); ?></textarea><?php
                                }
                            }else{
                                if(in_array($realExtension, $IMAGE_EXTENSIONS )){
                                    $urlDocument = protectUrlDocument($rootUri . 'image' . str_replace($DATA_ROOT, '', str_replace('/./', '/',  $repertoireReel.'/'.$documentALire)));
                                    ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . str_replace($DATA_ROOT, 'image', $repertoireReel) . '/' . $documentALire; ?>" alt=""/></a><?php
                                }
//                                 else if($realExtension == 'youtube'){
//                                     echo getYoutubeScript($contenuFichier);
//                                 }
//                                 else if($realExtension == 'uploadhero'){
// 									echo getUploadHeroScript($contenuFichier);
//                                 }
                                else{
									?><script type="text/javascript" src="<?php echo $rootUri;?>scripts/shCore.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushJScript.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shAutoloader.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushCss.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushBash.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushPhp.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushSql.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushXml.js"></script>
										<script type="text/javascript" src="<?php echo $rootUri;?>scripts/shBrushPlain.js"></script>
										<link type="text/css" rel="stylesheet" href="<?php echo $rootUri;?>css/shCoreEclipse.css"/>
                                    	<div id="contenuDocumentHighLight">
                                    	<pre class="brush: <?php echo $extension;?>;"><?php echo (htmlentities ( utf8_encode($contenuFichier) , ENT_COMPAT | ENT_HTML401, 'UTF-8'));?></pre></div>
										<script type="text/javascript">SyntaxHighlighter.all();</script>
                                    <?php
                                }
                            }
                        }else{
                            /*
                             * Affichage d'une icone applicative
                             */
                            $urlDocument = protectUrlDocument($rootUri . 'image' . str_replace($DATA_ROOT, '', str_replace('/./', '/',  $repertoireReel.'/'.$documentALire)));
                            switch ($realExtension){
                                case 'pdf':
                                    ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . 'icon/pdf.svg'?>" alt=""/></a><?php
                                    break;
                                    case 'mp3':
                                    case 'ogg':
                                        ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . 'icon/music.svg'?>" alt=""/></a><?php
                                    break;
                                    case 'exe':
                                        ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . 'icon/application.svg'?>" alt=""/></a><?php
                                    break;
                                    case 'doc':
                                    case 'docx':
                                    case 'xls':
                                    case 'xlsx':
                                    case 'pps':
                                    case 'ppsx':
                                    case 'ppt':
                                    case 'pptx':
                                        ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . 'icon/office.svg'?>" alt=""/></a><?php
                                    break;
                                    case 'zip':
                                    case 'tar':
                                    case 'gz':
                                    case 'rar':
                                    case 'war':
                                    case 'bz':
                                    case 'bz2':
                                        ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . 'icon/box.svg'?>" alt=""/></a><?php
                                    break;
                                default:
                                        ?><a href="<?php echo $urlDocument; ?>"><img src="<?php echo $rootUri . 'icon/alien.svg'?>" alt=""/></a><?php
                                    break;
                            }
                        }
                ?></section>
                <?php }else{ ?>
            		<section id="midle">
            			<div class="upload_form_cont">
            				<div class="info">
            					<div id="dropArea" class="dropArea">Drop your files here</div>
            				</div>
            				<div id="progress"></div>
            			</div>
            		</section>
                    <script type="text/javascript" src="<?php echo $rootUri;?>scripts/draganddrop.js"></script>
                <?php }?>
                <?php //include('nuage.html')?>
            </div>
        </form>
    </body>
</html>